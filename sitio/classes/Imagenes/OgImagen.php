<?php
/**
 * OgImagen.php — Imagen para compartir cada obra (WhatsApp, Facebook, LinkedIn…).
 * Arma un JPG de 1200×630 con la portada de la obra, el logo, el título y la ubicación.
 * Se genera una sola vez y queda guardada en img/opt/og/; si cambia el título,
 * la ubicación o la portada, se genera otra.
 */
namespace App\Imagenes;

use App\Modelos\Obras;

class OgImagen
{
    private const ANCHO = 1200;
    private const ALTO = 630;

    /** Devuelve la ruta relativa del JPG (img/opt/og/...) o null si el servidor no puede generarla. */
    public static function para(Obras $obra, string $portadaRelativa = ''): ?string
    {
        if (!function_exists('imagettftext') || !function_exists('imagejpeg')) {
            return null;
        }
        $sitio = realpath(__DIR__ . '/../../');
        $clave = substr(md5($obra->getTitulo() . '|' . $obra->getUbicacion() . '|' . $obra->getTipo() . '|' . $portadaRelativa . '|v3'), 0, 10);
        $rel = 'img/opt/og/' . $obra->getObraId() . '-' . $clave . '.jpg';
        if (is_file($sitio . '/' . $rel)) {
            return $rel;
        }
        try {
            $ok = self::generar($obra, $portadaRelativa, $sitio, $sitio . '/' . $rel);
        } catch (\Throwable $e) {
            error_log('OG imagen: ' . $e->getMessage());
            $ok = false;
        }
        if (!$ok) {
            return null;
        }
        // Borra versiones viejas de esta misma obra.
        foreach (glob($sitio . '/img/opt/og/' . $obra->getObraId() . '-*.jpg') ?: [] as $viejo) {
            if (basename($viejo) !== basename($rel)) @unlink($viejo);
        }
        return $rel;
    }

    private static function generar(Obras $obra, string $portada, string $sitio, string $destino): bool
    {
        $W = self::ANCHO; $H = self::ALTO;
        $im = imagecreatetruecolor($W, $H);
        $oscuro = imagecolorallocate($im, 20, 19, 17);
        imagefill($im, 0, 0, $oscuro);

        // 1. Foto de fondo (versión liviana si existe), recortada para llenar 1200×630.
        $foto = null;
        if ($portada !== '' && !str_ends_with($portada, '.svg')) {
            $liviana = 'img/opt/' . preg_replace('/\.[a-z0-9]+$/i', '', substr($portada, 4)) . '-1600.webp';
            foreach ([$liviana, $portada] as $cand) {
                if (is_file($sitio . '/' . $cand) && filesize($sitio . '/' . $cand) < 25 * 1048576) {
                    $foto = @imagecreatefromstring((string) file_get_contents($sitio . '/' . $cand));
                    if ($foto) break;
                }
            }
        }
        if ($foto) {
            $fw = imagesx($foto); $fh = imagesy($foto);
            $esc = max($W / $fw, $H / $fh);
            $cw = (int) round($W / $esc); $ch = (int) round($H / $esc);
            imagecopyresampled($im, $foto, 0, 0, (int) (($fw - $cw) / 2), (int) (($fh - $ch) * 0.4), $W, $H, $cw, $ch);
            imagedestroy($foto);
            // Velo: oscurece de izquierda a derecha y más abajo, para que el texto se lea siempre.
            imagealphablending($im, true);
            for ($x = 0; $x < $W; $x += 4) {
                $a = (int) round(127 - 127 * (0.86 - 0.5 * ($x / $W)));
                imagefilledrectangle($im, $x, 0, $x + 3, $H, imagecolorallocatealpha($im, 20, 19, 17, max(0, min(127, $a))));
            }
            for ($y = (int) ($H * 0.45); $y < $H; $y += 3) {
                $t = ($y - $H * 0.45) / ($H * 0.55);
                imagefilledrectangle($im, 0, $y, $W, $y + 2, imagecolorallocatealpha($im, 20, 19, 17, (int) round(127 - 70 * $t)));
            }
        } else {
            // Sin foto: plano con grilla dorada.
            $linea = imagecolorallocatealpha($im, 239, 213, 129, 112);
            for ($x = 0; $x < $W; $x += 40) imageline($im, $x, 0, $x, $H, $linea);
            for ($y = 0; $y < $H; $y += 40) imageline($im, 0, $y, $W, $y, $linea);
        }

        $cal = imagecolorallocate($im, 241, 238, 231);
        $oro = imagecolorallocate($im, 239, 213, 129);
        $oroOsc = imagecolorallocate($im, 201, 160, 79);
        $gris = imagecolorallocate($im, 190, 184, 172);
        $fDisplay = __DIR__ . '/../../includes/fuentes/InterDisplay-ExtraBold.otf';
        $fTexto = __DIR__ . '/../../includes/fuentes/Inter-SemiBold.otf';
        $x0 = 72;

        // 2. Logo arriba a la izquierda.
        $logoArchivo = $sitio . '/img/franza/logo-claro.png';
        if (is_file($logoArchivo) && ($logo = @imagecreatefrompng($logoArchivo))) {
            $lw = 230; $lh = (int) round(imagesy($logo) * $lw / imagesx($logo));
            imagealphablending($im, true);
            imagecopyresampled($im, $logo, $x0, 56, 0, 0, $lw, $lh, imagesx($logo), imagesy($logo));
            imagedestroy($logo);
        }

        // 3. Rótulo: número · tipo · rubro.
        $rotulo = 'N° ' . str_pad((string) $obra->getObraId(), 3, '0', STR_PAD_LEFT) . '   ·   OBRA ' . ($obra->getTipo() === 'industrial' ? 'INDUSTRIAL' : 'CIVIL');
        if ($obra->getRubro() !== '' && $obra->getRubro() !== 'Otras obras') {
            $rotulo .= '   ·   ' . mb_strtoupper($obra->getRubro(), 'UTF-8');
        }

        // 4. Título: el tamaño se achica hasta que entre en 3 líneas.
        $titulo = trim($obra->getTitulo());
        $anchoMax = $W - $x0 * 2;
        $tam = 78; $lineas = [];
        while ($tam >= 40) {
            $lineas = self::partir($titulo, $fDisplay, $tam, $anchoMax);
            if (count($lineas) <= 3) break;
            $tam -= 4;
        }
        $lineas = array_slice($lineas, 0, 3);
        $alto = (int) round($tam * 1.1);
        $lugar = 'en ' . trim($obra->getUbicacion());
        $tamLugar = 30;
        while ($tamLugar > 20 && self::ancho($lugar, $fTexto, $tamLugar) > $anchoMax) $tamLugar -= 2;

        // Se acomoda de abajo hacia arriba.
        $yLugar = $H - 78;
        $yUltima = $yLugar - $tamLugar - 34;
        $yPrimera = $yUltima - ($alto * (count($lineas) - 1));
        imagettftext($im, 17, 0, $x0, $yPrimera - $tam - 30, $oroOsc, $fTexto, $rotulo);
        foreach ($lineas as $i => $l) {
            imagettftext($im, $tam, 0, $x0, $yPrimera + $alto * $i, $cal, $fDisplay, $l);
        }
        imagettftext($im, $tamLugar, 0, $x0, $yLugar, $oro, $fTexto, $lugar);

        // 5. Pie: línea dorada y dominio.
        imagefilledrectangle($im, $x0, $H - 44, $x0 + 56, $H - 41, $oroOsc);
        $dominio = 'franza.com.ar';
        imagettftext($im, 16, 0, $W - $x0 - self::ancho($dominio, $fTexto, 16), $H - 36, $gris, $fTexto, $dominio);

        if (!is_dir(dirname($destino))) mkdir(dirname($destino), 0775, true);
        imageinterlace($im, true);
        $ok = imagejpeg($im, $destino, 84);
        imagedestroy($im);
        return $ok;
    }

    private static function ancho(string $texto, string $fuente, float $tam): int
    {
        $b = imagettfbbox($tam, 0, $fuente, $texto);
        return abs($b[2] - $b[0]);
    }

    /** Parte el texto en líneas que entren en $max píxeles. */
    private static function partir(string $texto, string $fuente, float $tam, int $max): array
    {
        $lineas = []; $actual = '';
        foreach (preg_split('/\s+/u', $texto) as $palabra) {
            $prueba = $actual === '' ? $palabra : $actual . ' ' . $palabra;
            if ($actual !== '' && self::ancho($prueba, $fuente, $tam) > $max) {
                $lineas[] = $actual;
                $actual = $palabra;
            } else {
                $actual = $prueba;
            }
        }
        if ($actual !== '') $lineas[] = $actual;
        return $lineas;
    }
}
