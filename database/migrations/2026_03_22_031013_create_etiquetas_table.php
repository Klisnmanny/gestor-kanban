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
        Schema::create('etiquetas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quadro_id')->constrained('quadros')->cascadeOnDelete();

            $table->string('nome');
            $table->string('cor'); // ex: red, blue, green
            $table->enum('tipo', ['prioridade', 'tipo', 'contexto']);
            $table->text('descricao')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etiquetas');
    }
};
