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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_test')->constrained('tests')->onDelete('cascade');
            $table->text('texte_question');
            $table->enum('type_question', ['qcm', 'reponse courte' , 'developpement']);
            $table->integer('points')->default(1);
            $table->text('reponse_correcte')->nullable(); // pour les questions ouvertes
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
