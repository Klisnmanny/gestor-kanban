<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuadroUsuario extends Model
{
    protected $table = 'quadro_usuarios';

    protected $fillable = [
        'quadro_id',
        'user_id',
        'papel'
    ];
}