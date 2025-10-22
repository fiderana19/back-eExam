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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_groupe')->constrained('groupes')->onDelete('cascade');
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('matricule')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'enseignant', 'etudiant'])->default('etudiant');
            $table->boolean('est_valider')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
