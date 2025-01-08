<?php

use App\Models\Country;
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
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropColumn('full_name');
            $table->dropColumn('id_no');
            $table->dropColumn('phone');
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->string('full_name');
            $table->string('id_no');
            $table->string('phone');
            $table->foreignIdFor(Country::class)->nullable()->constrained()->nullOnDelete();
        });
    }
};
