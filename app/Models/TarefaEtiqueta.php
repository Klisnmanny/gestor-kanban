<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarefaEtiqueta extends Model
{
    protected $table = 'tarefa_etiquetas';

    protected $fillable = [
        'tarefa_id',
        'etiqueta_id'
    ];
}