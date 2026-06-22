<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_datasets', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->string('course')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('quota')->nullable();
            $table->string('descriptor')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->nullable();
            $table->timestamps();
        });

        Schema::create('analysis_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_dataset_id')->nullable()->constrained('analysis_datasets')->cascadeOnDelete();
            $table->string('name');           // e.g. "Round 1"
            $table->string('slug')->unique(); // e.g. "round_1"
            $table->unsignedTinyInteger('round_number')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['analysis_dataset_id', 'sort_order'], 'analysis_rounds_dataset_sort_index');
        });

        Schema::create('analysis_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_dataset_id')->constrained('analysis_datasets')->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('status')->default('pending')->index();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_rows')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('analysis_import_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_import_id')->constrained('analysis_imports')->cascadeOnDelete();
            $table->string('sheet_name');
            $table->string('sheet_type')->index();
            $table->foreignId('analysis_round_id')->nullable()->constrained('analysis_rounds')->nullOnDelete();
            $table->unsignedInteger('row_count')->nullable();
            $table->timestamps();
        });

        Schema::create('analysis_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_dataset_id')->constrained('analysis_datasets')->cascadeOnDelete();
            $table->foreignId('analysis_import_id')->constrained('analysis_imports')->cascadeOnDelete();
            $table->foreignId('analysis_import_sheet_id')->nullable()->constrained('analysis_import_sheets')->nullOnDelete();
            $table->foreignId('analysis_round_id')->nullable()->constrained('analysis_rounds')->nullOnDelete();
            $table->string('college_name')->nullable()->index();
            $table->string('course')->nullable()->index();
            $table->string('quota')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->string('local_area')->nullable()->index();
            $table->unsignedInteger('seats')->nullable();
            $table->unsignedBigInteger('opening_rank')->nullable();
            $table->unsignedBigInteger('closing_rank')->nullable()->index();
            $table->decimal('marks', 8, 2)->nullable();
            $table->unsignedBigInteger('fem_closing_rank')->nullable()->index();
            $table->decimal('fem_closing_mark', 8, 2)->nullable();
            $table->decimal('fees', 20, 2)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['analysis_dataset_id', 'analysis_round_id'], 'analysis_records_dataset_round_index');
            $table->index(['analysis_dataset_id', 'closing_rank'], 'analysis_records_dataset_rank_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_records');
        Schema::dropIfExists('analysis_import_sheets');
        Schema::dropIfExists('analysis_imports');
        Schema::dropIfExists('analysis_rounds');
        Schema::dropIfExists('analysis_datasets');
    }
};
