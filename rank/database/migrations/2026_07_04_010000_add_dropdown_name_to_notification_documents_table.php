<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_documents', function (Blueprint $table) {
            $table->string('dropdown_name')->default('Notifications')->after('stored_path')->index();
        });
    }

    public function down(): void
    {
        Schema::table('notification_documents', function (Blueprint $table) {
            $table->dropColumn('dropdown_name');
        });
    }
};
