<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllOverIndiaMbbs2024Table extends Migration
{
    public function up()
    {
        Schema::create('all_over_india_mbbs_2024', function (Blueprint $table) {
            $table->id();
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
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('all_over_india_mbbs_2024');
    }
}
?>