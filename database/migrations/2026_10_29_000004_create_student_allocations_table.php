<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('allocated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('allocated_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->enum('status', ['active', 'released', 'transferred'])->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'status']);
            $table->index(['student_id', 'status']);
        });

        // PostgreSQL partial unique index: enforce at the database level that a student has at most ONE active allocation
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX unique_active_student_allocation ON student_allocations (student_id) WHERE status = 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("DROP INDEX IF EXISTS unique_active_student_allocation");
        }
        Schema::dropIfExists('student_allocations');
    }
};
