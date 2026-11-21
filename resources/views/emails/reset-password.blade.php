<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Restablece tu contraseña') }} · {{ $appName }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial,Helvetica,sans-serif; color:#1f2937;">

{{-- Texto de vista previa que muestran algunos clientes de correo --}}
<div style="display:none; max-height:0; overflow:hidden; opacity:0;">
    {{ __('Recibimos una solicitud para restablecer la contraseña de tu cuenta en :app.', ['app' => $appName]) }}
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f6f8; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(16,24,40,.08);">

                <tr>
                    <td style="background-color:#0f766e; padding:28px 32px;">
                        <div style="font-size:22px; font-weight:bold; letter-spacing:2px; color:#ffffff;">
                            {{ mb_strtoupper($appName) }}
                        </div>
                        <div style="font-size:13px; color:#d1fae5; margin-top:4px;">
                            {{ __('Conectando oportunidades laborales') }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:36px 32px 8px 32px;">
                        <h1 style="margin:0 0 18px 0; font-size:22px; line-height:1.3; color:#111827;">
                            {{ __('Restablece tu contraseña') }}
                        </h1>

                        <p style="margin:0 0 14px 0; font-size:15px; line-height:1.6;">
                            {!! __('Hola, :nombre:', ['nombre' => '<strong>'.e($nombre).'</strong>']) !!}
                        </p>

                        <p style="margin:0 0 14px 0; font-size:15px; line-height:1.6;">
                            {{ __('Recibimos una solicitud para restablecer la contraseña de tu cuenta en :app. Si fuiste tú, pulsa el botón para crear una contraseña nueva.', ['app' => $appName]) }}
                        </p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:10px 32px 18px 32px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center" style="background-color:#0f766e; border-radius:8px;">
                                    <a href="{{ $actionUrl }}"
                                       style="display:inline-block; padding:14px 32px; font-size:16px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:8px;">
                                        {{ __('Restablecer mi contraseña') }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 32px 22px 32px;">
                        <p style="margin:0 0 8px 0; font-size:13px; line-height:1.5; color:#6b7280;">
                            {{ __('Si el botón no funciona, copia y pega este enlace en tu navegador:') }}
                        </p>
                        <p style="margin:0; font-size:12px; line-height:1.5; word-break:break-all;">
                            <a href="{{ $actionUrl }}" style="color:#0f766e;">{{ $actionUrl }}</a>
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 32px;">
                        <div style="background-color:#f0fdfa; border-left:4px solid #0f766e; padding:14px 16px; border-radius:4px;">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#115e59;">
                                <strong>{{ __('El enlace caduca en :minutos minutos', ['minutos' => $expiraMinutos]) }}</strong>
                                {{ __('y solo puede utilizarse una vez.') }}
                            </p>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:22px 32px 0 32px;">
                        <div style="background-color:#fffbeb; border:1px solid #fde68a; padding:14px 16px; border-radius:6px;">
                            <p style="margin:0 0 6px 0; font-size:13px; font-weight:bold; color:#92400e;">
                                {{ __('Aviso de seguridad') }}
                            </p>
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#78350f;">
                                {{ __('Si no solicitaste este cambio, ignora este correo: tu contraseña sigue siendo la misma. Nunca compartas este enlace con nadie; :app jamás te pedirá tu contraseña por correo, teléfono o mensajería.', ['app' => $appName]) }}
                            </p>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 32px 32px 32px;">
                        <p style="margin:0; font-size:13px; line-height:1.6; color:#6b7280;">
                            {{ __('Gracias por usar :app.', ['app' => $appName]) }}
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background-color:#f9fafb; border-top:1px solid #e5e7eb; padding:18px 32px;">
                        <p style="margin:0; font-size:12px; line-height:1.6; color:#9ca3af; text-align:center;">
                            &copy; {{ date('Y') }} {{ $appName }} · {{ __('Todos los derechos reservados.') }}<br>
                            {{ __('Este es un correo automático, por favor no respondas a este mensaje.') }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
