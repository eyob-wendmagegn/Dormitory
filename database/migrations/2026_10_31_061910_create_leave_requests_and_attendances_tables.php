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
        // 1. Leave Requests / Gate Passes
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('request_type')->default('gate_pass'); // gate_pass, overnight, weekend, medical, emergency
            $table->date('start_date');
            $table->date('end_date');
            $table->string('destination')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->text('reason');
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // 2. Attendances
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('proctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('block_id')->constrained('dormitory_blocks')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('leave_request_id')->nullable()->constrained('leave_requests')->nullOnDelete();
            $table->date('date');
            $table->timestamp('recorded_at')->useCurrent();
            $table->string('status')->default('present'); // present, absent, excused, sick
            $table->text('remarks')->nullable();
            $table->boolean('is_finalized')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'date']);
            $table->index(['block_id', 'date']);
            $table->index(['date', 'status']);
        });

        // 3. Attendance Submissions (Finalized daily curfew rounds)
        Schema::create('attendance_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('dormitory_blocks')->cascadeOnDelete();
            $table->foreignId('proctor_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('total_students')->default(0);
            $table->unsignedInteger('present_count')->default(0);
            $table->unsignedInteger('absent_count')->default(0);
            $table->unsignedInteger('excused_count')->default(0);
            $table->unsignedInteger('sick_count')->default(0);
            $table->timestamp('submitted_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamps();

            $table->unique(['block_id', 'date']);
            $table->index(['date', 'status']);
        });

        // 4. Attendance Alerts & Warning System
        Schema::create('attendance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type')->default('absentee_warning'); // absentee_warning, attendance_submitted, leave_approved, leave_rejected
            $table->string('title');
            $table->text('message');
            $table->date('date')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_alerts');
        Schema::dropIfExists('attendance_submissions');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('leave_requests');
    }
};
