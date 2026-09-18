<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SRS Section 7.4: FR-VAC-001 through FR-VAC-008, BR-001, BR-002, BR-003, BR-005, BR-009
     */
    public function up(): void
    {
        // 1. Vacancies table
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique(); // e.g. TOR-TML-2026-001
            $table->foreignId('subsidiary_id')->constrained('subsidiaries')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            $table->string('title');
            $table->unsignedInteger('openings')->default(1);
            $table->unsignedInteger('openings_filled')->default(0);
            $table->string('employment_type')->default('Full-time'); // Full-time, Part-time, Contract, Remote, Hybrid
            $table->string('location');
            $table->string('grade_level')->nullable();
            $table->string('reports_to')->nullable();
            
            // Salary information is confidential / internal only (BR-005, FR-VAC-004)
            $table->decimal('salary_amount', 12, 2)->nullable();
            $table->string('salary_currency', 10)->default('ETB');
            
            $table->date('closing_date');
            $table->text('description');
            $table->text('responsibilities');
            $table->text('qualifications');
            $table->string('experience_years')->nullable();
            $table->json('skills_required')->nullable();
            $table->text('application_requirements')->nullable();
            
            // Status lifecycle: Draft -> Pending Approval -> Approved -> Published -> Closed -> Archived
            $table->enum('status', [
                'Draft',
                'Pending Approval',
                'Approved',
                'Published',
                'Closed',
                'Archived'
            ])->default('Draft');
            
            $table->timestamps();

            $table->index('subsidiary_id');
            $table->index('department_id');
            $table->index('status');
            $table->index('closing_date');
        });

        // 2. Custom Vacancy Screening Questions (BR-009, Section 10)
        Schema::create('vacancy_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained('vacancies')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', ['text', 'boolean', 'select', 'number'])->default('text');
            $table->json('options')->nullable(); // For select type questions
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();

            $table->index('vacancy_id');
        });

        // 3. Vacancy Status History & Audit Log (FR-VAC-003, Section 6)
        Schema::create('vacancy_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained('vacancies')->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('vacancy_id');
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancy_status_histories');
        Schema::dropIfExists('vacancy_questions');
        Schema::dropIfExists('vacancies');
    }
};
