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
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('target_role')->default('all')->after('category'); // 'all', 'proctor', 'student'
            $table->string('target_audience')->default('all')->after('target_role'); // 'all', 'block_proctors', 'block_students'
            $table->foreignId('block_id')->nullable()->after('target_audience')->constrained('dormitory_blocks')->nullOnDelete();
            $table->string('priority')->default('normal')->after('block_id'); // 'normal', 'urgent', 'critical'
            $table->timestamp('expires_at')->nullable()->after('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['block_id']);
            $table->dropColumn(['target_role', 'target_audience', 'block_id', 'priority', 'expires_at']);
        });
    }
};
