<?php
/**
 * fotos-subir.php — Acción (fetch): sube UNA foto apenas el admin la agrega en el editor.
 * La foto queda en img/tmp/ hasta que se guarda la obra (ahí se mueve a su carpeta).
 * Así el botón "Guardar" ya no manda 40 fotos juntas y nunca supera el límite del servidor.
 * Responde JSON: { ok, token: "t:archivo", url, kb }.
 */
use App\Autenticacion\Autenticacion;
use App\Seguridad\Csrf;
use App\Imagenes\ImagenOptimizador;

require_once __DIR__ . '/../../bootstrap/init.php';
header('Content-Type: application/json; charset=utf-8');

function responder(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    responder(['ok' => false, 'error' => 'La foto pesa demasiado para el servidor.'], 413);
}

$auth = new Autenticacion;
if (!Csrf::validar() || !$auth->estaAutenticado() || !$auth->getUsuario()->esAdmin()) {
    responder(['ok' => false, 'error' => 'Se cerró la sesión. Recargá la página e ingresá de nuevo.'], 403);
}

$f = $_FILES['foto'] ?? null;
if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $porTamano = in_array($f['error'] ?? 0, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
    responder(['ok' => false, 'error' => $porTamano ? 'La foto pesa demasiado para el servidor.' : 'No llegó la foto. Probá de nuevo.'], 400);
}

$ext = strtolower(pathinfo((string) $f['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) || $f['size'] > 20 * 1024 * 1024 || !@getimagesize($f['tmp_name'])) {
    responder(['ok' => false, 'error' => 'Ese archivo no es una foto válida (JPG, PNG o WebP).'], 400);
}

$sitio = realpath(__DIR__ . '/../../');
$dirTmp = $sitio . '/img/tmp/';
if (!is_dir($dirTmp)) {
    mkdir($dirTmp, 0775, true);
}

// Limpieza: fotos subidas hace más de 3 días que nunca se guardaron.
foreach (glob($dirTmp . '*') ?: [] as $viejo) {
    if (is_file($viejo) && filemtime($viejo) < time() - 3 * 86400) {
        @unlink($viejo);
    }
}

$base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo((string) $f['name'], PATHINFO_FILENAME));
$nombreBase = substr($base, 0, 40) . '_' . bin2hex(random_bytes(5));

$guardado = ImagenOptimizador::guardarOptimizado($f['tmp_name'], $dirTmp, $nombreBase, 2000, 82);
if ($guardado === false) {
    // Sin GD en el servidor: se guarda tal cual (el navegador ya la achicó).
    $guardado = $nombreBase . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    if (!move_uploaded_file($f['tmp_name'], $dirTmp . $guardado)) {
        responder(['ok' => false, 'error' => 'No se pudo guardar la foto en el servidor.'], 500);
    }
}

responder([
    'ok'    => true,
    'token' => 't:' . $guardado,
    'url'   => '../img/tmp/' . $guardado,
    'kb'    => (int) round(filesize($dirTmp . $guardado) / 1024),
]);
