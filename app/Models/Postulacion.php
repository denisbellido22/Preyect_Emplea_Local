<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Postulacion extends Model
{
    use HasFactory;

    /** Estados de la postulación (RF11). */
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_REVISADA = 'revisada';
    public const ESTADO_ACEPTADA = 'aceptada';
    public const ESTADO_RECHAZADA = 'rechazada';

    protected $table = 'postulaciones';

    protected $fillable = [
        'oferta_id',
        'user_id',
        'estado',
        'mensaje',
    ];

    /**
     * Oferta a la que se postula.
     */
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class);
    }

    /**
     * Postulante que realizó la postulación.
     */
    public function postulante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
