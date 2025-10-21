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
        Schema::create('options_qcm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_question')->constrained('questions')->onDelete('cascade');
            $table->text('texte_option');
            $table->boolean('est_correcte')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_qcms');
    }
};
