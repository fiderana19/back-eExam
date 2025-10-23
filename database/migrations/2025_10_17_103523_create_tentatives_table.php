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
        Schema::create('tentatives', function (Blueprint $table) {
            $table->id('id_tentative');
            $table->foreignId('id_utilisateur')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('id_test')->constrained('tests')->onDelete('cascade');
            $table->timestamp('heure_debut')->useCurrent();
            $table->timestamp('heure_soumission')->nullable();
            $table->float('note_obtenue')->default(0);
            $table->boolean('est_noter')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tentatives');
    }
};
