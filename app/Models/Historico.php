<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historico extends Model
{
    protected $table = 'historicos';

    protected $fillable = [
        'tarefa_id',
        'user_id',
        'acao'
    ];

    public function tarefa()
    {
        return $this->belongsTo(Tarefa::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}