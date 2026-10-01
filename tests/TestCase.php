<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;

abstract class TestCase extends BaseTestCase
{
    /**
     * Crea un archivo PNG real (válido) en un directorio temporal y lo devuelve.
     * No requiere la extensión GD.
     */
    protected function pngTemporal(int $ancho = 20, int $alto = 20, array $rgb = [15, 118, 110]): string
    {
        $fila = str_repeat(chr($rgb[0]).chr($rgb[1]).chr($rgb[2]), $ancho);
        $raw = '';
        for ($y = 0; $y < $alto; $y++) {
            $raw .= "\x00".$fila;
        }

        $chunk = fn (string $tipo, string $datos) => pack('N', strlen($datos)).$tipo.$datos.pack('N', crc32($tipo.$datos));

        $png = "\x89PNG\r\n\x1a\n";
        $png .= $chunk('IHDR', pack('NNCCCCC', $ancho, $alto, 8, 2, 0, 0, 0));
        $png .= $chunk('IDAT', gzcompress($raw, 9));
        $png .= $chunk('IEND', '');

        $ruta = sys_get_temp_dir().'/test_'.uniqid().'.png';
        file_put_contents($ruta, $png);

        return $ruta;
    }

    /**
     * Devuelve un UploadedFile PNG real listo para usarse en una petición.
     */
    protected function pngSubible(string $nombre = 'imagen.png'): UploadedFile
    {
        return new UploadedFile($this->pngTemporal(), $nombre, 'image/png', null, true);
    }
}
