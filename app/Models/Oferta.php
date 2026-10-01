<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Oferta extends Model
{
    use HasFactory;

    /** Estados de la oferta (RF04/RF06). */
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_APROBADO = 'aprobado';
    public const ESTADO_RECHAZADO = 'rechazado';

    protected $table = 'ofertas';

    protected $fillable = [
        'user_id',
        'titulo',
        'descripcion',
        'requisitos',
        'lugar',
        'tipo_trabajo',
        'salario',
        'fecha_limite',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
            'salario' => 'decimal:2',
        ];
    }

    /**
     * Empleador que publicó la oferta.
     */
    public function empleador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Postulaciones recibidas para esta oferta.
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }

    /**
     * Sólo ofertas aprobadas (visibles públicamente).
     */
    public function scopeAprobadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_APROBADO);
    }

    /**
     * Búsqueda real por título, ubicación o tipo de trabajo (RF07).
     */
    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        if (blank($termino)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($termino) {
            $q->where('titulo', 'like', "%{$termino}%")
                ->orWhere('lugar', 'like', "%{$termino}%")
                ->orWhere('tipo_trabajo', 'like', "%{$termino}%");
        });
    }
}
