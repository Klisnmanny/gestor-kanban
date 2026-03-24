<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anexo extends Model
{
    protected $table = 'anexos';

    protected $fillable = [
        'tarefa_id',
        'caminho_arquivo'
    ];

    public function tarefa()
    {
        return $this->belongsTo(Tarefa::class);
    }
}