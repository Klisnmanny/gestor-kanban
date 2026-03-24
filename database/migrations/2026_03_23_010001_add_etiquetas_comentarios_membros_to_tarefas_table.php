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
        Schema::table('tarefas', function (Blueprint $table) {
            // Proteger contra colunas já existentes (já criadas em migration anterior)
            if (!Schema::hasColumn('tarefas', 'etiquetas_data')) {
                $table->json('etiquetas_data')->nullable()->after('descricao');
            }
            if (!Schema::hasColumn('tarefas', 'comentarios_data')) {
                $table->json('comentarios_data')->nullable()->after('etiquetas_data');
            }
            if (!Schema::hasColumn('tarefas', 'membros_data')) {
                $table->json('membros_data')->nullable()->after('comentarios_data');
            }
            if (!Schema::hasColumn('tarefas', 'checklist_data')) {
                $table->json('checklist_data')->nullable()->after('membros_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            // Remover apenas colunas que existem
            $drops = [];
            foreach (['etiquetas_data', 'comentarios_data', 'membros_data', 'checklist_data'] as $col) {
                if (Schema::hasColumn('tarefas', $col)) {
                    $drops[] = $col;
                }
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
