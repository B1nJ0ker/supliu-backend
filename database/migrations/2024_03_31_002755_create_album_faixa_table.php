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
        Schema::create('album_faixa', function (Blueprint $table) {
            $table->foreignId('album_id')->constrained('albuns')->onDelete('cascade');
            $table->foreignId('faixa_id')->constrained()->onDelete('cascade');
            $table->integer('numero')->nullable();
            $table->timestamps();
            $table->primary(['album_id', 'faixa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('album_faixa');
    }
};
