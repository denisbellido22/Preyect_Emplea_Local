<?php

namespace Database\Seeders\Concerns;

trait CreatesPlaceholderImages
{
    /**
     * Genera un PNG sólido válido (sin GD) y devuelve su ruta relativa en /public.
     */
    protected function crearPng(string $carpeta, string $nombre, int $ancho, int $alto, array $rgb): string
    {
        $dir = public_path('uploads/'.$carpeta);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fila = str_repeat(chr($rgb[0]).chr($rgb[1]).chr($rgb[2]), $ancho);
        $raw = '';
        for ($y = 0; $y < $alto; $y++) {
            $raw .= "\x00".$fila;
        }

        $png = "\x89PNG\r\n\x1a\n";
        $png .= $this->chunk('IHDR', pack('NNCCCCC', $ancho, $alto, 8, 2, 0, 0, 0));
        $png .= $this->chunk('IDAT', gzcompress($raw, 9));
        $png .= $this->chunk('IEND', '');

        $ruta = 'uploads/'.$carpeta.'/'.$nombre;
        file_put_contents(public_path($ruta), $png);

        return $ruta;
    }

    private function chunk(string $tipo, string $datos): string
    {
        return pack('N', strlen($datos)).$tipo.$datos.pack('N', crc32($tipo.$datos));
    }
}
