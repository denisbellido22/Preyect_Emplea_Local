<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Guarda imágenes subidas realmente dentro de /public/uploads (RF18).
 */
class ImageUploader
{
    /**
     * Mueve el archivo subido a public/uploads/{carpeta} y devuelve la ruta relativa.
     */
    public static function guardar(UploadedFile $file, string $carpeta): string
    {
        $destino = public_path('uploads/'.$carpeta);

        if (! is_dir($destino)) {
            mkdir($destino, 0755, true);
        }

        $nombre = uniqid('', true).'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $file->move($destino, $nombre);

        return 'uploads/'.$carpeta.'/'.$nombre;
    }

    /**
     * Elimina una imagen previamente guardada (si existe dentro de uploads).
     */
    public static function eliminar(?string $ruta): void
    {
        if (blank($ruta)) {
            return;
        }

        $archivo = public_path($ruta);

        if (is_file($archivo) && str_contains($ruta, 'uploads/')) {
            @unlink($archivo);
        }
    }
}
