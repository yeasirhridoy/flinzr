<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->text('fcm_token')->nullable()->change();
            $table->boolean('is_active_notification')->default(true)->change();
            $table->boolean('is_logged')->default(false)->change();
            $table->string('device_type')->nullable()->change();
            $table->text('device_details')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->text('fcm_token')->nullable(false)->change();
            $table->boolean('is_active_notification')->default(false)->change();
            $table->boolean('is_logged')->default(false)->change();
            $table->string('device_type')->nullable(false)->change();
            $table->text('device_details')->nullable(false)->change();
        });
    }
};
