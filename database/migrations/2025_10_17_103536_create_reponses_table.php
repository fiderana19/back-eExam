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
        Schema::create('reponses_etudiants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_question')->constrained('questions')->onDelete('cascade');
            $table->foreignId('id_tentative')->constrained('tentatives')->onDelete('cascade');
            $table->text('reponse_texte')->nullable();
            $table->float('score_question')->default(0);
            $table->boolean('est_corriger')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reponses');
    }
};
