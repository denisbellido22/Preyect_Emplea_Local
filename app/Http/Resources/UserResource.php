<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Perfil del usuario autenticado. Se usa únicamente en /api/auth/me y en el
 * registro/login: nunca se incluye password, remember_token ni tokens.
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'role_label' => __('role.'.$this->role),
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'telefono' => $this->telefono,
            'distrito' => $this->distrito,
            'profesion' => $this->profesion,
            'experiencia' => $this->experiencia,
            'descripcion' => $this->descripcion,
            'empresa' => $this->empresa,
            'email_verified' => $this->hasVerifiedEmail(),
            'email_verified_at' => optional($this->email_verified_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
