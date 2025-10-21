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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('id_groupe')->constrained('groupes')->onDelete('cascade');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->integer('duree_minutes');
            $table->integer('max_questions');
            $table->integer('note_max');
            $table->timestamp('date_declechement')->nullable();
            $table->enum('status', ['brouillon', 'actif', 'ferme'])->default('brouillon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
