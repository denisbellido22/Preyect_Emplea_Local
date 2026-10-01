<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Roles válidos del sistema (RF20). */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EMPLEADOR = 'empleador';
    public const ROLE_POSTULANTE = 'postulante';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nombres',
        'apellidos',
        'dni',
        'telefono',
        'distrito',
        'descripcion',
        'profesion',
        'experiencia',
        'empresa',
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
        ];
    }

    /**
     * Ofertas laborales publicadas por el empleador.
     */
    public function ofertas(): HasMany
    {
        return $this->hasMany(Oferta::class);
    }

    /**
     * Postulaciones realizadas por el postulante.
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }

    /**
     * Solicitudes de publicidad registradas por el empleador.
     */
    public function publicidades(): HasMany
    {
        return $this->hasMany(Publicidad::class);
    }

    /**
     * Oportunidades creadas por el administrador.
     */
    public function oportunidades(): HasMany
    {
        return $this->hasMany(Oportunidad::class);
    }

    /**
     * Nombre para mostrar del usuario.
     */
    public function nombreCompleto(): string
    {
        $nombre = trim(($this->nombres ?? '').' '.($this->apellidos ?? ''));

        return $nombre !== '' ? $nombre : $this->name;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEmpleador(): bool
    {
        return $this->role === self::ROLE_EMPLEADOR;
    }

    public function isPostulante(): bool
    {
        return $this->role === self::ROLE_POSTULANTE;
    }
}
