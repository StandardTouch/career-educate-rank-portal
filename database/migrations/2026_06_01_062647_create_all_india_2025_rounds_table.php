<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('all_india_rounds_2025', function (Blueprint $table) {
            $table->id();

            // Foreign key to rounds
            $table->foreignId('round_id')
                ->constrained('rounds')
                ->cascadeOnDelete();

            // Core college info
            $table->string('state_name');
            $table->text('college_name');
            $table->string('category');
            $table->string('local_area');
            $table->unsignedSmallInteger('total_seats');

            // Rank & Mark columns (nullable — FEM columns are often partial)
            $table->unsignedBigInteger('gen_closing_rank')->nullable();
            $table->unsignedBigInteger('fem_closing_rank')->nullable();
            $table->decimal('gen_closing_mark', 6, 2)->nullable();
            $table->decimal('fem_closing_mark', 6, 2)->nullable();

            // Tuition fee stored as integer (paise stripped, raw INR)
            $table->unsignedInteger('tuition_fee')->nullable();

            $table->timestamps();

            // Indexes for common query patterns
            $table->index('round_id');
            $table->index('state_name');
            $table->index('category');
            $table->index('gen_closing_rank');
            $table->index('gen_closing_mark');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('all_india_rounds_2025');
    }
};
