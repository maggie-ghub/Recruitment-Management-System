<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\SubsidiaryController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module 1 API Routes: Auth, User Management, Companies & Departments
|--------------------------------------------------------------------------
*/

// Public Authentication endpoints (rate limited per NFR-SEC-005)
Route::prefix('auth')->middleware('throttle:30,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Current User Profile & Logout
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Companies (Subsidiaries)
    // Read: authenticated users
    Route::get('/subsidiaries', [SubsidiaryController::class, 'index']);
    Route::get('/subsidiaries/{subsidiary}', [SubsidiaryController::class, 'show']);
    // Write: Admin only
    Route::middleware('role:Admin')->group(function () {
        Route::post('/subsidiaries', [SubsidiaryController::class, 'store']);
        Route::put('/subsidiaries/{subsidiary}', [SubsidiaryController::class, 'update']);
        Route::delete('/subsidiaries/{subsidiary}', [SubsidiaryController::class, 'destroy']);
    });

    // Departments
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

    // Audit Logs (Admin & HR Manager per FR-ADM-004)
    Route::middleware('role:Admin,HR Manager')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });

    /*
    |--------------------------------------------------------------------------
    | Module 2 API Routes: Reusable Applicant Profile & Document Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('applicant')->group(function () {
        // Profile Information & Completeness (FR-APP-001, FR-APP-002, FR-APP-005)
        Route::get('/profile', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'getProfile']);
        Route::post('/profile', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'updateProfile']);

        // Education History
        Route::post('/education', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'addEducation']);
        Route::delete('/education/{education}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'deleteEducation']);

        // Work Experience History
        Route::post('/experience', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'addWorkExperience']);
        Route::delete('/experience/{experience}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'deleteWorkExperience']);

        // Skills
        Route::post('/skills', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'addSkill']);
        Route::delete('/skills/{skill}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'deleteSkill']);

        // Document Management: CV & Supporting Files (FR-APP-003, FR-APP-004, NFR-007)
        Route::post('/documents/cv', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'uploadCv']);
        Route::post('/documents', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'uploadSupportingDocument']);
        Route::get('/documents/{document}/download', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'downloadDocument']);
        Route::delete('/documents/{document}', [\App\Http\Controllers\Api\ApplicantProfileController::class, 'deleteDocument']);
    });

    /*
    |--------------------------------------------------------------------------
    | Module 3 API Routes: Vacancy Management & Approval Workflow
    |--------------------------------------------------------------------------
    */
    // Vacancy Discovery (Authenticated applicants or recruiters)
    Route::get('/vacancies', [\App\Http\Controllers\Api\VacancyController::class, 'index']);
    Route::get('/vacancies/{vacancy}', [\App\Http\Controllers\Api\VacancyController::class, 'show']);

    // Vacancy Lifecycle & Creation (Internal recruitment staff)
    Route::middleware('role:Admin,HR Manager,Recruiter')->group(function () {
        Route::post('/vacancies', [\App\Http\Controllers\Api\VacancyController::class, 'store']);
        Route::put('/vacancies/{vacancy}', [\App\Http\Controllers\Api\VacancyController::class, 'update']);
        Route::post('/vacancies/{vacancy}/submit-approval', [\App\Http\Controllers\Api\VacancyController::class, 'submitForApproval']);
        Route::post('/vacancies/{vacancy}/publish', [\App\Http\Controllers\Api\VacancyController::class, 'publish']);
        Route::post('/vacancies/{vacancy}/close', [\App\Http\Controllers\Api\VacancyController::class, 'close']);
        Route::post('/vacancies/{vacancy}/archive', [\App\Http\Controllers\Api\VacancyController::class, 'archive']);
        Route::post('/vacancies/{vacancy}/questions', [\App\Http\Controllers\Api\VacancyController::class, 'saveQuestions']);
    });

    // Vacancy Approval / Correction (HR Manager & Admin only per UC-06)
    Route::middleware('role:Admin,HR Manager')->group(function () {
        Route::post('/vacancies/{vacancy}/approve', [\App\Http\Controllers\Api\VacancyController::class, 'approve']);
    });
});
