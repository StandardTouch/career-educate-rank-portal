<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('alias')->unique();
            $table->string('course')->index();
            $table->string('source')->default('admin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_mappings');
    }
};
