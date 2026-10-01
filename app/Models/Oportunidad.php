<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Oportunidad extends Model
{
    use HasFactory;

    public const ESTADO_PUBLICADO = 'publicado';
    public const ESTADO_BORRADOR = 'borrador';

    /** Tipos permitidos (RF14). */
    public const TIPOS = [
        'beca',
        'charla',
        'capacitacion',
        'curso',
        'evento',
        'otra',
    ];

    protected $table = 'oportunidades';

    protected $fillable = [
        'user_id',
        'titulo',
        'tipo',
        'descripcion',
        'institucion',
        'fecha',
        'lugar',
        'enlace',
        'imagen',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    /**
     * Administrador que creó la oportunidad.
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Sólo oportunidades publicadas (visibles para los usuarios).
     */
    public function scopePublicadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PUBLICADO);
    }
}
