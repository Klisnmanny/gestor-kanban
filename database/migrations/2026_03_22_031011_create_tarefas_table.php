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
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coluna_id')->constrained('colunas')->cascadeOnDelete();

            $table->string('titulo');
            $table->text('descricao')->nullable();

            $table->integer('ordem')->default(0);

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('usuario_responsavel_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
