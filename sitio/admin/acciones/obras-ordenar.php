<?php
/**
 * obras-ordenar.php — Acción (fetch): guarda el orden de las obras o marca/desmarca una destacada.
 * Responde JSON. Requiere admin y token CSRF.
 */
use App\Autenticacion\Autenticacion;
use App\Modelos\Obras;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';
header('Content-Type: application/json; charset=utf-8');

$auth = new Autenticacion;
if (!Csrf::validar() || !$auth->estaAutenticado() || !$auth->getUsuario()->esAdmin()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Sin permiso. Recargá la página e intentá de nuevo.']);
    exit;
}

try {
    $modelo = new Obras();
    if (isset($_POST['destacar'])) {
        $modelo->marcarDestacada((int) $_POST['destacar'], ($_POST['valor'] ?? '0') === '1');
    } else {
        $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
        $modelo->ordenar(array_filter($ids));
    }
    echo json_encode(['ok' => true]);
} catch (\Throwable $th) {
    error_log('Ordenar obras: ' . $th->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar. Probá de nuevo.']);
}
