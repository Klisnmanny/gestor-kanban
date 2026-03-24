<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'foto'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 🔥 RELACIONAMENTOS

    public function quadros()
    {
        return $this->hasMany(Quadro::class);
    }

    public function quadrosParticipando()
    {
        return $this->belongsToMany(Quadro::class, 'quadro_usuarios')
            ->withPivot('papel')
            ->withTimestamps();
    }

    public function tarefasCriadas()
    {
        return $this->hasMany(Tarefa::class, 'user_id');
    }

    public function tarefasResponsavel()
    {
        return $this->hasMany(Tarefa::class, 'usuario_responsavel_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }
}