<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\MailFailureReporter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerException;

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

        // El correo queda pendiente de verificación: registrarse nunca lo marca
        // como verificado, sólo el enlace enviado a la bandeja del usuario.
        try {
            $user->sendEmailVerificationNotification();
        } catch (MailerException $e) {
            MailFailureReporter::report($e, 'No se pudo enviar el correo de verificación tras el registro.');

            return redirect()->route('verification.notice')->with(
                'error',
                __('Tu cuenta fue creada, pero no pudimos enviar el correo de verificación. Puedes solicitar uno nuevo desde esta misma pantalla en unos minutos.')
            );
        }

        return redirect()->route('verification.notice')
            ->with(
                'success',
                __('¡Bienvenido a Emplea Local! Tu cuenta fue creada como :rol. Revisa tu correo para verificar tu dirección.', ['rol' => __('role.'.$user->role)])
            );
    }

    /**
     * Formulario de inicio de sesión (RF02).
     */
    public function showLogin()
    {
        $cuentasDemo = $this->cuentasDemo();

        return view('auth.login', [
            'cuentasDemo' => $cuentasDemo,
            'demoPassword' => $cuentasDemo->isNotEmpty() ? config('demo_accounts.password') : null,
        ]);
    }

    /**
     * Cuentas de prueba autorizadas para el acceso rápido del login.
     *
     * Solo se activan en desarrollo (config/demo_accounts.php) y únicamente se
     * exponen nombre, correo, rol y la contraseña de demostración: nunca hashes
     * ni cuentas reales ajenas al dominio de pruebas. En producción devuelve una
     * colección vacía y la sección no se muestra.
     */
    private function cuentasDemo(): Collection
    {
        if (! config('demo_accounts.enabled') || app()->environment('production')) {
            return collect();
        }

        $dominio = config('demo_accounts.allowed_domain');
        $orden = [User::ROLE_ADMIN => 1, User::ROLE_EMPLEADOR => 2, User::ROLE_POSTULANTE => 3];

        return User::query()
            ->whereIn('email', config('demo_accounts.emails', []))
            ->when($dominio, fn ($query) => $query->where('email', 'like', '%@'.$dominio))
            ->get(['name', 'email', 'role'])
            ->sortBy(fn ($usuario) => $orden[$usuario->role] ?? 4)
            ->values();
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
                ->with('success', __('Sesión iniciada correctamente.'));
        }

        return back()
            ->withErrors(['email' => __('Las credenciales no coinciden con nuestros registros.')])
            ->onlyInput('email');
    }

    /**
     * Formulario para solicitar el enlace de recuperación (RF02).
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Envía el enlace de recuperación al correo registrado (RF02).
     *
     * Usa el broker de contraseñas de Laravel sobre la tabla
     * password_reset_tokens, con token temporal y de un sólo uso.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (MailerException $e) {
            // Un fallo de SMTP no debe romper la pantalla ni revelar detalles
            // técnicos: se registra de forma saneada y se avisa al usuario.
            MailFailureReporter::report($e, 'No se pudo enviar el correo de recuperación de contraseña.');

            return back()
                ->withInput($request->only('email'))
                ->with('error', __('No pudimos enviar el correo de recuperación en este momento. Revisa la configuración de correo del sistema o inténtalo de nuevo más tarde.'));
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    /**
     * Formulario para definir la nueva contraseña desde el enlace del correo (RF02).
     */
    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Guarda la nueva contraseña si el token y el correo son válidos (RF02).
     *
     * El broker verifica que el token corresponda a esa cuenta, que no haya
     * expirado y que no se haya usado antes (al restablecer se elimina).
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', PasswordRule::min(8), 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Hash seguro de Laravel: nunca se guarda en texto plano.
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    /**
     * Cierra la sesión del usuario (RF02).
     */
    public function logout(Request $request)
    {
        // El idioma elegido no depende de la cuenta: se conserva tras cerrar
        // sesión para que la próxima visita siga en el mismo idioma.
        $locale = app()->getLocale();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('locale', $locale);

        return redirect()->route('home')->with('success', __('Tu sesión fue cerrada correctamente.'));
    }
}
