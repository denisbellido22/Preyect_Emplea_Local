<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

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
     * Envía el enlace de recuperación con la plantilla propia de Emplea Local.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Envía el enlace de verificación con la plantilla propia de Emplea Local.
     *
     * La URL firmada, temporal y ligada al identificador y al correo del
     * usuario la genera Laravel (VerifyEmail::verificationUrl).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
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

    /**
     * Enlace para abrir el buzón del correo registrado.
     *
     * Para los proveedores más usados abre la bandeja de entrada en el
     * navegador; en cualquier otro caso delega en el cliente de correo del
     * dispositivo mediante mailto.
     */
    public function urlCorreo(): string
    {
        $dominio = strtolower(Str::after($this->email, '@'));

        $buzones = [
            'gmail.com' => 'https://mail.google.com/mail/u/0/#inbox',
            'googlemail.com' => 'https://mail.google.com/mail/u/0/#inbox',
            'outlook.com' => 'https://outlook.live.com/mail/0/',
            'hotmail.com' => 'https://outlook.live.com/mail/0/',
            'live.com' => 'https://outlook.live.com/mail/0/',
            'yahoo.com' => 'https://mail.yahoo.com/',
            'yahoo.es' => 'https://mail.yahoo.com/',
        ];

        return $buzones[$dominio] ?? 'mailto:'.$this->email;
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
