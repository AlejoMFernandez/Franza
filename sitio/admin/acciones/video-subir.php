<?php
/**
 * video-subir.php — Acción (fetch): sube un video en partes chicas (1,5 MB cada una),
 * así nunca choca con el límite de subida del hosting. Cada parte trae:
 *   id      → identificador de la subida (lo genera el panel, 24 caracteres hex)
 *   offset  → en qué byte empieza esta parte (sirve para retomar si se cortó)
 *   parte   → el pedazo del archivo
 *   final=1 → en la última parte, junto con nombre y poster (imagen de vista previa)
 * El video queda en img/tmp/ hasta que se guarda la obra.
 */
use App\Autenticacion\Autenticacion;
use App\Modelos\Obras;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';
header('Content-Type: application/json; charset=utf-8');

function responder(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    responder(['ok' => false, 'error' => 'El servidor rechazó la parte del video por tamaño.'], 413);
}

$auth = new Autenticacion;
if (!Csrf::validar() || !$auth->estaAutenticado() || !$auth->getUsuario()->esAdmin()) {
    responder(['ok' => false, 'error' => 'Se cerró la sesión. Recargá la página e ingresá de nuevo.'], 403);
}

$id = (string) ($_POST['id'] ?? '');
if (!preg_match('/^[a-f0-9]{24}$/', $id)) {
    responder(['ok' => false, 'error' => 'Subida inválida.'], 400);
}

$dirTmp = realpath(__DIR__ . '/../../') . '/img/tmp/';
if (!is_dir($dirTmp)) {
    mkdir($dirTmp, 0775, true);
}
$parcial = $dirTmp . $id . '.part';
$maxBytes = Obras::MAX_VIDEO_MB * 1024 * 1024;

// 1. Agregar la parte en su lugar.
$parte = $_FILES['parte'] ?? null;
if ($parte && ($parte['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $offset = (int) ($_POST['offset'] ?? -1);
    $actual = is_file($parcial) ? filesize($parcial) : 0;
    if ($offset !== $actual) {
        // La parte no encaja (se perdió una o se repitió): el panel retoma desde $actual.
        responder(['ok' => false, 'retomar' => $actual], 409);
    }
    if ($actual + $parte['size'] > $maxBytes) {
        @unlink($parcial);
        responder(['ok' => false, 'error' => 'El video supera los ' . Obras::MAX_VIDEO_MB . ' MB.'], 400);
    }
    $in = fopen($parte['tmp_name'], 'rb');
    $out = fopen($parcial, $actual ? 'ab' : 'wb');
    if (!$in || !$out) {
        responder(['ok' => false, 'error' => 'No se pudo guardar el video en el servidor.'], 500);
    }
    stream_copy_to_stream($in, $out);
    fclose($in);
    fclose($out);
} elseif ($parte) {
    responder(['ok' => false, 'error' => 'Se cortó la subida. Probá de nuevo.'], 400);
}

if (empty($_POST['final'])) {
    responder(['ok' => true, 'recibido' => is_file($parcial) ? filesize($parcial) : 0]);
}

// 2. Última parte: validar que sea un video y dejarlo listo.
if (!is_file($parcial) || filesize($parcial) < 1024) {
    responder(['ok' => false, 'error' => 'El video llegó vacío.'], 400);
}
$ext = strtolower(pathinfo((string) ($_POST['nombre'] ?? ''), PATHINFO_EXTENSION));
$ext = $ext === 'm4v' ? 'mp4' : $ext;
$tipo = function_exists('mime_content_type') ? (string) @mime_content_type($parcial) : '';
$esVideo = in_array($ext, ['mp4', 'webm', 'mov'], true)
    && ($tipo === '' || str_starts_with($tipo, 'video/') || $tipo === 'application/octet-stream');
if (!$esVideo) {
    @unlink($parcial);
    responder(['ok' => false, 'error' => 'Ese archivo no es un video MP4, MOV o WebM.'], 400);
}

$base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo((string) $_POST['nombre'], PATHINFO_FILENAME));
$final = substr($base, 0, 40) . '_' . substr($id, 0, 10) . '.' . $ext;
rename($parcial, $dirTmp . $final);

// Vista previa (un cuadro del video que arma el panel).
$poster = $_FILES['poster'] ?? null;
if ($poster && ($poster['error'] ?? 1) === UPLOAD_ERR_OK && @getimagesize($poster['tmp_name'])) {
    move_uploaded_file($poster['tmp_name'], $dirTmp . pathinfo($final, PATHINFO_FILENAME) . '-poster.jpg');
}

responder([
    'ok'    => true,
    'token' => 't:' . $final,
    'url'   => '../img/tmp/' . $final,
    'mb'    => round(filesize($dirTmp . $final) / 1048576, 1),
]);
