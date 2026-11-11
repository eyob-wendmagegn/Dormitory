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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('dormitory_blocks')->cascadeOnDelete();
            $table->string('room_number');
            $table->unsignedInteger('capacity')->default(4);
            $table->enum('status', ['available', 'full', 'maintenance'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['block_id', 'room_number']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
