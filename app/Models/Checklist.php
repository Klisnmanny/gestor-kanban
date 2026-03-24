<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $table = 'checklists';

    protected $fillable = [
        'tarefa_id',
        'titulo'
    ];

    public function tarefa()
    {
        return $this->belongsTo(Tarefa::class);
    }

    public function itens()
    {
        return $this->hasMany(ItemChecklist::class);
    }
}