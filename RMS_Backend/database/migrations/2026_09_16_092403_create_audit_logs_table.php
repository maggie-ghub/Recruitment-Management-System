<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Audit log table for compliance with FR-ADM-003, FR-ADM-004, FR-ADM-005, NFR-SEC-006
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // created, updated, deleted, login, failed_login, locked, suspended
            $table->string('entity_type'); // User, Subsidiary, Department, Vacancy, Application
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('user_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
