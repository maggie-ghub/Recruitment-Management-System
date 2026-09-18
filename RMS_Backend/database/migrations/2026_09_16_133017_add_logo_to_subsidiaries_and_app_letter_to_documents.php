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
        Schema::table('subsidiaries', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('code');
        });

        Schema::table('documents', function (Blueprint $table) {
            // Change type to string to support 'application_letter', 'cv', 'certificate', 'degree', 'other'
            $table->string('type', 50)->default('cv')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subsidiaries', function (Blueprint $table) {
            $table->dropColumn('logo_url');
        });
    }
};
