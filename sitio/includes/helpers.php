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

/**
 * ¿El servidor reescribe direcciones (mod_rewrite + .htaccess)? El .htaccess avisa con FZ_URLS.
 * Si no, el sitio sigue usando las direcciones viejas (index.php?seccion=...) y todo funciona igual.
 */
function fz_urls_lindas(): bool
{
    static $si = null;
    if ($si === null) {
        $si = !empty($_SERVER['FZ_URLS']) || !empty($_SERVER['REDIRECT_FZ_URLS']) || getenv('FZ_URLS') === '1';
    }
    return $si;
}

/** Carpeta base del sitio, para <base href>: "/" en el hosting o "/2 - CLIENTES/Franza-main/sitio/" en la compu. */
function fz_base(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = preg_replace('#/admin$#', '', $dir);
    return ($dir === '/' || $dir === '.' || $dir === '') ? '/' : rtrim($dir, '/') . '/';
}

/** Slugs de todas las obras (una sola consulta por página). */
function fz_slugs(): array
{
    static $mapa = null;
    if ($mapa === null) {
        $mapa = [];
        if (\App\BaseDeDatos\Migracion::tieneSlug()) {
            foreach (\App\BaseDeDatos\DBConexion::getConexion()->query("SELECT obra_id, slug FROM obras")->fetchAll(PDO::FETCH_ASSOC) as $f) {
                if ($f['slug'] !== null && $f['slug'] !== '') $mapa[(int) $f['obra_id']] = $f['slug'];
            }
        }
    }
    return $mapa;
}

/** Link a la ficha de una obra: "obra/restauracion-de-fachada" (o el viejo index.php?seccion=obra-ver&id=72). */
function fz_url_obra(int|Obras $obra): string
{
    $id = $obra instanceof Obras ? $obra->getObraId() : $obra;
    $slug = $obra instanceof Obras ? $obra->getSlug() : (fz_slugs()[$id] ?? '');
    if (fz_urls_lindas() && $slug !== '') {
        return 'obra/' . $slug;
    }
    return 'index.php?seccion=obra-ver&id=' . $id;
}

/** Dirección de la página actual (para anclas como #contacto, que con <base> apuntarían al inicio). */
function fz_aqui(): string
{
    return strtok($_SERVER['REQUEST_URI'] ?? '', '#') ?: fz_base();
}

/** Links de las secciones del sitio. */
function fz_url(string $seccion): string
{
    $lindas = ['inicio' => './', 'obrasciviles' => 'obras/civiles', 'obrasindustriales' => 'obras/industriales'];
    if (fz_urls_lindas() && isset($lindas[$seccion])) {
        return $lindas[$seccion];
    }
    return 'index.php?seccion=' . $seccion;
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

/** Fotos y videos de la galería pública (portada primero, sin capturas de pantalla viejas). */
function fz_medios(Obras $obra): array
{
    $medios = array_values(array_filter($obra->getMediosCarpeta(), fn($a) => !preg_match('/^screenshot/i', $a)));
    $portada = $obra->getImagen();
    if ($portada && in_array($portada, $medios, true)) {
        $medios = array_merge([$portada], array_values(array_filter($medios, fn($a) => $a !== $portada)));
    }
    return $medios;
}

/** Imagen de vista previa de un video (se genera al subirlo); '' si no tiene. */
function fz_poster(string $rutaVideo): string
{
    $poster = 'img/opt/' . preg_replace('/\.[a-z0-9]+$/i', '', substr($rutaVideo, 4)) . '-poster.jpg';
    return is_file(__DIR__ . '/../' . $poster) ? $poster : '';
}

/**
 * Tamaño del título grande de la obra según su largo (título + "en" + ubicación),
 * para que siempre entre completo en la portada. Devuelve la clase CSS.
 */
function fz_titulo_tamano(string $titulo, string $ubicacion = ''): string
{
    $largo = mb_strlen(trim($titulo . ' en ' . $ubicacion));
    if ($largo <= 40) return 'fz-titulo--xl';
    if ($largo <= 60) return 'fz-titulo--l';
    if ($largo <= 85) return 'fz-titulo--m';
    if ($largo <= 115) return 'fz-titulo--s';
    return 'fz-titulo--xs';
}

/**
 * Estado vacío reutilizable: plano con el isotipo, un título, un texto y acciones.
 * $acciones: [['texto' => ..., 'href' => ..., 'estilo' => 'oro'|'linea', 'externo' => bool], ...]
 */
function fz_vacio(string $eyebrow, string $titulo, string $texto, array $acciones = [], string $clase = ''): string
{
    $html = '<div class="fz-vacio-bloque ' . e($clase) . '">'
        . '<div class="fz-vacio-bloque__plano" aria-hidden="true"><img src="img/franza/isotipo-oscuro.svg" alt="" width="120" height="164"><span class="fz-vacio-bloque__cota"></span></div>'
        . '<div class="fz-vacio-bloque__texto">'
        . '<span class="fz-mono fz-oro">' . e($eyebrow) . '</span>'
        . '<h2 class="fz-vacio-bloque__titulo">' . e($titulo) . '</h2>'
        . '<p>' . e($texto) . '</p>';
    if ($acciones) {
        $html .= '<div class="fz-vacio-bloque__acciones">';
        foreach ($acciones as $a) {
            $ext = !empty($a['externo']) ? ' target="_blank" rel="noopener"' : '';
            $html .= '<a class="fz-btn fz-btn--' . e($a['estilo'] ?? 'oro') . '" href="' . e($a['href']) . '"' . $ext . '>' . e($a['texto']) . '</a>';
        }
        $html .= '</div>';
    }
    return $html . '</div></div>';
}

/** Hasta este largo el texto de la obra se muestra grande; más largo pasa a formato de lectura. */
const FZ_TEXTO_CORTO = 280;

/**
 * Prepara el texto "Sobre la obra" para leerse cómodo:
 * - corto: se muestra entero en letra grande (como siempre);
 * - largo: la primera oración queda destacada y el resto va en párrafos de lectura.
 */
function fz_texto_obra(string $texto): array
{
    $texto = trim(preg_replace("/\r\n?/", "\n", $texto));
    if (mb_strlen($texto) <= FZ_TEXTO_CORTO) {
        return ['largo' => false, 'destacado' => $texto, 'parrafos' => []];
    }
    $destacado = '';
    // Primera oración (si es razonablemente corta) como frase destacada.
    if (preg_match('/^(.{30,220}?[.!?])(\s+|$)/su', $texto, $m)) {
        $destacado = trim($m[1]);
        $texto = trim(mb_substr($texto, mb_strlen($m[0])));
    }
    $parrafos = array_values(array_filter(array_map('trim', preg_split("/\n{1,}/", $texto))));
    return ['largo' => true, 'destacado' => $destacado, 'parrafos' => $parrafos, 'caracteres' => mb_strlen($texto)];
}

/** Imagen que se muestra cuando una obra todavía no tiene ninguna foto. */
const FZ_SIN_PORTADA = 'img/franza/sin-portada.svg';

/**
 * Ruta completa de la portada de una obra (array de BD u objeto).
 * Si la portada no está cargada o el archivo no existe, usa la primera foto de la galería;
 * si no hay ninguna foto, devuelve la imagen de FRANZA "sin portada".
 */
function fz_portada(Obras|array $obra): string
{
    if (is_array($obra)) {
        $tipoCarpeta = ($obra['tipo'] === 'civil') ? 'obrasciviles' : 'obrasindustriales';
        $ruta = 'img/' . $tipoCarpeta . '/' . $obra['carpeta'] . '/' . $obra['imagen'];
        return ($obra['imagen'] && is_file(__DIR__ . '/../' . $ruta)) ? $ruta : FZ_SIN_PORTADA;
    }
    $base = $obra->getRutaImagenes();
    if ($obra->getImagen() && is_file(__DIR__ . '/../' . $base . $obra->getImagen())) {
        return $base . $obra->getImagen();
    }
    $fotos = fz_galeria($obra);
    return $fotos ? $base . $fotos[0] : FZ_SIN_PORTADA;
}

/** True si la obra no tiene ninguna foto propia para mostrar. */
function fz_sin_portada(Obras|array $obra): bool
{
    return fz_portada($obra) === FZ_SIN_PORTADA;
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
