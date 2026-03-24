<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coluna extends Model
{
    protected $table = 'colunas';

    protected $fillable = [
        'quadro_id',
        'nome',
        'ordem',
        'user_id'
    ];

    public function quadro()
    {
        return $this->belongsTo(Quadro::class);
    }

    public function tarefas()
    {
        return $this->hasMany(Tarefa::class)->orderBy('ordem');
    }
}