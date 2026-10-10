<?php
namespace App\Imagenes;

class ImagenOptimizador {

    /**
     * Optimiza y guarda una imagen subida.
     * Redimensiona si supera $maxAncho, convierte a WebP (quality $calidad).
     * Si GD/WebP no está disponible, devuelve false → usar move_uploaded_file como fallback.
     * Devuelve el nombre del archivo guardado (con extensión) o false en error.
     */
    public static function guardarOptimizado(
        string $tmpPath,
        string $dirDestino,
        string $nombreBase,
        int $maxAncho = 1920,
        int $calidad = 82
    ): string|false {

        if (!function_exists('imagecreatefromjpeg')) {
            return false;
        }

        $info = @getimagesize($tmpPath);
        if (!$info) {
            return false;
        }

        [$ancho, $alto, $tipo] = $info;

        $src = match($tipo) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($tmpPath),
            IMAGETYPE_PNG  => @imagecreatefrompng($tmpPath),
            IMAGETYPE_GIF  => @imagecreatefromgif($tmpPath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($tmpPath),
            default        => false,
        };

        if (!$src) {
            return false;
        }

        if ($ancho > $maxAncho) {
            $nuevoAlto = (int)round($alto * $maxAncho / $ancho);
            $redim = imagecreatetruecolor($maxAncho, $nuevoAlto);

            if (in_array($tipo, [IMAGETYPE_PNG, IMAGETYPE_WEBP])) {
                imagealphablend($redim, false);
                imagesavealpha($redim, true);
            }

            imagecopyresampled($redim, $src, 0, 0, 0, 0, $maxAncho, $nuevoAlto, $ancho, $alto);
            imagedestroy($src);
            $src = $redim;
        }

        $dir = rtrim($dirDestino, '/\\') . DIRECTORY_SEPARATOR;

        if (function_exists('imagewebp')) {
            $nombre = $nombreBase . '.webp';
            $ok = imagewebp($src, $dir . $nombre, $calidad);
        } else {
            // Fallback: aplanar alpha a blanco y guardar como JPEG
            $w = imagesx($src);
            $h = imagesy($src);
            $fondo = imagecreatetruecolor($w, $h);
            imagefill($fondo, 0, 0, imagecolorallocate($fondo, 255, 255, 255));
            imagecopy($fondo, $src, 0, 0, 0, 0, $w, $h);
            imagedestroy($src);
            $src = $fondo;
            $nombre = $nombreBase . '.jpg';
            $ok = imagejpeg($src, $dir . $nombre, $calidad);
        }

        imagedestroy($src);
        return $ok ? $nombre : false;
    }

    /**
     * Crea las versiones livianas (800 y 1600 px, WebP) que usa el sitio público,
     * en img/opt/<misma ruta>-<ancho>.webp. $rutaRelativa es relativa al sitio,
     * por ejemplo "img/obrasciviles/mi-obra/foto.webp".
     */
    public static function generarVersiones(string $rutaRelativa): void
    {
        if (!function_exists('imagewebp') || !str_starts_with($rutaRelativa, 'img/')) {
            return;
        }
        $sitio = realpath(__DIR__ . '/../../');
        $origen = $sitio . '/' . $rutaRelativa;
        $info = @getimagesize($origen);
        if (!$info) {
            return;
        }
        [$ancho, $alto, $tipo] = $info;
        $src = match($tipo) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($origen),
            IMAGETYPE_PNG  => @imagecreatefrompng($origen),
            IMAGETYPE_GIF  => @imagecreatefromgif($origen),
            IMAGETYPE_WEBP => @imagecreatefromwebp($origen),
            default        => false,
        };
        if (!$src) {
            return;
        }
        $sinExt = preg_replace('/\.[a-z0-9]+$/i', '', substr($rutaRelativa, 4));
        foreach ([800, 1600] as $max) {
            $escala = min(1, $max / max($ancho, $alto));
            $w = max(1, (int) round($ancho * $escala));
            $h = max(1, (int) round($alto * $escala));
            $dst = imagecreatetruecolor($w, $h);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $ancho, $alto);
            $destino = $sitio . '/img/opt/' . $sinExt . '-' . $max . '.webp';
            if (!is_dir(dirname($destino))) {
                mkdir(dirname($destino), 0775, true);
            }
            imagewebp($dst, $destino, 78);
            imagedestroy($dst);
        }
        imagedestroy($src);
    }

    /** Borra las versiones livianas de una imagen. */
    public static function borrarVersiones(string $rutaRelativa): void
    {
        if (!str_starts_with($rutaRelativa, 'img/')) {
            return;
        }
        $sitio = realpath(__DIR__ . '/../../');
        $sinExt = preg_replace('/\.[a-z0-9]+$/i', '', substr($rutaRelativa, 4));
        foreach ([800, 1600] as $max) {
            $f = $sitio . '/img/opt/' . $sinExt . '-' . $max . '.webp';
            if (is_file($f)) {
                unlink($f);
            }
        }
    }
}
