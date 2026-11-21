<?php

namespace App\Http\Controllers;

use App\Support\MailFailureReporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerException;

class ProfileController extends Controller
{
    /**
     * Muestra el perfil del usuario autenticado (RF03).
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Datos que se eliminarán junto con la cuenta (RF03: aviso antes de borrar).
        $afectados = [];

        if ($user->isEmpleador()) {
            if ($total = $user->ofertas()->count()) {
                $afectados[] = __(':count oferta(s) de empleo publicada(s), junto con las postulaciones recibidas en ellas.', ['count' => $total]);
            }

            if ($total = $user->publicidades()->count()) {
                $afectados[] = __(':count solicitud(es) de publicidad.', ['count' => $total]);
            }
        }

        if ($user->isPostulante() && ($total = $user->postulaciones()->count())) {
            $afectados[] = __(':count postulación(es) a ofertas de empleo.', ['count' => $total]);
        }

        if ($user->isAdmin() && $user->oportunidades()->exists()) {
            $afectados[] = __('Los registros de oportunidades que publiqué se conservan, pero quedarán sin autor.');
        }

        return view('perfil.show', compact('user', 'afectados'));
    }

    /**
     * Formulario de edición del perfil (RF03).
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('perfil.edit', compact('user'));
    }

    /**
     * Actualiza el perfil. Los campos dependen del rol (RF03).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'distrito' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ];

        if ($user->isPostulante()) {
            $rules['dni'] = ['required', 'numeric', 'digits:8', Rule::unique('users', 'dni')->ignore($user->id)];
            $rules['profesion'] = ['required', 'string', 'max:150'];
            $rules['experiencia'] = ['nullable', 'string', 'max:3000'];
        } else {
            $rules['empresa'] = ['nullable', 'string', 'max:150'];
            $rules['dni'] = ['nullable', 'numeric', 'digits:8', Rule::unique('users', 'dni')->ignore($user->id)];
            $rules['profesion'] = ['nullable', 'string', 'max:150'];
        }

        $data = $request->validate($rules);

        $emailAnterior = $user->email;

        $user->update($data);

        $mensaje = __('Perfil actualizado correctamente.');

        // Si el correo cambió, la verificación anterior deja de ser válida:
        // el usuario debe confirmar la nueva dirección desde su bandeja.
        if (strcasecmp($emailAnterior, $user->email) !== 0) {
            $user->forceFill(['email_verified_at' => null])->save();

            try {
                $user->sendEmailVerificationNotification();
                $mensaje = __('Perfil actualizado correctamente. Como cambiaste tu correo, te enviamos un enlace para verificar la nueva dirección.');
            } catch (MailerException $e) {
                MailFailureReporter::report($e, 'No se pudo enviar el correo de verificación tras cambiar el correo del perfil.');

                return redirect()->route('perfil.show')
                    ->with('success', __('Perfil actualizado correctamente.'))
                    ->with('error', __('Cambiaste tu correo, pero no pudimos enviar el enlace de verificación. Solicítalo de nuevo desde «Verificación de correo electrónico».'));
            }
        }

        return redirect()->route('perfil.show')->with('success', $mensaje);
    }

    /**
     * Formulario de cambio de contraseña del usuario autenticado (RF03).
     * Disponible para cualquier rol: admin, empleador y postulante.
     */
    public function editPassword(Request $request)
    {
        $user = $request->user();

        return view('perfil.password', compact('user'));
    }

    /**
     * Actualiza la contraseña de la cuenta autenticada (RF03).
     *
     * La verificación y el guardado se hacen en el servidor y siempre sobre
     * $request->user(), por lo que un usuario sólo puede cambiar su propia
     * contraseña aunque manipule el formulario o la petición.
     */
    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        $user = $request->user();

        // Hash seguro de Laravel: la contraseña nunca se guarda en texto plano.
        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('perfil.show')
            ->with('success', __('Tu contraseña se actualizó correctamente.'));
    }

    /**
     * Elimina la cuenta del usuario autenticado (RF03).
     *
     * Exige la contraseña actual y una confirmación explícita. El borrado se
     * hace siempre sobre $request->user(), por lo que una petición manipulada
     * no puede eliminar la cuenta de otra persona. Las relaciones se resuelven
     * con las reglas ya definidas en el esquema (ofertas, postulaciones y
     * publicidades en cascada; oportunidades.user_id en NULL).
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'confirmar_eliminacion' => ['accepted'],
        ], [
            'confirmar_eliminacion.accepted' => __('Debes marcar la casilla de confirmación para eliminar tu cuenta.'),
        ]);

        // El rol administrador no puede eliminar su propia cuenta.
        if ($user->isAdmin()) {
            return redirect()->route('perfil.show')->with(
                'error',
                __('Los administradores no pueden eliminar su cuenta.')
            );
        }

        // Se cierra la sesión ANTES de borrar la cuenta: al cerrar sesión Laravel
        // renueva el remember_token y guarda el modelo, lo que volvería a insertar
        // el registro que acabamos de eliminar.
        Auth::logout();

        DB::transaction(fn () => $user->delete());

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with(
            'success',
            __('Tu cuenta y sus datos asociados fueron eliminados. Gracias por haber usado Emplea Local.')
        );
    }
}
