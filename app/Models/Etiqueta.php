<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etiqueta extends Model
{
    protected $table = 'etiquetas';

    protected $fillable = [
        'quadro_id',
        'nome',
        'cor',
        'tipo',
        'descricao'
    ];

    public function quadro()
    {
        return $this->belongsTo(Quadro::class);
    }

    public function tarefas()
    {
        return $this->belongsToMany(Tarefa::class, 'tarefa_etiquetas');
    }
}