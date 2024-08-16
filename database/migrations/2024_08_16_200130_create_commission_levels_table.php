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
        Schema::create('commission_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('level_1_target');
            $table->integer('level_1_commission');
            $table->integer('level_2_target');
            $table->integer('level_2_commission');
            $table->integer('level_3_target');
            $table->integer('level_3_commission');
            $table->integer('level_4_target');
            $table->integer('level_4_commission');
            $table->integer('level_5_target');
            $table->integer('level_5_commission');
            $table->integer('level_6_target');
            $table->integer('level_6_commission');
            $table->integer('level_7_target');
            $table->integer('level_7_commission');
            $table->integer('level_8_target');
            $table->integer('level_8_commission');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_levels');
    }
};
