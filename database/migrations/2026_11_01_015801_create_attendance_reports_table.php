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
        Schema::create('attendance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('dormitory_blocks')->cascadeOnDelete();
            $table->foreignId('proctor_id')->constrained('users')->cascadeOnDelete();
            $table->string('report_type')->default('weekly'); // weekly, monthly, custom
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('tracked_days_count')->default(0);
            $table->unsignedInteger('total_residents')->default(0);
            $table->unsignedInteger('total_present')->default(0);
            $table->unsignedInteger('total_absent')->default(0);
            $table->unsignedInteger('total_excused')->default(0);
            $table->unsignedInteger('total_sick')->default(0);
            $table->unsignedInteger('frequent_absentees_count')->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted'); // submitted, reviewed
            $table->timestamp('submitted_at')->useCurrent();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('manager_remarks')->nullable();
            $table->timestamps();

            $table->index(['block_id', 'start_date', 'end_date']);
            $table->index(['proctor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_reports');
    }
};
