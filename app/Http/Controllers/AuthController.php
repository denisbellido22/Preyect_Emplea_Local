<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Formulario público de registro (RF01).
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Registra un Empleador o Postulante. Nunca un Administrador (RF01).
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:empleador,postulante'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $partes = preg_split('/\s+/', trim($data['name']), 2);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'nombres' => $partes[0] ?? $data['name'],
            'apellidos' => $partes[1] ?? null,
            'telefono' => $data['telefono'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', '¡Bienvenido a Emplea Local! Tu cuenta fue creada como '.ucfirst($user->role).'.');
    }

    /**
     * Formulario de inicio de sesión (RF02).
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Autentica al usuario con credenciales reales (RF02).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Sesión iniciada correctamente.');
        }

        return back()
            ->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.'])
            ->onlyInput('email');
    }

    /**
     * Cierra la sesión del usuario (RF02).
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Tu sesión fue cerrada correctamente.');
    }
}
