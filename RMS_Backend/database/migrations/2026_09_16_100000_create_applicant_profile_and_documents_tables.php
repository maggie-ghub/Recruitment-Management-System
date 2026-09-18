<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SRS Section 7.3: FR-APP-001, FR-APP-002, FR-APP-003, FR-APP-004, FR-APP-005
     */
    public function up(): void
    {
        // 1. Personal & Contact Profile
        Schema::create('applicant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Ethiopia');
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        // 2. Education History
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('institution');
            $table->string('degree'); // Bachelor's, Master's, Diploma, etc.
            $table->string('field_of_study'); // Computer Science, Accounting, etc.
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('grade')->nullable(); // GPA or Grade
            $table->timestamps();

            $table->index('user_id');
        });

        // 3. Work Experience History
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name');
            $table->string('job_title');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('responsibilities')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        // 4. Skills
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->enum('proficiency', ['Beginner', 'Intermediate', 'Advanced', 'Expert'])->default('Intermediate');
            $table->timestamps();

            $table->index('user_id');
        });

        // 5. Document Management (CV & Permitted Supporting Documents)
        // FR-APP-003, FR-APP-004, NFR-007 (stored outside web root, served via access-controlled routes)
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['cv', 'certificate', 'degree', 'other'])->default('cv');
            $table->string('original_filename');
            $table->string('stored_path'); // e.g. documents/user_1/cv_hash.pdf
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->boolean('is_primary_cv')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('work_experiences');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('applicant_profiles');
    }
};
