<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelanganaBdsGovtQuota2025Table extends Migration
{
    public function up()
    {
        Schema::create('telangana_bds_govt_quota_2025', function (Blueprint $table) {
            $table->id();
            $table->string('state_name')->nullable();
            $table->string('college_name')->nullable();
            $table->string('category')->nullable();
            $table->string('local_area')->nullable();
            $table->unsignedInteger('total_seats')->nullable();
            $table->string('quota')->nullable();
            $table->string('admission')->nullable();
            $table->unsignedBigInteger('rank')->nullable();
            $table->unsignedBigInteger('gen_closing_rank')->nullable();
            $table->unsignedBigInteger('fem_closing_rank')->nullable();
            $table->decimal('gen_closing_mark', 8, 2)->nullable();
            $table->decimal('fem_closing_mark', 8, 2)->nullable();
            $table->decimal('fees', 20, 2)->nullable();
            $table->decimal('tuition_fee', 20, 2)->nullable();
            $table->decimal('total_fee', 20, 2)->nullable();
            $table->string('seat_type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('telangana_bds_govt_quota_2025');
    }
}
?>