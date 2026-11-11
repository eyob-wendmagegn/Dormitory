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
        // 1. General Warehouse Asset Ledger
        Schema::create('assets_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_name'); // 'Mattress', 'Pillow'
            $table->string('item_code')->unique(); // 'MATTRESS', 'PILLOW'
            $table->unsignedInteger('total_quantity')->default(0);
            $table->unsignedInteger('available_quantity')->default(0);
            $table->unsignedInteger('allocated_quantity')->default(0);
            $table->unsignedInteger('damaged_quantity')->default(0);
            $table->decimal('unit_penalty_charge', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Stage 1: Proctor Asset Assignment (Manager to Proctor Transfer)
        Schema::create('proctor_asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('proctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('block_id')->nullable()->constrained('dormitory_blocks')->nullOnDelete();
            $table->string('item_type'); // 'mattress', 'pillow'
            $table->unsignedInteger('assigned_quantity');
            $table->unsignedInteger('confirmed_quantity')->default(0);
            $table->enum('status', ['pending_confirmation', 'confirmed', 'rejected'])->default('pending_confirmation');
            $table->date('transfer_date');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['proctor_id', 'status']);
            $table->index(['block_id', 'item_type']);
        });

        // 3. Stage 2 & 3: Student Asset Issue & Return (Proctor to Student Check-out & Check-in / Clearance)
        Schema::create('student_asset_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('proctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('block_id')->nullable()->constrained('dormitory_blocks')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('item_type'); // 'mattress', 'pillow'
            $table->unsignedInteger('quantity')->default(1);
            $table->date('issue_date');
            $table->enum('status', ['pending_confirmation', 'confirmed', 'returned', 'damaged', 'missing'])->default('pending_confirmation');
            $table->timestamp('student_confirmed_at')->nullable();
            $table->date('return_date')->nullable();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('condition_status', ['good', 'damaged', 'missing'])->nullable();
            $table->decimal('penalty_charge', 10, 2)->default(0.00);
            $table->boolean('is_penalty_paid')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['proctor_id', 'item_type']);
            $table->index(['block_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_asset_issues');
        Schema::dropIfExists('proctor_asset_assignments');
        Schema::dropIfExists('assets_inventories');
    }
};
