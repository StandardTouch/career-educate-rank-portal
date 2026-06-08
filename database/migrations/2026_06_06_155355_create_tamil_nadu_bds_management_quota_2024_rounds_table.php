<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTamilNaduBdsManagementQuota2024RoundsTable extends Migration
{
    public function up()
    {
        Schema::create('tamil_nadu_bds_management_quota_2024_rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('round_id');
            $table->foreign('round_id', 'fk_tamil_nadu_bds_management_quot_6a08f1_rnd')->references('id')->on('rounds');
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
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tamil_nadu_bds_management_quota_2024_rounds');
    }
}
?>