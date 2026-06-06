<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAndhraPradeshBdsGovtQuota2024RoundsTable extends Migration
{
    public function up()
    {
        Schema::create('andhra_pradesh_bds_govt_quota_-_2024_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('rounds');
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
        Schema::dropIfExists('andhra_pradesh_bds_govt_quota_-_2024_rounds');
    }
}
?>