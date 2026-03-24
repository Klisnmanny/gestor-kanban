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
            // Adicionar coluna status se não existir (posiciona após 'cor' se existir, senão após 'descricao')
            if (!Schema::hasColumn('tarefas', 'status')) {
                $afterColumn = Schema::hasColumn('tarefas', 'cor') ? 'cor' : 'descricao';
                $table->string('status')->default('pendente')->after($afterColumn);
            }
            
            // Adicionar colunas JSON se não existirem
            if (!Schema::hasColumn('tarefas', 'checklist_data')) {
                $table->json('checklist_data')->nullable()->after('data_fim');
            }
            if (!Schema::hasColumn('tarefas', 'etiquetas_data')) {
                $table->json('etiquetas_data')->nullable()->after('checklist_data');
            }
            if (!Schema::hasColumn('tarefas', 'membros_data')) {
                $table->json('membros_data')->nullable()->after('etiquetas_data');
            }
            if (!Schema::hasColumn('tarefas', 'comentarios_data')) {
                $table->json('comentarios_data')->nullable()->after('membros_data');
            }
        });
    }

    /**
     * Run the migrations.
     */
    public function down(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropColumn('checklist_data');
            $table->dropColumn('etiquetas_data');
            $table->dropColumn('membros_data');
            $table->dropColumn('comentarios_data');
        });
    }
};
