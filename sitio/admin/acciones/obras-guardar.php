<?php
/**
 * obras-guardar.php — Acción: crear o editar una obra desde el editor visual.
 * Guarda textos, rubro y extras; sube fotos nuevas (optimizadas + versiones web),
 * elimina las fotos marcadas, guarda el orden de la galería y la portada.
 * Sin ?id crea una obra nueva; con ?id edita la existente.
 */

use App\Autenticacion\Autenticacion;
use App\Modelos\Obras;
use App\Seguridad\Csrf;
use App\Imagenes\ImagenOptimizador;

require_once __DIR__ . '/../../bootstrap/init.php';

$obraId = (int) ($_GET['id'] ?? 0);
$volver = '../index.php?seccion=' . ($obraId ? 'obras-editar&id=' . $obraId : 'obras-agregar');
$sobrantes = 0;

// Si el envío superó el límite del servidor, PHP descarta todo el formulario.
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $_SESSION['mensajeFeedback'] = 'El envío superó el límite del servidor. Tus fotos ya subidas quedaron guardadas como borrador: tocá "Recuperar" en el editor.';
    $_SESSION['estadoFeedback'] = 'error';
    header('Location: ' . $volver);
    exit;
}

Csrf::verificarOAbortar($volver);

$autenticacion = new Autenticacion;
if (!$autenticacion->estaAutenticado() || !$autenticacion->getUsuario()->esAdmin()) {
    $_SESSION['mensajeFeedback'] = 'Ingresá con un usuario administrador.';
    $_SESSION['estadoFeedback'] = 'error';
    header('Location: ../index.php?seccion=inicio');
    exit;
}

$modelo = new Obras();
$obra = $obraId ? $modelo->porId($obraId) : null;
if ($obraId && !$obra) {
    $_SESSION['mensajeFeedback'] = 'Esa obra no existe o se eliminó.';
    $_SESSION['estadoFeedback'] = 'error';
    header('Location: ../index.php?seccion=obras');
    exit;
}

$texto = fn(string $k, int $max = 255) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

$titulo    = preg_replace('/\s+/', ' ', $texto('titulo', 100));
$ubicacion = $texto('ubicacion', 100);
$tipo      = ($_POST['tipo'] ?? '') === 'industrial' ? 'industrial' : 'civil';
$formato   = ($_POST['formato'] ?? 'texto') === 'pasos' ? 'pasos' : 'texto';
$problema  = $texto('problema', 5000);
$pasos     = array_values(array_filter(array_map(fn($p) => mb_substr(trim(preg_replace('/\s+/', ' ', (string) $p)), 0, 400), (array) ($_POST['pasos'] ?? [])), fn($p) => $p !== ''));
$explicacion = $texto('explicacion', 10000);

if ($formato === 'pasos') {
    if (!$pasos) {
        $errores[] = 'Agregá al menos un paso o cambiá a "Texto corrido".';
    }
    // El texto general se arma solo (se usa para buscadores y vistas previas).
    $explicacion = trim($problema . ' ' . implode(' ', $pasos));
} else {
    $pasos = [];
    $problema = '';
}

$errores = $errores ?? [];
if ($titulo === '' || mb_strlen($titulo) < 3) $errores[] = 'Escribí el título de la obra.';
if ($ubicacion === '') $errores[] = 'Escribí dónde fue la obra.';
if ($explicacion === '') $errores[] = 'Contá qué se hizo en la obra.';

if ($errores) {
    $_SESSION['mensajeFeedback'] = implode('<br>', array_map(fn($e) => htmlspecialchars($e, ENT_QUOTES, 'UTF-8'), $errores));
    $_SESSION['estadoFeedback'] = 'error';
    header('Location: ' . $volver);
    exit;
}

function slugObra(string $texto): string
{
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $slug = strtolower((string) $slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug ?: 'obra-' . time();
}

function borrarCarpeta(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (array_diff(scandir($dir), ['.', '..']) as $f) {
        is_dir("$dir/$f") ? borrarCarpeta("$dir/$f") : unlink("$dir/$f");
    }
    rmdir($dir);
}

$sitio = realpath(__DIR__ . '/../../');

// 1. Carpeta de imágenes. Si la obra existe, la carpeta no cambia aunque cambie el título.
if ($obra) {
    $carpeta = $obra->getCarpeta() ?: slugObra($titulo);
    $tipoAnterior = $obra->getTipo();
    if ($tipoAnterior !== $tipo) {
        foreach (['img/', 'img/opt/'] as $raiz) {
            $de = $sitio . '/' . $raiz . Obras::carpetaTipo($tipoAnterior) . '/' . $carpeta;
            $a  = $sitio . '/' . $raiz . Obras::carpetaTipo($tipo) . '/' . $carpeta;
            if (is_dir($de) && !is_dir($a)) {
                if (!is_dir(dirname($a))) mkdir(dirname($a), 0775, true);
                rename($de, $a);
            }
        }
    }
} else {
    $base = slugObra($titulo);
    $carpeta = $base;
    $n = 2;
    while ($modelo->carpetaEnUso($carpeta) || is_dir($sitio . '/img/' . Obras::carpetaTipo($tipo) . '/' . $carpeta)) {
        $carpeta = $base . '-' . $n++;
    }
}
$rutaRel = 'img/' . Obras::carpetaTipo($tipo) . '/' . $carpeta . '/';
$rutaAbs = $sitio . '/' . $rutaRel;
if (!is_dir($rutaAbs)) {
    mkdir($rutaAbs, 0775, true);
}

// 2. Eliminar fotos marcadas.
foreach ((array) ($_POST['eliminar'] ?? []) as $archivo) {
    $archivo = basename((string) $archivo);
    if ($archivo === '' || !is_file($rutaAbs . $archivo)) continue;
    unlink($rutaAbs . $archivo);
    ImagenOptimizador::borrarVersiones($rutaRel . $archivo);
}

// 3. Subir fotos nuevas (el navegador ya las achica antes de enviarlas).
$nuevas = [];
$permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$fallidas = 0;
if (!empty($_FILES['fotos_nuevas']['name']) && is_array($_FILES['fotos_nuevas']['name'])) {
    $f = $_FILES['fotos_nuevas'];
    foreach ($f['name'] as $i => $nombre) {
        if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        $ext = strtolower(pathinfo((string) $nombre, PATHINFO_EXTENSION));
        if ($f['error'][$i] !== UPLOAD_ERR_OK || !in_array($ext, $permitidas, true) || $f['size'][$i] > 15 * 1024 * 1024 || !@getimagesize($f['tmp_name'][$i])) {
            $fallidas++;
            continue;
        }
        $base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo((string) $nombre, PATHINFO_FILENAME));
        $nombreBase = substr($base, 0, 40) . '_' . time() . '_' . $i;
        $guardado = ImagenOptimizador::guardarOptimizado($f['tmp_name'][$i], $rutaAbs, $nombreBase);
        if ($guardado === false) {
            $guardado = $nombreBase . '.' . $ext;
            if (!move_uploaded_file($f['tmp_name'][$i], $rutaAbs . $guardado)) {
                $fallidas++;
                continue;
            }
        }
        ImagenOptimizador::generarVersiones($rutaRel . $guardado);
        $nuevas[$i] = $guardado;
    }
}

// 3b. Fotos que ya se subieron una por una desde el editor (están en img/tmp/).
$dirTmp = $sitio . '/img/tmp/';
$movidas = [];
$moverTmp = function (string $archivo) use (&$movidas, $dirTmp, $rutaAbs, $rutaRel, $sitio): ?string {
    $archivo = basename($archivo);
    if (isset($movidas[$archivo])) return $movidas[$archivo];
    if ($archivo === '' || !is_file($dirTmp . $archivo)) return null;
    $destino = $archivo;
    while (is_file($rutaAbs . $destino)) {
        $destino = pathinfo($archivo, PATHINFO_FILENAME) . '_' . mt_rand(10, 99) . '.' . pathinfo($archivo, PATHINFO_EXTENSION);
    }
    if (!rename($dirTmp . $archivo, $rutaAbs . $destino)) return null;
    if (Obras::esVideo($destino)) {
        // La vista previa del video va con las versiones livianas: img/opt/.../<video>-poster.jpg
        $poster = $dirTmp . pathinfo($archivo, PATHINFO_FILENAME) . '-poster.jpg';
        if (is_file($poster)) {
            $dest = $sitio . '/img/opt/' . substr($rutaRel, 4) . pathinfo($destino, PATHINFO_FILENAME) . '-poster.jpg';
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0775, true);
            rename($poster, $dest);
        }
    } else {
        ImagenOptimizador::generarVersiones($rutaRel . $destino);
    }
    return $movidas[$archivo] = $destino;
};

// 4. Orden de la galería y portada ("f:archivo" existente, "t:archivo" subida al editor, "n:3" foto nueva nro 3).
$resolver = function (string $token) use ($nuevas, $rutaAbs, $moverTmp): ?string {
    if (str_starts_with($token, 'f:')) {
        $a = basename(substr($token, 2));
        return is_file($rutaAbs . $a) ? $a : null;
    }
    if (str_starts_with($token, 't:')) {
        return $moverTmp(substr($token, 2));
    }
    if (str_starts_with($token, 'n:')) {
        return $nuevas[(int) substr($token, 2)] ?? null;
    }
    return null;
};
$orden = [];
foreach ((array) json_decode((string) ($_POST['orden_fotos'] ?? '[]'), true) as $token) {
    $a = $resolver((string) $token);
    if ($a !== null && !in_array($a, $orden, true)) $orden[] = $a;
}
foreach ($nuevas as $a) {
    if (!in_array($a, $orden, true)) $orden[] = $a;
}
// Topes por obra (fotos y videos): lo que sobra y es nuevo no se guarda.
$sobrantes = 0;
$cuenta = ['foto' => 0, 'video' => 0];
$tope = ['foto' => Obras::MAX_FOTOS, 'video' => Obras::MAX_VIDEOS];
$orden = array_values(array_filter($orden, function ($a) use (&$cuenta, $tope, &$sobrantes, $nuevas, $movidas, $rutaAbs, $rutaRel) {
    $clase = Obras::esVideo($a) ? 'video' : 'foto';
    if (++$cuenta[$clase] <= $tope[$clase]) return true;
    if (in_array($a, $nuevas, true) || in_array($a, $movidas, true)) {
        @unlink($rutaAbs . $a);
        ImagenOptimizador::borrarVersiones($rutaRel . $a);
        $sobrantes++;
        return false;
    }
    return true;
}));

$imagen = $resolver((string) ($_POST['portada'] ?? '')) ?? '';
if ($imagen !== '' && Obras::esVideo($imagen)) $imagen = '';
if ($imagen === '' && $obra && $obra->getImagen() && is_file($rutaAbs . $obra->getImagen())) {
    $imagen = $obra->getImagen();
}
if ($imagen === '') {
    $imagen = array_values(array_filter($orden, fn($a) => !Obras::esVideo($a)))[0] ?? '';
}

$datos = [
    'carpeta'     => $carpeta,
    'titulo'      => $titulo,
    'ubicacion'   => $ubicacion,
    'explicacion' => $explicacion,
    'tipo'        => $tipo,
    'imagen'      => $imagen,
    'rubro'       => $texto('rubro', 80) ?: null,
    'etiquetas'   => $texto('etiquetas', 255) ?: null,
    'badge'       => $texto('badge', 60) ?: null,
    'problema'    => $problema ?: null,
    'pasos'       => $pasos ? implode("\n", $pasos) : null,
    'duracion'    => $texto('duracion', 60) ?: null,
    'anio'        => $texto('anio', 10) ?: null,
    'destacada'   => !empty($_POST['destacada']) ? 1 : 0,
    'fotos_orden' => json_encode(array_values($orden), JSON_UNESCAPED_UNICODE),
    // Dirección linda: la que escribió el admin, o la del título; siempre única.
    'slug'        => $modelo->slugUnico(trim((string) ($_POST['slug'] ?? '')) ?: ($obra && $obra->getSlug() !== '' ? $obra->getSlug() : $titulo), $obra ? $obra->getObraId() : 0),
];

try {
    if ($obra) {
        $modelo->editar($obra->getObraId(), $datos);
        $id = $obra->getObraId();
        $msj = 'Guardado. La obra ya se ve así en el sitio.';
    } else {
        $id = $modelo->crear($datos + ['usuario_fk' => $autenticacion->getUsuarioId()]);
        $msj = 'La obra se publicó en el sitio.';
    }
    if ($sobrantes) {
        $msj .= " $sobrantes archivo(s) no se guardaron porque cada obra admite hasta " . Obras::MAX_FOTOS . ' fotos y ' . Obras::MAX_VIDEOS . ' videos.';
    }
    if ($fallidas) {
        $msj .= " $fallidas foto(s) no se pudieron subir (formato no válido o demasiado pesadas).";
    }
    $_SESSION['mensajeFeedback'] = $msj;
    $_SESSION['estadoFeedback'] = ($fallidas || $sobrantes) ? 'error' : 'exito';
    header('Location: ../index.php?seccion=obras-editar&id=' . $id);
    exit;
} catch (\Throwable $th) {
    error_log('Guardar obra: ' . $th->getMessage());
    if (!$obra && !array_diff(scandir($rutaAbs), ['.', '..'])) {
        borrarCarpeta($rutaAbs);
    }
    $_SESSION['mensajeFeedback'] = 'No se pudo guardar la obra. Probá de nuevo y, si sigue, avisale a quien mantiene el sitio.';
    $_SESSION['estadoFeedback'] = 'error';
    header('Location: ' . $volver);
    exit;
}
