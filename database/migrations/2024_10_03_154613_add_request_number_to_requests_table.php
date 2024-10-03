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
        Schema::table('artist_requests', function (Blueprint $table) {
            $table->string('request_number')->nullable();
        });
        Schema::table('influencer_requests', function (Blueprint $table) {
            $table->string('request_number')->nullable();
        });
        Schema::table('special_requests', function (Blueprint $table) {
            $table->string('request_number')->nullable();
        });
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->string('request_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artist_requests', function (Blueprint $table) {
            $table->dropColumn('request_number');
        });
        Schema::table('influencer_requests', function (Blueprint $table) {
            $table->dropColumn('request_number');
        });
        Schema::table('special_requests', function (Blueprint $table) {
            $table->dropColumn('request_number');
        });
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropColumn('request_number');
        });
    }
};
