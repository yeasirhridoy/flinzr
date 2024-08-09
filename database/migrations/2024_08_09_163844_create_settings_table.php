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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('privacy_policy')->nullable();
            $table->string('review_app')->nullable();
            $table->string('help_center')->nullable();
            $table->string('become_an_artist')->nullable();
            $table->string('upload_request_terms')->nullable();
            $table->string('payout_request_terms')->nullable();
            $table->integer('artist_commission_value')->nullable();
            $table->integer('filter_price')->nullable();
            $table->integer('special_request_price')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
