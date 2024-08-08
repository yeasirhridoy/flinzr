<?php

use App\Models\Collection;
use App\Models\Color;
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
        Schema::create('collection_color', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Collection::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Color::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_color');
    }
};
