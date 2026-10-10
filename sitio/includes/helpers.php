<?php
/**
 * helpers.php — Funciones de presentación del sitio público (rediseño 2026).
 * Escapado, imágenes optimizadas, datos extra de cada obra y links.
 */

use App\Modelos\Obras;

/** Contenido curado del sitio (textos, pares antes/después, etapas, datos extra de obras). */
function fz_data(): array
{
    static $data = null;
    if ($data === null) {
        $data = require __DIR__ . '/../data/franza.php';
    }
    return $data;
}

/** Escapa texto para HTML. */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Devuelve la versión optimizada (.webp) de una imagen si existe en img/opt/,
 * si no, la original. $ruta es relativa al sitio, ej: "img/obrasciviles/x/foto.jpg".
 */
function fz_img(string $ruta, int $ancho = 1600): string
{
    if (str_starts_with($ruta, 'img/') && !str_starts_with($ruta, 'img/opt/')) {
        $sinExt = preg_replace('/\.[a-z0-9]+$/i', '', substr($ruta, 4));
        $opt = 'img/opt/' . $sinExt . '-' . $ancho . '.webp';
        if (is_file(__DIR__ . '/../' . $opt)) {
            return $opt;
        }
    }
    return $ruta;
}

/** Número de obra para mostrar: "N° 072". */
function fz_num(int $id): string
{
    return 'N° ' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
}

/** Datos extra de una obra (rubro, etiquetas, pasos...). Primero la base, si no el archivo de contenidos. */
function fz_extra(Obras|array $obra): array
{
    $base = ['rubro' => 'Otras obras', 'etiquetas' => '', 'badge' => '', 'render' => false, 'problema' => '', 'pasos' => [], 'duracion' => '', 'anio' => ''];
    if ($obra instanceof Obras && \App\BaseDeDatos\Migracion::actualizada()) {
        $extra = [
            'rubro'     => $obra->getRubro() ?: 'Otras obras',
            'etiquetas' => $obra->getEtiquetas(),
            'badge'     => $obra->getBadge(),
            'problema'  => $obra->getProblema(),
            'pasos'     => $obra->getPasos(),
            'duracion'  => $obra->getDuracion(),
            'anio'      => $obra->getAnio(),
        ];
        $extra['render'] = stripos($extra['badge'], 'render') !== false;
        return $extra + $base;
    }
    $carpeta = is_array($obra) ? ($obra['carpeta'] ?? '') : $obra->getCarpeta();
    $extra = fz_data()['obras'][$carpeta] ?? [];
    return $extra + $base;
}

/** Número de WhatsApp para mostrar: +54 9 11 3693-2502 */
function fz_whatsapp_visible(): string
{
    $n = fz_data()['contacto']['whatsapp'];
    if (preg_match('/^549(11)(\d{4})(\d{4})$/', $n, $m)) {
        return '+54 9 ' . $m[1] . ' ' . $m[2] . '-' . $m[3];
    }
    return '+' . $n;
}

/** URL absoluta del sitio (para SEO). */
function fz_url_absoluta(string $ruta = ''): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'www.franza.com.ar';
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = preg_replace('#/admin$#', '', $dir);
    $dir = ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/');
    return ($https ? 'https' : 'http') . '://' . $host . $dir . '/' . ltrim($ruta, '/');
}

/** Link a la ficha de una obra. */
function fz_url_obra(int $id): string
{
    return 'index.php?seccion=obra-ver&id=' . $id;
}

/** Link de WhatsApp con mensaje. */
function fz_whatsapp(string $mensaje = '¡Hola! Me gustaría hacer una consulta.'): string
{
    return 'https://wa.me/' . fz_data()['contacto']['whatsapp'] . '?text=' . rawurlencode($mensaje);
}

/** Imágenes de la galería de una obra (portada primero, sin capturas de pantalla sueltas). */
function fz_galeria(Obras $obra): array
{
    $imagenes = array_values(array_filter($obra->getImagenesCarpeta(), fn($img) => !preg_match('/^screenshot/i', $img)));
    $portada = $obra->getImagen();
    if ($portada && in_array($portada, $imagenes, true)) {
        $imagenes = array_merge([$portada], array_values(array_filter($imagenes, fn($img) => $img !== $portada)));
    }
    return $imagenes;
}

/** Ruta completa de la portada de una obra (array de BD u objeto). */
function fz_portada(Obras|array $obra): string
{
    if (is_array($obra)) {
        $tipoCarpeta = ($obra['tipo'] === 'civil') ? 'obrasciviles' : 'obrasindustriales';
        return 'img/' . $tipoCarpeta . '/' . $obra['carpeta'] . '/' . $obra['imagen'];
    }
    return $obra->getRutaImagenes() . $obra->getImagen();
}

/** Ícono de flecha reutilizable. */
function fz_flecha(int $tam = 20): string
{
    return '<svg width="' . $tam . '" height="' . $tam . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
}

/** Envuelve cada palabra para animar la entrada del título (palabra por palabra). */
function fz_palabras(string $texto, int $desde = 0): string
{
    $html = [];
    foreach (preg_split('/\s+/', trim($texto)) as $i => $p) {
        $html[] = '<span class="fz-p" aria-hidden="true"><span style="--i:' . ($desde + $i) . '">' . e($p) . '</span></span>';
    }
    return implode(' ', $html);
}
