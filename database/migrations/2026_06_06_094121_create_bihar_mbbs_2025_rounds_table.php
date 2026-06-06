<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBiharMbbs2025RoundsTable extends Migration
{
    public function up()
    {
        Schema::create('bihar_mbbs_2025_rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('round_id');
            $table->foreign('round_id', 'fk_bihar_mbbs_2025_rounds_2002ba_rnd')->references('id')->on('rounds');
            $table->string('college_name');
            $table->string('category')->nullable();
            $table->string('local_area')->nullable();
            $table->string('quota')->nullable();
            $table->string('admission')->nullable();
            $table->integer('rank')->nullable();
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
        Schema::dropIfExists('bihar_mbbs_2025_rounds');
    }
}
?>