<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\SubsidiaryController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (no auth required)
|--------------------------------------------------------------------------
*/

// Authentication (rate limited)
Route::prefix('auth')->middleware('throttle:30,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public: Token-based document stream (auth embedded in one-time token, 90s expiry)
// Placed OUTSIDE auth:sanctum so browser can navigate to it directly without headers.
// This allows IDM / browser to download PDFs natively without XHR interception.
Route::get(
    '/documents/stream/{token}',
    [\App\Http\Controllers\Api\ApplicantProfileController::class, 'streamByToken']
)->name('documents.stream');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth helpers
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Module 1: Companies (Subsidiaries) & Departments
    |--------------------------------------------------------------------------
    */
    Route::get('/subsidiaries', [SubsidiaryController::class, 'index']);
    Route::get('/subsidiaries/{subsidiary}/logo', [SubsidiaryController::class, 'logo']);
    Route::get('/subsidiaries/{subsidiary}', [SubsidiaryController::class, 'show']);

    Route::middleware('role:Admin')->group(function () {
        Route::post('/subsidiaries', [SubsidiaryController::class, 'store']);
        Route::put('/subsidiaries/{subsidiary}', [SubsidiaryController::class, 'update']);
        Route::delete('/subsidiaries/{subsidiary}', [SubsidiaryController::class, 'destroy']);
    });

    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::middleware('role:Admin,HR Manager')->group(function () {
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);
    });

    // User Management (Admin & HR Manager)
    Route::middleware('role:Admin,HR Manager')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);
    });

    // Audit Logs
    Route::middleware('role:Admin,HR Manager')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });

    /*
    |--------------------------------------------------------------------------
    | Module 2: Applicant Profile & Document Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('applicant')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'getProfile']);
        Route::post('/profile', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'updateProfile']);

        Route::post('/education', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'addEducation']);
        Route::put('/education/{education}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'updateEducation']);
        Route::delete('/education/{education}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'deleteEducation']);

        Route::post('/experience', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'addWorkExperience']);
        Route::put('/experience/{experience}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'updateWorkExperience']);
        Route::delete('/experience/{experience}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'deleteWorkExperience']);

        Route::post('/skills', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'addSkill']);
        Route::delete('/skills/{skill}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'deleteSkill']);

        // Document Management (FR-APP-003, FR-APP-004, NFR-007)
        Route::post('/documents/cv', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'uploadCv']);
        Route::post('/documents', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'uploadSupportingDocument']);
        Route::get('/documents/{document}/download', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'downloadDocument']);
        Route::get('/documents/{document}/view', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'viewDocument']);
        Route::delete('/documents/{document}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'deleteDocument']);
        // Token request (authenticated) - used by frontend before opening stream URL
        Route::post('/documents/{document}/request-token', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'requestDownloadToken']);

        /*
        |--------------------------------------------------------------------------
        | Module 4: Application Submission & Tracking (Applicant-side)
        |--------------------------------------------------------------------------
        */
        Route::post('/applications/{vacancy}', [ApplicationController::class, 'apply']);
        Route::get('/applications', [ApplicationController::class, 'myApplications']);
        Route::patch('/applications/{application}/withdraw', [ApplicationController::class, 'withdraw']);
    });

    /*
    |--------------------------------------------------------------------------
    | Module 3: Vacancy Management & Approval Workflow
    |--------------------------------------------------------------------------
    */
    Route::get('/vacancies', [\App\Http\Controllers\Api\VacancyController::class, 'index']);
    Route::get('/vacancies/{vacancy}', [\App\Http\Controllers\Api\VacancyController::class, 'show']);

    // Recruitment staff: manage applications for a vacancy
    Route::get('/vacancies/{vacancy}/applications', [ApplicationController::class, 'vacancyApplications']);
    Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus']);
    // Recruitment staff: view full applicant profile (scoped to their company)
    Route::get('/applicant-profiles/{userId}', [ApplicationController::class, 'getApplicantProfile']);

    Route::middleware('role:Admin,HR Manager,Recruiter')->group(function () {
        Route::post('/vacancies', [\App\Http\Controllers\Api\VacancyController::class, 'store']);
        Route::put('/vacancies/{vacancy}', [\App\Http\Controllers\Api\VacancyController::class, 'update']);
        Route::post('/vacancies/{vacancy}/submit-approval', [\App\Http\Controllers\Api\VacancyController::class, 'submitForApproval']);
        Route::post('/vacancies/{vacancy}/publish', [\App\Http\Controllers\Api\VacancyController::class, 'publish']);
        Route::post('/vacancies/{vacancy}/close', [\App\Http\Controllers\Api\VacancyController::class, 'close']);
        Route::post('/vacancies/{vacancy}/reopen', [\App\Http\Controllers\Api\VacancyController::class, 'reopen']);
        Route::post('/vacancies/{vacancy}/archive', [\App\Http\Controllers\Api\VacancyController::class, 'archive']);
        Route::post('/vacancies/{vacancy}/questions', [\App\Http\Controllers\Api\VacancyController::class, 'saveQuestions']);
    });

    Route::middleware('role:Admin,HR Manager')->group(function () {
        Route::post('/vacancies/{vacancy}/approve', [\App\Http\Controllers\Api\VacancyController::class, 'approve']);
    });
});
