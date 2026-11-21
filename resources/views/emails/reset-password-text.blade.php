{{ $appName }} · {{ __('Restablece tu contraseña') }}

{{ __('Hola, :nombre:', ['nombre' => $nombre]) }}

{{ __('Recibimos una solicitud para restablecer la contraseña de tu cuenta en :app. Si fuiste tú, abre este enlace para crear una contraseña nueva:', ['app' => $appName]) }}

{{ $actionUrl }}

{{ __('El enlace caduca en :minutos minutos y solo puede utilizarse una vez.', ['minutos' => $expiraMinutos]) }}

{{ __('Aviso de seguridad: si no solicitaste este cambio, ignora este correo; tu contraseña sigue siendo la misma. Nunca compartas este enlace con nadie. :app jamás te pedirá tu contraseña por correo, teléfono o mensajería.', ['app' => $appName]) }}

© {{ date('Y') }} {{ $appName }} · {{ __('Este es un correo automático, por favor no respondas.') }}
