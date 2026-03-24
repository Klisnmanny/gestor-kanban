<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemChecklist extends Model
{
    protected $table = 'itens_checklist';

    protected $fillable = [
        'checklist_id',
        'conteudo',
        'concluido'
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }
}