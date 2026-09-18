<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Subsidiary;
use App\Models\Vacancy;
use App\Models\VacancyQuestion;
use App\Models\VacancyStatusHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VacancyController extends Controller
{
    /**
     * FR-VAC-001, FR-SEARCH-001, BR-001, BR-003:
     * List vacancies with role-based filtering, company isolation, and salary confidentiality.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isApplicant = !$user || $user->role === 'Applicant';

        $query = Vacancy::with(['subsidiary', 'department', 'creator']);

        // Applicants only see Published vacancies (BR-001)
        if ($isApplicant) {
            $query->where('status', 'Published');
        } else {
            // Recruitment users: Apply subsidiary scope unless Holding Admin or HR Manager
            if ($user->subsidiary_id) {
                $query->where('subsidiary_id', $user->subsidiary_id);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
        }

        // Search & Filter
        if ($request->has('search') && !empty($request->search)) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('reference_number', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%");
            });
        }

        if ($request->has('subsidiary_id') && !empty($request->subsidiary_id)) {
            $query->where('subsidiary_id', $request->subsidiary_id);
        }

        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('employment_type') && !empty($request->employment_type)) {
            $query->where('employment_type', $request->employment_type);
        }

        $vacancies = $query->latest()->get()->map(function ($vacancy) use ($isApplicant) {
            // Salary information is confidential and never publicly displayed (BR-005, FR-VAC-004)
            if ($isApplicant) {
                $vacancy->salary_amount = null;
                $vacancy->salary_currency = null;
            }
            $vacancy->is_open = $vacancy->isOpen();
            return $vacancy;
        });

        return response()->json([
            'data' => $vacancies,
        ]);
    }

    /**
     * FR-VAC-003, BR-005: Get detailed single vacancy.
     */
    public function show(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();
        $isApplicant = !$user || $user->role === 'Applicant';

        // Applicants can only view published vacancies
        if ($isApplicant && $vacancy->status !== 'Published') {
            return response()->json(['message' => 'Vacancy not available.'], 404);
        }

        // Check subsidiary isolation for internal staff
        if (!$isApplicant && $user->subsidiary_id && $vacancy->subsidiary_id !== $user->subsidiary_id) {
            return response()->json(['message' => 'Forbidden: Outside your company scope.'], 403);
        }

        $vacancy->load(['subsidiary', 'department', 'creator', 'questions', 'statusHistories.user']);

        if ($isApplicant) {
            $vacancy->salary_amount = null;
            $vacancy->salary_currency = null;
        }

        $vacancy->is_open = $vacancy->isOpen();

        return response()->json([
            'data' => $vacancy,
        ]);
    }

    /**
     * FR-VAC-001, UC-05: Create vacancy draft.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Enforce role: Recruiters, HR Managers, Admin
        if (!in_array($user->role, ['Admin', 'HR Manager', 'Recruiter'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'department_id' => 'nullable|exists:departments,id',
            'title' => 'required|string|max:255',
            'openings' => 'required|integer|min:1',
            'employment_type' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'grade_level' => 'nullable|string|max:100',
            'reports_to' => 'nullable|string|max:255',
            'salary_amount'   => 'nullable|numeric|min:0|max:999999999.99',
            'salary_currency' => 'nullable|string|max:10',
            'closing_date' => 'required|date|after_or_equal:today',
            'description' => 'required|string',
            'responsibilities' => 'required|string',
            'qualifications' => 'required|string',
            'experience_years' => 'nullable|string|max:100',
            'skills_required' => 'nullable|array',
            'application_requirements' => 'nullable|string',
        ]);

        // Enforce subsidiary scope: subsidiary recruiter cannot create vacancy for another company
        if ($user->subsidiary_id && (int)$validated['subsidiary_id'] !== (int)$user->subsidiary_id) {
            return response()->json(['message' => 'You can only create vacancies for your assigned company.'], 403);
        }

        $subsidiary = Subsidiary::findOrFail($validated['subsidiary_id']);
        $reference = Vacancy::generateReference($subsidiary->code);

        $vacancy = Vacancy::create(array_merge($validated, [
            'reference_number' => $reference,
            'created_by' => $user->id,
            'status' => 'Draft',
        ]));

        VacancyStatusHistory::create([
            'vacancy_id' => $vacancy->id,
            'changed_by' => $user->id,
            'from_status' => null,
            'to_status' => 'Draft',
            'notes' => 'Vacancy created as Draft.',
        ]);

        AuditLog::record('vacancy_created', 'Vacancy', $vacancy->id, null, ['ref' => $vacancy->reference_number, 'title' => $vacancy->title], $user->id);

        return response()->json([
            'message' => 'Vacancy draft created successfully.',
            'data' => $vacancy->load(['subsidiary', 'department']),
        ], 201);
    }

    /**
     * FR-VAC-002: Update vacancy draft or approved details.
     */
    public function update(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        // Scope check
        if ($user->subsidiary_id && $vacancy->subsidiary_id !== $user->subsidiary_id) {
            return response()->json(['message' => 'Forbidden: Outside your company scope.'], 403);
        }

        // Cannot edit closed or archived vacancies unless reopened
        if (in_array($vacancy->status, ['Closed', 'Archived']) && $user->role !== 'Admin') {
            return response()->json(['message' => 'Cannot edit a closed or archived vacancy.'], 422);
        }

        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'title' => 'required|string|max:255',
            'openings' => 'required|integer|min:1',
            'employment_type' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'grade_level' => 'nullable|string|max:100',
            'reports_to' => 'nullable|string|max:255',
            'salary_amount'   => 'nullable|numeric|min:0|max:999999999.99',
            'salary_currency' => 'nullable|string|max:10',
            'closing_date' => 'required|date',
            'description' => 'required|string',
            'responsibilities' => 'required|string',
            'qualifications' => 'required|string',
            'experience_years' => 'nullable|string|max:100',
            'skills_required' => 'nullable|array',
            'application_requirements' => 'nullable|string',
        ]);

        $before = $vacancy->toArray();
        $vacancy->update($validated);

        AuditLog::record('vacancy_updated', 'Vacancy', $vacancy->id, $before, $vacancy->toArray(), $user->id);

        return response()->json([
            'message' => 'Vacancy updated successfully.',
            'data' => $vacancy->load(['subsidiary', 'department']),
        ]);
    }

    /**
     * UC-05 -> UC-06: Recruiter submits draft for approval.
     */
    public function submitForApproval(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        if ($vacancy->status !== 'Draft') {
            return response()->json(['message' => 'Only Draft vacancies can be submitted for approval.'], 422);
        }

        $vacancy->status = 'Pending Approval';
        $vacancy->save();

        VacancyStatusHistory::create([
            'vacancy_id' => $vacancy->id,
            'changed_by' => $user->id,
            'from_status' => 'Draft',
            'to_status' => 'Pending Approval',
            'notes' => $request->notes ?? 'Submitted for management review & approval.',
        ]);

        AuditLog::record('vacancy_submitted_for_approval', 'Vacancy', $vacancy->id, null, null, $user->id);

        return response()->json([
            'message' => 'Vacancy submitted for approval.',
            'data' => $vacancy,
        ]);
    }

    /**
     * UC-06: HR Manager / Admin approves vacancy (or returns to Draft).
     */
    public function approve(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        if (!in_array($user->role, ['Admin', 'HR Manager'])) {
            return response()->json(['message' => 'Unauthorized. Approval requires HR Manager or Admin role.'], 403);
        }

        $request->validate([
            'decision' => 'required|in:Approved,Draft', // Draft if returned for correction
            'notes' => 'nullable|string',
        ]);

        $fromStatus = $vacancy->status;
        $vacancy->status = $request->decision;
        $vacancy->save();

        VacancyStatusHistory::create([
            'vacancy_id' => $vacancy->id,
            'changed_by' => $user->id,
            'from_status' => $fromStatus,
            'to_status' => $vacancy->status,
            'notes' => $request->notes ?? ($request->decision === 'Approved' ? 'Approved by HR Manager' : 'Returned for revisions'),
        ]);

        AuditLog::record('vacancy_approval_decision', 'Vacancy', $vacancy->id, null, ['decision' => $request->decision], $user->id);

        return response()->json([
            'message' => "Vacancy status updated to {$vacancy->status}.",
            'data' => $vacancy,
        ]);
    }

    /**
     * FR-VAC-006, UC-07, BR-001: Publish approved vacancy.
     */
    public function publish(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        if ($vacancy->status !== 'Approved') {
            return response()->json(['message' => 'Only Approved vacancies can be published.'], 422);
        }

        if (Carbon::now()->startOfDay()->gt($vacancy->closing_date)) {
            return response()->json(['message' => 'Closing date has passed. Please update closing date before publishing.'], 422);
        }

        $vacancy->status = 'Published';
        $vacancy->save();

        VacancyStatusHistory::create([
            'vacancy_id' => $vacancy->id,
            'changed_by' => $user->id,
            'from_status' => 'Approved',
            'to_status' => 'Published',
            'notes' => 'Vacancy published and open for applications.',
        ]);

        AuditLog::record('vacancy_published', 'Vacancy', $vacancy->id, null, null, $user->id);

        return response()->json([
            'message' => 'Vacancy published successfully.',
            'data' => $vacancy,
        ]);
    }

    /**
     * FR-VAC-007, UC-11: Close vacancy (blocks new applications).
     */
    public function close(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        if (!in_array($user->role, ['Admin', 'HR Manager', 'Recruiter'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $fromStatus = $vacancy->status;
        $vacancy->status = 'Closed';
        $vacancy->save();

        VacancyStatusHistory::create([
            'vacancy_id' => $vacancy->id,
            'changed_by' => $user->id,
            'from_status' => $fromStatus,
            'to_status' => 'Closed',
            'notes' => $request->notes ?? 'Vacancy closed.',
        ]);

        AuditLog::record('vacancy_closed', 'Vacancy', $vacancy->id, null, null, $user->id);

        return response()->json([
            'message' => 'Vacancy closed. New applications blocked.',
            'data' => $vacancy,
        ]);
    }

    /**
     * Reopen a closed vacancy (reverse of close).
     * Allowed for Admin, HR Manager, Recruiter to handle accidental closures.
     */
    public function reopen(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        if (!in_array($user->role, ['Admin', 'HR Manager', 'Recruiter'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($vacancy->status !== 'Closed') {
            return response()->json(['message' => 'Only closed vacancies can be reopened.'], 422);
        }

        $vacancy->status = 'Published';
        $vacancy->save();

        VacancyStatusHistory::create([
            'vacancy_id'  => $vacancy->id,
            'changed_by'  => $user->id,
            'from_status' => 'Closed',
            'to_status'   => 'Published',
            'notes'       => $request->notes ?? 'Vacancy reopened and accepting applications again.',
        ]);

        AuditLog::record('vacancy_reopened', 'Vacancy', $vacancy->id, null, null, $user->id);

        return response()->json([
            'message' => 'Vacancy reopened. Applications are now accepted again.',
            'data'    => $vacancy,
        ]);
    }

    /**
     * FR-VAC-008: Archive closed vacancy.
     */
    public function archive(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        if ($vacancy->status !== 'Closed' && $user->role !== 'Admin') {
            return response()->json(['message' => 'Only closed vacancies can be archived.'], 422);
        }

        $fromStatus = $vacancy->status;
        $vacancy->status = 'Archived';
        $vacancy->save();

        VacancyStatusHistory::create([
            'vacancy_id' => $vacancy->id,
            'changed_by' => $user->id,
            'from_status' => $fromStatus,
            'to_status' => 'Archived',
            'notes' => 'Vacancy archived.',
        ]);

        AuditLog::record('vacancy_archived', 'Vacancy', $vacancy->id, null, null, $user->id);

        return response()->json([
            'message' => 'Vacancy archived.',
            'data' => $vacancy,
        ]);
    }

    /**
     * BR-009, Section 10: Configure screening questions for vacancy.
     */
    public function saveQuestions(Request $request, Vacancy $vacancy)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string|max:500',
            'questions.*.question_type' => 'required|in:text,boolean,select,number',
            'questions.*.options' => 'nullable|array',
            'questions.*.is_required' => 'nullable|boolean',
            'questions.*.order' => 'nullable|integer',
        ]);

        // Replace questions
        $vacancy->questions()->delete();

        foreach ($request->questions as $idx => $q) {
            VacancyQuestion::create([
                'vacancy_id' => $vacancy->id,
                'question_text' => $q['question_text'],
                'question_type' => $q['question_type'],
                'options' => $q['options'] ?? null,
                'is_required' => $q['is_required'] ?? true,
                'order' => $q['order'] ?? ($idx + 1),
            ]);
        }

        return response()->json([
            'message' => 'Screening questions updated successfully.',
            'questions' => $vacancy->questions()->get(),
        ]);
    }
}
