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
            $table->dropColumn('full_name');
            $table->dropColumn('phone');
            $table->dropColumn('id_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artist_requests', function (Blueprint $table) {
            $table->string('full_name');
            $table->string('phone');
            $table->string('id_no');
        });
    }
};
