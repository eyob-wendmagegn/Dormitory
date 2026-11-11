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
        Schema::table('block_proctor_assignments', function (Blueprint $table) {
            $table->json('assigned_departments')->nullable()->after('assigned_by');
            $table->json('assigned_years')->nullable()->after('assigned_departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('block_proctor_assignments', function (Blueprint $table) {
            $table->dropColumn(['assigned_departments', 'assigned_years']);
        });
    }
};
