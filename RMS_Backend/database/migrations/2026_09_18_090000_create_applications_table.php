<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-APP-006, UC-08: Applications table for applicant submissions.
     * Stores application status lifecycle, cover letter, and references.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Applicant
            $table->foreignId('cv_document_id')->nullable()->constrained('documents')->nullOnDelete();

            // Application content
            $table->text('cover_letter')->nullable();

            // Lifecycle status
            $table->enum('status', [
                'Submitted',
                'Under Review',
                'Shortlisted',
                'Interview Scheduled',
                'Offer Extended',
                'Hired',
                'Rejected',
                'Withdrawn',
            ])->default('Submitted');

            // Rejection / withdrawal notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Prevent duplicate applications
            $table->unique(['vacancy_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
