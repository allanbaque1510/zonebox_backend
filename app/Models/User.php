<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'id_tipo_usuario',
        'cedula',
        'telefono',
        'email',
        'password',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['primer_nombre', 'primer_apellido'];

    protected $with = ['tipoUsuario', 'casillero'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }
    protected function primerNombre(): Attribute {
        return Attribute::make(
            get: fn () => ucwords(strtolower(explode(' ', $this->nombre)[0]))
        );
    }

    protected function primerApellido(): Attribute {
        return Attribute::make(
            get: fn () => ucwords(strtolower(explode(' ', $this->apellido)[0]))
        );
    }

    protected function nombre(): Attribute {
        return Attribute::make(
            get: fn ($value) => ucwords(strtolower($value))
        );
    }
    
    protected function apellido(): Attribute {
        return Attribute::make(
            get: fn ($value) => ucwords(strtolower($value))
        );
    }

    public function tipoUsuario()
    {
        return $this->belongsTo(TipoUsuario::class, 'id_tipo_usuario');
    }

    public function casillero()
    {
        return $this->hasOne(Casillero::class, 'id_usuario');
    }

}
