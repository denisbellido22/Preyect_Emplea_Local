<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Models\Postulacion;
use App\Models\Publicidad;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard según el rol del usuario. El panel admin usa datos reales (RF19).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            User::ROLE_ADMIN => $this->admin(),
            User::ROLE_EMPLEADOR => $this->empleador($user),
            default => $this->postulante($user),
        };
    }

    /**
     * Panel de administración con cantidades reales desde la base de datos.
     */
    private function admin()
    {
        $metricas = [
            'usuarios' => User::count(),
            'administradores' => User::where('role', User::ROLE_ADMIN)->count(),
            'empleadores' => User::where('role', User::ROLE_EMPLEADOR)->count(),
            'postulantes' => User::where('role', User::ROLE_POSTULANTE)->count(),
            'ofertas' => Oferta::count(),
            'ofertas_pendientes' => Oferta::where('estado', Oferta::ESTADO_PENDIENTE)->count(),
            'ofertas_aprobadas' => Oferta::where('estado', Oferta::ESTADO_APROBADO)->count(),
            'ofertas_rechazadas' => Oferta::where('estado', Oferta::ESTADO_RECHAZADO)->count(),
            'postulaciones' => Postulacion::count(),
            'oportunidades' => Oportunidad::count(),
            'publicidades' => Publicidad::count(),
            'publicidades_pendientes' => Publicidad::where('estado', Publicidad::ESTADO_PENDIENTE)->count(),
        ];

        $ultimasOfertas = Oferta::with('empleador')->latest()->take(5)->get();
        $ultimasPublicidades = Publicidad::with('empleador')->latest()->take(5)->get();

        return view('dashboard.admin', compact('metricas', 'ultimasOfertas', 'ultimasPublicidades'));
    }

    /**
     * Panel de Empleador: sus ofertas, postulaciones recibidas y publicidad.
     */
    private function empleador(User $user)
    {
        $ofertas = $user->ofertas();
        $idsOfertas = $ofertas->pluck('id');

        $metricas = [
            'ofertas' => $ofertas->count(),
            'ofertas_pendientes' => (clone $ofertas)->where('estado', Oferta::ESTADO_PENDIENTE)->count(),
            'ofertas_aprobadas' => (clone $ofertas)->where('estado', Oferta::ESTADO_APROBADO)->count(),
            'postulaciones' => Postulacion::whereIn('oferta_id', $idsOfertas)->count(),
            'publicidades' => $user->publicidades()->count(),
        ];

        $ultimasPostulaciones = Postulacion::with(['postulante', 'oferta'])
            ->whereIn('oferta_id', $idsOfertas)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.empleador', compact('metricas', 'ultimasPostulaciones'));
    }

    /**
     * Panel del Postulante: sus postulaciones y ofertas disponibles.
     */
    private function postulante(User $user)
    {
        $metricas = [
            'postulaciones' => $user->postulaciones()->count(),
            'postulaciones_pendientes' => $user->postulaciones()->where('estado', Postulacion::ESTADO_PENDIENTE)->count(),
            'postulaciones_aceptadas' => $user->postulaciones()->where('estado', Postulacion::ESTADO_ACEPTADA)->count(),
            'ofertas_disponibles' => Oferta::aprobadas()->count(),
        ];

        $misPostulaciones = $user->postulaciones()->with('oferta')->latest()->take(5)->get();
        $ofertasRecientes = Oferta::aprobadas()->with('empleador')->latest()->take(4)->get();

        return view('dashboard.postulante', compact('metricas', 'misPostulaciones', 'ofertasRecientes'));
    }
}
