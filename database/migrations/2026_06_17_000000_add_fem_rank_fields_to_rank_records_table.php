<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rank_records', function (Blueprint $table) {
            if (! Schema::hasColumn('rank_records', 'fem_closing_rank')) {
                $table->unsignedBigInteger('fem_closing_rank')->nullable()->after('marks')->index();
            }

            if (! Schema::hasColumn('rank_records', 'fem_closing_mark')) {
                $table->decimal('fem_closing_mark', 8, 2)->nullable()->after('fem_closing_rank');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rank_records', function (Blueprint $table) {
            if (Schema::hasColumn('rank_records', 'fem_closing_mark')) {
                $table->dropColumn('fem_closing_mark');
            }

            if (Schema::hasColumn('rank_records', 'fem_closing_rank')) {
                $table->dropColumn('fem_closing_rank');
            }
        });
    }
};
