<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quadro extends Model
{
    protected $table = 'quadros';

    protected $fillable = [
        'nome',
        'descricao',
        'publico',
        'user_id',
        'status'
    ];

    public function dono()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'quadro_usuarios')
            ->withPivot('papel')
            ->withTimestamps();
    }

    public function colunas()
    {
        return $this->hasMany(Coluna::class)->orderBy('ordem');
    }

    public function etiquetas()
    {
        return $this->hasMany(Etiqueta::class);
    }
}