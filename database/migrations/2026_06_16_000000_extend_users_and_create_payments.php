<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('neet_rank')->nullable()->after('mobile_verified_at');
            $table->decimal('neet_marks', 5, 2)->nullable()->after('neet_rank');
            $table->string('quota')->nullable()->after('neet_marks');
            $table->string('category')->nullable()->after('quota');
            $table->string('state')->nullable()->after('category');
            $table->string('plan')->default('none')->after('state');
            $table->string('payment_status')->default('unpaid')->after('plan');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan');
            $table->decimal('amount', 10, 2);
            $table->string('transaction_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'neet_rank',
                'neet_marks',
                'quota',
                'category',
                'state',
                'plan',
                'payment_status'
            ]);
        });
    }
};
