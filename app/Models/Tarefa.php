<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarefa extends Model
{
    protected $table = 'tarefas';

    protected $fillable = [
        'coluna_id',
        'titulo',
        'descricao',
        'cor',
        'ordem',
        'status',
        'user_id',
        'usuario_responsavel_id',
        'data_inicio',
        'data_fim',
        'etiquetas_data',
        'comentarios_data',
        'membros_data',
        'checklist_data'
    ];

    protected $casts = [
        'etiquetas_data' => 'array',
        'comentarios_data' => 'array',
        'membros_data' => 'array',
        'checklist_data' => 'array',
    ];

    public function coluna()
    {
        return $this->belongsTo(Coluna::class);
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'usuario_responsavel_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function etiquetas()
    {
        return $this->belongsToMany(Etiqueta::class, 'tarefa_etiquetas');
    }

    public function anexos()
    {
        return $this->hasMany(Anexo::class);
    }
}