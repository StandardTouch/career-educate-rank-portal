<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUttarakhandMbbs2025RoundsTable extends Migration
{
    public function up()
    {
        Schema::create('uttarakhand_mbbs_2025_rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('round_id');
            $table->foreign('round_id', 'fk_uttarakhand_mbbs_2025_rounds_d0d0f9_rnd')->references('id')->on('rounds');
            $table->string('college_name')->nullable();
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
        Schema::dropIfExists('uttarakhand_mbbs_2025_rounds');
    }
}
?>