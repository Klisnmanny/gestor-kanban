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
        Schema::create('itens_checklist', function (Blueprint $table) {
            $table->id();

            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();

            $table->string('conteudo');
            $table->boolean('concluido')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_checklists');
    }
};
