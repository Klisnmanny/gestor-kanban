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
        Schema::create('colunas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quadro_id')->constrained('quadros')->cascadeOnDelete();

            $table->string('nome');
            $table->integer('ordem')->default(0);

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colunas');
    }
};
