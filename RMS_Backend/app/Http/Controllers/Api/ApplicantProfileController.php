<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicantProfile;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Education;
use App\Models\Skill;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApplicantProfileController extends Controller
{
    /**
     * FR-APP-001, FR-APP-002, FR-APP-005:
     * Fetch complete reusable applicant profile along with completeness score & missing items.
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        $profile = ApplicantProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['country' => 'Ethiopia']
        );

        $educations = Education::where('user_id', $user->id)->latest('start_date')->get();
        $experiences = WorkExperience::where('user_id', $user->id)->latest('start_date')->get();
        $skills = Skill::where('user_id', $user->id)->get();
        $documents = Document::where('user_id', $user->id)->latest()->get();
        $primaryCv = $documents->firstWhere('is_primary_cv', true);

        // Calculate completeness and list missing required items (FR-APP-005)
        $missing = [];
        $totalWeight = 0;
        $earnedWeight = 0;

        // 1. Phone number (Weight 20)
        $totalWeight += 20;
        if (!empty($profile->phone)) {
            $earnedWeight += 20;
        } else {
            $missing[] = 'Contact phone number is missing';
        }

        // 2. Primary CV uploaded (Weight 30)
        $totalWeight += 30;
        if ($primaryCv) {
            $earnedWeight += 30;
        } else {
            $missing[] = 'Primary CV / Resume document is not uploaded';
        }

        // 3. Education history (Weight 20)
        $totalWeight += 20;
        if ($educations->count() > 0) {
            $earnedWeight += 20;
        } else {
            $missing[] = 'At least one education history entry is required';
        }

        // 4. Work experience (Weight 20)
        $totalWeight += 20;
        if ($experiences->count() > 0) {
            $earnedWeight += 20;
        } else {
            $missing[] = 'Work experience history is empty (add at least one position or record)';
        }

        // 5. Skills (Weight 10)
        $totalWeight += 10;
        if ($skills->count() > 0) {
            $earnedWeight += 10;
        } else {
            $missing[] = 'No professional skills added';
        }

        $completeness = round(($earnedWeight / $totalWeight) * 100);

        return response()->json([
            'profile' => $profile,
            'educations' => $educations,
            'work_experiences' => $experiences,
            'skills' => $skills,
            'documents' => $documents,
            'primary_cv' => $primaryCv,
            'completeness_percentage' => $completeness,
            'missing_items' => $missing,
            'is_ready_to_apply' => count($missing) === 0,
        ]);
    }

    /**
     * FR-APP-001: Update personal contact information.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone' => 'nullable|string|max:30',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'bio' => 'nullable|string|max:1000',
        ]);

        $profile = ApplicantProfile::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        AuditLog::record('profile_updated', 'ApplicantProfile', $profile->id, null, $validated, $user->id);

        return response()->json([
            'message' => 'Personal profile updated successfully.',
            'profile' => $profile,
        ]);
    }

    /**
     * Education: Add
     */
    public function addEducation(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'institution' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
            'grade' => 'nullable|string|max:50',
        ]);

        $education = Education::create([
            'user_id' => $user->id,
            'institution' => $validated['institution'],
            'degree' => $validated['degree'],
            'field_of_study' => $validated['field_of_study'],
            'start_date' => $validated['start_date'],
            'end_date' => !empty($validated['is_current']) ? null : ($validated['end_date'] ?? null),
            'is_current' => !empty($validated['is_current']),
            'grade' => $validated['grade'] ?? null,
        ]);

        AuditLog::record('education_added', 'Education', $education->id, null, $education->toArray(), $user->id);

        return response()->json([
            'message' => 'Education entry added successfully.',
            'education' => $education,
        ], 201);
    }

    /**
     * Education: Update
     */
    public function updateEducation(Request $request, Education $education)
    {
        if ($education->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'institution' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
            'grade' => 'nullable|string|max:50',
        ]);

        $education->update([
            'institution' => $validated['institution'],
            'degree' => $validated['degree'],
            'field_of_study' => $validated['field_of_study'],
            'start_date' => $validated['start_date'],
            'end_date' => !empty($validated['is_current']) ? null : ($validated['end_date'] ?? null),
            'is_current' => !empty($validated['is_current']),
            'grade' => $validated['grade'] ?? null,
        ]);

        AuditLog::record('education_updated', 'Education', $education->id, null, $education->toArray(), $request->user()->id);

        return response()->json([
            'message' => 'Education entry updated successfully.',
            'education' => $education,
        ]);
    }

    /**
     * Education: Delete
     */
    public function deleteEducation(Request $request, Education $education)
    {
        if ($education->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $education->delete();
        return response()->json(['message' => 'Education entry removed.']);
    }

    /**
     * Work Experience: Add
     */
    public function addWorkExperience(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
            'responsibilities' => 'nullable|string|max:2000',
        ]);

        $experience = WorkExperience::create([
            'user_id' => $user->id,
            'company_name' => $validated['company_name'],
            'job_title' => $validated['job_title'],
            'start_date' => $validated['start_date'],
            'end_date' => !empty($validated['is_current']) ? null : ($validated['end_date'] ?? null),
            'is_current' => !empty($validated['is_current']),
            'responsibilities' => $validated['responsibilities'] ?? null,
        ]);

        AuditLog::record('experience_added', 'WorkExperience', $experience->id, null, $experience->toArray(), $user->id);

        return response()->json([
            'message' => 'Work experience entry added successfully.',
            'work_experience' => $experience,
        ], 201);
    }

    /**
     * Work Experience: Update
     */
    public function updateWorkExperience(Request $request, WorkExperience $experience)
    {
        if ($experience->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
            'responsibilities' => 'nullable|string|max:2000',
        ]);

        $experience->update([
            'company_name' => $validated['company_name'],
            'job_title' => $validated['job_title'],
            'start_date' => $validated['start_date'],
            'end_date' => !empty($validated['is_current']) ? null : ($validated['end_date'] ?? null),
            'is_current' => !empty($validated['is_current']),
            'responsibilities' => $validated['responsibilities'] ?? null,
        ]);

        AuditLog::record('experience_updated', 'WorkExperience', $experience->id, null, $experience->toArray(), $request->user()->id);

        return response()->json([
            'message' => 'Work experience entry updated successfully.',
            'work_experience' => $experience,
        ]);
    }

    /**
     * Work Experience: Delete
     */
    public function deleteWorkExperience(Request $request, WorkExperience $experience)
    {
        if ($experience->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $experience->delete();
        return response()->json(['message' => 'Work experience entry removed.']);
    }

    /**
     * Skills: Add
     */
    public function addSkill(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'proficiency' => 'required|in:Beginner,Intermediate,Advanced,Expert',
        ]);

        $skill = Skill::updateOrCreate(
            ['user_id' => $user->id, 'name' => trim($validated['name'])],
            ['proficiency' => $validated['proficiency']]
        );

        return response()->json([
            'message' => 'Skill saved successfully.',
            'skill' => $skill,
        ], 201);
    }

    /**
     * Skills: Delete
     */
    public function deleteSkill(Request $request, Skill $skill)
    {
        if ($skill->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $skill->delete();
        return response()->json(['message' => 'Skill removed.']);
    }

    /**
     * FR-APP-003, NFR-007:
     * Upload / Replace CV (PDF/DOCX, max 5MB, strict MIME and extension validation).
     * Stored in private disk outside web root.
     */
    public function uploadCv(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'cv' => [
                'required',
                'file',
                'max:15360', // 15MB max
                'mimes:pdf,docx,doc',
            ],
        ], [
            'cv.required' => 'Please select a CV file to upload.',
            'cv.max'      => 'The CV file may not be greater than 15 MB in size.',
            'cv.mimes'    => 'Invalid file format. Only PDF, DOC, and DOCX formats are supported for CV.',
        ]);

        $file = $request->file('cv');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        
        // Store in private storage directory
        $storedPath = $file->storeAs("documents/user_{$user->id}", $filename, 'local');

        // Mark existing CVs as not primary
        Document::where('user_id', $user->id)->where('type', 'cv')->update(['is_primary_cv' => false]);

        $document = Document::create([
            'user_id' => $user->id,
            'type' => 'cv',
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'is_primary_cv' => true,
        ]);

        AuditLog::record('cv_uploaded', 'Document', $document->id, null, [
            'filename' => $document->original_filename,
            'size' => $document->file_size,
        ], $user->id);

        return response()->json([
            'message' => 'CV uploaded successfully.',
            'document' => $document,
        ], 201);
    }

    /**
     * FR-APP-004: Upload supporting document (Application Letter, Certificate, Degree, etc.).
     */
    public function uploadSupportingDocument(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'type' => 'required|in:application_letter,certificate,degree,other',
            'document' => [
                'required',
                'file',
                'max:15360', // 15MB max
                'mimes:pdf,docx,doc,png,jpg,jpeg',
            ],
        ], [
            'document.required' => 'Please select a document file to upload.',
            'document.max' => 'The uploaded file exceeds the 15 MB size limit.',
            'document.mimes' => 'Unsupported file type. Permitted formats are PDF, DOC, DOCX, PNG, JPG, and JPEG.',
            'type.in' => 'Invalid document category selected.',
        ]);

        $file = $request->file('document');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $storedPath = $file->storeAs("documents/user_{$user->id}", $filename, 'local');

        $document = Document::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'is_primary_cv' => false,
        ]);

        AuditLog::record('document_uploaded', 'Document', $document->id, null, [
            'type' => $document->type,
            'filename' => $document->original_filename,
        ], $user->id);

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'document' => $document,
        ], 201);
    }

    /**
     * FR-APP-003, NFR-007:
     * Secure access-controlled download stream.
     * File is never exposed via direct public URL.
     */
    public function downloadDocument(Request $request, Document $document)
    {
        $user = $request->user();

        // Authorization check: Owner or authorized recruitment roles (Admin, HR Manager, Recruiter, Hiring Manager)
        $isOwner = $document->user_id === $user->id;
        $isRecruiterOrAdmin = in_array($user->role, ['Admin', 'HR Manager', 'Recruiter', 'Hiring Manager']);

        if (!$isOwner && !$isRecruiterOrAdmin) {
            return response()->json(['message' => 'Forbidden: You cannot access this document.'], 403);
        }

        // Try standard private storage location, fallback to direct app storage
        $path = storage_path('app/private/' . $document->stored_path);
        if (!file_exists($path)) {
            $path = storage_path('app/' . $document->stored_path);
        }

        if (!file_exists($path)) {
            return response()->json(['message' => 'Requested file was not found on server.'], 404);
        }

        AuditLog::record('document_downloaded', 'Document', $document->id, null, null, $user->id);

        $fileSize = filesize($path);
        $ext = strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION));
        $extMimes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
        ];
        $mime = $extMimes[$ext] ?? ($document->mime_type ?: 'application/octet-stream');

        $origin = request()->header('Origin') ?: '*';

        return response()->stream(function () use ($path) {
            // Flush any output buffer to avoid corruption/empty body
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            readfile($path);
        }, 200, [
            'Content-Type'                     => $mime,
            'Content-Length'                   => $fileSize,
            'Content-Disposition'              => 'attachment; filename="' . rawurlencode($document->original_filename) . '"',
            'Cache-Control'                    => 'no-store, no-cache, must-revalidate',
            'Pragma'                           => 'no-cache',
            'Access-Control-Allow-Origin'      => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, Accept, X-Requested-With',
            'Access-Control-Allow-Methods'     => 'GET, OPTIONS',
            'Vary'                             => 'Origin',
        ]);
    }

    /**
     * Preview Document directly in browser/in-app modal.
     */
    public function viewDocument(Request $request, Document $document)
    {
        $user = $request->user();

        $isOwner = $document->user_id === $user->id;
        $isRecruiterOrAdmin = in_array($user->role, ['Admin', 'HR Manager', 'Recruiter', 'Hiring Manager']);

        if (!$isOwner && !$isRecruiterOrAdmin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Try standard private storage location, fallback to direct app storage
        $path = storage_path('app/private/' . $document->stored_path);
        if (!file_exists($path)) {
            $path = storage_path('app/' . $document->stored_path);
        }

        if (!file_exists($path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        $fileSize = filesize($path);
        $ext = strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION));
        $extMimes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
        ];
        $mime = $extMimes[$ext] ?? ($document->mime_type ?: 'application/octet-stream');

        $origin = request()->header('Origin') ?: '*';

        return response()->stream(function () use ($path) {
            // Flush any output buffer to avoid corruption/empty body
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            readfile($path);
        }, 200, [
            'Content-Type'                     => $mime,
            'Content-Length'                   => $fileSize,
            // No Content-Disposition here - avoids IDM treating this as a download
            'Cache-Control'                    => 'no-store, no-cache, must-revalidate',
            'Pragma'                           => 'no-cache',
            'Access-Control-Allow-Origin'      => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, Accept, X-Requested-With',
            'Access-Control-Allow-Methods'     => 'GET, OPTIONS',
            'Vary'                             => 'Origin',
        ]);
    }

    /**
     * Delete Document
     */
    public function deleteDocument(Request $request, Document $document)
    {
        $user = $request->user();

        if ($document->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Remove from disk
        $path1 = storage_path('app/private/' . $document->stored_path);
        if (file_exists($path1)) {
            @unlink($path1);
        }
        $path2 = storage_path('app/' . $document->stored_path);
        if (file_exists($path2)) {
            @unlink($path2);
        }

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully.']);
    }

    /**
     * Issue a short-lived (90 second) download token for a document.
     * The frontend uses this token in a direct browser URL - no XHR needed.
     * This bypasses IDM interception of PDF downloads via Axios/fetch.
     */
    public function requestDownloadToken(Request $request, Document $document)
    {
        $user = $request->user();

        $isOwner = $document->user_id === $user->id;
        $isRecruiterOrAdmin = in_array($user->role, ['Admin', 'HR Manager', 'Recruiter', 'Hiring Manager']);

        if (!$isOwner && !$isRecruiterOrAdmin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $token = Str::random(64);

        Cache::put('doc_download_' . $token, [
            'document_id' => $document->id,
            'user_id'     => $user->id,
        ], now()->addSeconds(90));

        return response()->json([
            'token'      => $token,
            'expires_in' => 90,
        ]);
    }

    /**
     * Stream document using a pre-issued short-lived token (no auth header required).
     * Used for direct browser navigation (window.open) to avoid IDM XHR interception.
     */
    public function streamByToken(Request $request, string $token)
    {
        $data = Cache::pull('doc_download_' . $token); // pull = get + delete (one-time use)

        if (!$data) {
            return response()->json(['message' => 'Download link has expired or is invalid. Please try again.'], 410);
        }

        $document = Document::find($data['document_id']);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $path = storage_path('app/private/' . $document->stored_path);
        if (!file_exists($path)) {
            $path = storage_path('app/' . $document->stored_path);
        }

        if (!file_exists($path)) {
            return response()->json(['message' => 'File not found on server.'], 404);
        }

        AuditLog::record('document_downloaded', 'Document', $document->id, null, null, $data['user_id']);

        $fileSize = filesize($path);
        $ext      = strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION));
        $extMimes = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
        ];
        $mime = $extMimes[$ext] ?? ($document->mime_type ?: 'application/octet-stream');

        return response()->stream(function () use ($path) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            readfile($path);
        }, 200, [
            'Content-Type'        => $mime,
            'Content-Length'      => $fileSize,
            'Content-Disposition' => 'attachment; filename="' . rawurlencode($document->original_filename) . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Pragma'              => 'no-cache',
        ]);
    }
}
