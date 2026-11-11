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
        Schema::table('comments', function (Blueprint $table) {
            $table->string('target_role', 50)->nullable()->after('category')->index();
            $table->foreignId('block_id')->nullable()->after('target_role')->constrained('dormitory_blocks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['block_id']);
            $table->dropColumn(['target_role', 'block_id']);
        });
    }
};
