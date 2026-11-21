<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se emite cuando una publicación se elimina, se rechaza o vuelve a borrador:
 * la portada debe retirarla sin recargar la página.
 */
class PublicacionRetirada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $key,
        public string $categoria,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('publicaciones')];
    }

    public function broadcastAs(): string
    {
        return 'publicacion.retirada';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'key' => $this->key,
            'categoria' => $this->categoria,
        ];
    }
}
