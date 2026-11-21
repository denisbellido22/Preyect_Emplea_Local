<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se emite cuando una oferta aprobada u oportunidad publicada pasa a ser
 * visible en la portada. El canal es público y el payload ya viene filtrado.
 */
class PublicacionPublicada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $publicacion
     */
    public function __construct(public array $publicacion) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('publicaciones')];
    }

    public function broadcastAs(): string
    {
        return 'publicacion.publicada';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['publicacion' => $this->publicacion];
    }
}
