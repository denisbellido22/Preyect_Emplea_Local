{{ $appName }} · {{ __('Verifica tu correo electrónico') }}

{{ __('Hola, :nombre:', ['nombre' => $nombre]) }}

{{ __('Gracias por crear tu cuenta en :app. Para confirmar que esta dirección de correo te pertenece, abre este enlace para verificar tu cuenta:', ['app' => $appName]) }}

{{ $actionUrl }}

{{ __('El enlace caduca en :minutos minutos. Después de ese tiempo deberás solicitar uno nuevo desde tu cuenta.', ['minutos' => $expiraMinutos]) }}

{{ __('¿No creaste esta cuenta? Si no te registraste en :app, ignora este mensaje: tu dirección no quedará verificada. Nunca compartas este enlace con nadie; :app jamás te pedirá tu contraseña por correo, teléfono o mensajería.', ['app' => $appName]) }}

© {{ date('Y') }} {{ $appName }} · {{ __('Este es un correo automático, por favor no respondas.') }}
