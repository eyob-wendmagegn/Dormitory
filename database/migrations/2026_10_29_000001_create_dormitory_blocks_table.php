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
        Schema::create('dormitory_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('block_code')->unique();
            $table->enum('gender_category', ['male', 'female'])->default('male')->comment('Determines which students may reside in this block');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamps();

            $table->index('status');
            $table->index('gender_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dormitory_blocks');
    }
};
