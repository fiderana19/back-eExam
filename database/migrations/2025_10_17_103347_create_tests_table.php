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
            $table->id('id_test');
            $table->foreignId('id_utilisateur')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('id_groupe')->constrained('groupes')->onDelete('cascade');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->integer('duree_minutes');
            $table->integer('max_questions')->default(15);
            $table->integer('note_max')->default(20);
            $table->timestamp('date_declechement')->nullable()->default(null);
            $table->enum('status', ['En attente', 'En cours', 'Terminé'])->default('En attente');
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
