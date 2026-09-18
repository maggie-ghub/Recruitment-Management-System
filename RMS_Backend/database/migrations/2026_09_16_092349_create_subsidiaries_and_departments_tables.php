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
        // Companies (Subsidiaries) table
        Schema::create('subsidiaries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained('subsidiaries')->onDelete('cascade');
            $table->string('name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
        Schema::dropIfExists('subsidiaries');
    }
};
