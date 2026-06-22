<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
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

        Schema::table('rounds', function (Blueprint $table) {
            $table->foreignId('dataset_id')->nullable()->after('id')->constrained('datasets')->cascadeOnDelete();
            $table->unsignedTinyInteger('round_number')->nullable()->after('slug');
            $table->index(['dataset_id', 'sort_order'], 'rounds_dataset_sort_index');
        });

        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('status')->default('pending')->index();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_rows')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('import_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->string('sheet_name');
            $table->string('sheet_type')->index();
            $table->foreignId('round_id')->nullable()->constrained('rounds')->nullOnDelete();
            $table->unsignedInteger('row_count')->nullable();
            $table->timestamps();
        });

        Schema::create('rank_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->foreignId('import_sheet_id')->nullable()->constrained('import_sheets')->nullOnDelete();
            $table->foreignId('round_id')->nullable()->constrained('rounds')->nullOnDelete();
            $table->string('college_name')->nullable()->index();
            $table->string('course')->nullable()->index();
            $table->string('quota')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->string('local_area')->nullable()->index();
            $table->unsignedInteger('seats')->nullable();
            $table->unsignedBigInteger('opening_rank')->nullable();
            $table->unsignedBigInteger('closing_rank')->nullable()->index();
            $table->decimal('marks', 8, 2)->nullable();
            $table->decimal('fees', 20, 2)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['dataset_id', 'round_id']);
            $table->index(['dataset_id', 'closing_rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_records');
        Schema::dropIfExists('import_sheets');
        Schema::dropIfExists('imports');

        Schema::table('rounds', function (Blueprint $table) {
            $table->dropIndex('rounds_dataset_sort_index');
            $table->dropConstrainedForeignId('dataset_id');
            $table->dropColumn('round_number');
        });

        Schema::dropIfExists('datasets');
    }
};
