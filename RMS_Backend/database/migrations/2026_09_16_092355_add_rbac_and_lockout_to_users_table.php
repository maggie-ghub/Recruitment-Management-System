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
        Schema::table('users', function (Blueprint $table) {
            // RBAC role (Admin, HR Manager, Recruiter, Hiring Manager, Applicant)
            $table->string('role')->default('Applicant')->after('email');
            
            // Scope isolation: holding users have subsidiary_id = null; subsidiary staff are bound to their company
            $table->foreignId('subsidiary_id')->nullable()->after('role')->constrained('subsidiaries')->nullOnDelete();
            
            // Account status: active, inactive, suspended (FR-AUTH-005)
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('subsidiary_id');
            
            // Brute force protection: 5 attempts -> 15 min lockout (FR-AUTH-006, NFR-SEC-005)
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('password');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['subsidiary_id']);
            $table->dropColumn(['role', 'subsidiary_id', 'status', 'failed_login_attempts', 'locked_until']);
        });
    }
};
