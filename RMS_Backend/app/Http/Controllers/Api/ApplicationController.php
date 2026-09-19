<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicantProfile;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Education;
use App\Models\Skill;
use App\Models\Vacancy;
use App\Models\WorkExperience;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * FR-APP-006, UC-08:
     * Applicant submits application for an open vacancy.
     */
    public function apply(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        // Only applicants can submit applications
        if ($user->role !== 'Applicant') {
            return response()->json(['message' => 'Only applicants can submit applications.'], 403);
        }

        // Vacancy must be open (Published + not past closing date)
        if (!$vacancy->isOpen()) {
            return response()->json(['message' => 'This vacancy is no longer accepting applications.'], 422);
        }

        $screeningAnswers = $request->input('screening_answers', []);
        if (!is_array($screeningAnswers)) {
            $screeningAnswers = [];
        }

        foreach ($screeningAnswers as $index => $answer) {
            if (!is_null($answer) && !is_scalar($answer)) {
                return response()->json([
                    'message' => 'Each screening answer must be a single value.',
                ], 422);
            }

            $screeningAnswers[$index] = is_null($answer) ? null : (string) $answer;
        }
        $questions = $vacancy->questions()->get();
        $missingQuestions = $questions
            ->where('is_required', true)
            ->filter(function ($question, $index) use ($screeningAnswers) {
                $answer = $screeningAnswers[$index] ?? null;

                return $answer === null || trim((string) $answer) === '';
            })
            ->map(fn ($question) => $question->question_text)
            ->values();

        if ($missingQuestions->isNotEmpty()) {
            return response()->json([
                'message' => 'Please answer all required screening questions.',
                'errors' => ['screening_answers' => $missingQuestions],
            ], 422);
        }

        // Prevent duplicate applications (ignore withdrawn ones - applicant may re-apply)
        $existing = Application::where('vacancy_id', $vacancy->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->status !== 'Withdrawn') {
            return response()->json([
                'message'     => 'You have already applied for this vacancy.',
                'application' => $existing,
            ], 422);
        }

        $request->validate([
            'cover_letter'   => 'nullable|string|max:5000',
            'cv_document_id' => 'nullable|exists:documents,id',
            'screening_answers' => 'nullable|array',
            'screening_answers.*' => 'nullable|max:5000',
        ]);

        $storedScreeningAnswers = [];
        foreach ($questions as $index => $question) {
            $storedScreeningAnswers[$question->id] = $screeningAnswers[$index] ?? null;
        }

        // If no CV document specified, find the applicant's primary CV
        $cvDocumentId = $request->cv_document_id;
        if (!$cvDocumentId) {
            $primaryCv = Document::where('user_id', $user->id)
                ->where('type', 'cv')
                ->where('is_primary_cv', true)   // correct column name from migration
                ->first();
            $cvDocumentId = $primaryCv?->id;
        }

        // If re-applying after withdrawal, update the existing record instead of inserting
        if ($existing && $existing->status === 'Withdrawn') {
            $existing->update([
                'cv_document_id' => $cvDocumentId,
                'cover_letter'   => $request->cover_letter ?? $existing->cover_letter,
                'screening_answers' => $storedScreeningAnswers,
                'status'         => 'Submitted',
                'notes'          => null,
            ]);
            $application = $existing->fresh();
        } else {
            $application = Application::create([
                'vacancy_id'     => $vacancy->id,
                'user_id'        => $user->id,
                'cv_document_id' => $cvDocumentId,
                'cover_letter'   => $request->cover_letter ?? null,
                'screening_answers' => $storedScreeningAnswers,
                'status'         => 'Submitted',
            ]);
        }

        // Record audit log - wrapped so a logging failure doesn't block the application
        try {
            AuditLog::record(
                'application_submitted',
                'Application',
                $application->id,
                null,
                ['vacancy_ref' => $vacancy->reference_number, 'vacancy_title' => $vacancy->title],
                $user->id
            );
        } catch (\Throwable $ignored) {
            // Audit failure should never block the user's application submission
        }

        return response()->json([
            'message'     => 'Application submitted successfully.',
            'application' => $application->load(['vacancy:id,title,reference_number,subsidiary_id', 'vacancy.questions', 'cvDocument:id,original_filename']),
        ], 201);
    }

    /**
     * Get all applications submitted by the authenticated applicant.
     */
    public function myApplications(Request $request)
    {
        $user = $request->user();

        $applications = Application::where('user_id', $user->id)
            ->with([
                'vacancy:id,title,reference_number,status,closing_date,location,employment_type,subsidiary_id',
                'vacancy.subsidiary:id,name',
                'cvDocument:id,original_filename,type',
            ])
            ->latest()
            ->get();

        return response()->json(['data' => $applications]);
    }

    /**
     * Applicant withdraws their own application.
     */
    public function withdraw(Request $request, Application $application)
    {
        $user = $request->user();

        if ($application->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (in_array($application->status, ['Hired', 'Rejected'])) {
            return response()->json(['message' => 'This application cannot be withdrawn at this stage.'], 422);
        }

        $application->status = 'Withdrawn';
        $application->notes  = $request->reason ?? 'Withdrawn by applicant.';
        $application->save();

        AuditLog::record('application_withdrawn', 'Application', $application->id, null, null, $user->id);

        return response()->json(['message' => 'Application withdrawn successfully.']);
    }

    /**
     * FR-REC-001: Recruitment staff lists all applications for a vacancy.
     */
    public function vacancyApplications(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        // Subsidiary scoping: staff can only see applications for their own company
        if ($user->subsidiary_id && $vacancy->subsidiary_id !== $user->subsidiary_id) {
            return response()->json(['message' => 'Forbidden: Outside your company scope.'], 403);
        }

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->with([
                'applicant:id,name,email',
                'cvDocument:id,original_filename,type',
            ])
            ->latest()
            ->get();

        return response()->json(['data' => $applications]);
    }

    /**
     * FR-REC-003: Recruitment staff views a specific applicant's full profile.
     * Only accessible if the applicant has applied for a vacancy in the viewer's scope.
     */
    public function getApplicantProfile(Request $request, $applicantUserId)
    {
        $viewer = $request->user();
        $allowedRoles = ['Admin', 'HR Manager', 'Recruiter', 'Hiring Manager'];

        if (!in_array($viewer->role, $allowedRoles)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Ensure the viewer has at least one vacancy that this applicant applied for,
        // scoped to their subsidiary if they are not a holding-company user.
        $query = Application::where('user_id', $applicantUserId)
            ->whereHas('vacancy', function ($q) use ($viewer) {
                if ($viewer->subsidiary_id) {
                    $q->where('subsidiary_id', $viewer->subsidiary_id);
                }
            });

        if (!$query->exists()) {
            return response()->json(['message' => 'No application found for this applicant within your scope.'], 403);
        }

        $profile      = ApplicantProfile::where('user_id', $applicantUserId)->first();
        $educations   = Education::where('user_id', $applicantUserId)->latest('start_date')->get();
        $experiences  = WorkExperience::where('user_id', $applicantUserId)->latest('start_date')->get();
        $skills       = Skill::where('user_id', $applicantUserId)->get();
        $documents    = Document::where('user_id', $applicantUserId)->latest()->get();
        $applications = Application::where('user_id', $applicantUserId)
            ->with(['vacancy:id,title,reference_number,status', 'vacancy.questions'])
            ->latest()
            ->get();

        return response()->json([
            'profile'      => $profile,
            'educations'   => $educations,
            'experiences'  => $experiences,
            'skills'       => $skills,
            'documents'    => $documents,
            'applications' => $applications,
        ]);
    }

    /**
     * FR-REC-002: Update application status (Shortlist, Reject, Offer, etc.)
     */
    public function updateStatus(Request $request, Application $application)
    {
        $user = $request->user();

        $allowedRoles = ['Admin', 'HR Manager', 'Recruiter', 'Hiring Manager'];
        if (!in_array($user->role, $allowedRoles)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'status' => 'required|in:Under Review,Shortlisted,Interview Scheduled,Offer Extended,Hired,Rejected',
            'notes'  => 'nullable|string|max:2000',
        ]);

        $before = $application->status;
        $application->status = $request->status;
        $application->notes  = $request->notes ?? $application->notes;
        $application->save();

        AuditLog::record(
            'application_status_updated',
            'Application',
            $application->id,
            ['status' => $before],
            ['status' => $application->status],
            $user->id
        );

        return response()->json([
            'message'     => "Application status updated to {$application->status}.",
            'application' => $application,
        ]);
    }
}
