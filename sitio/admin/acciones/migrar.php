<?php
/**
 * migrar.php — Acción: actualiza la base de datos para el panel nuevo (se puede repetir sin riesgo).
 */
use App\Autenticacion\Autenticacion;
use App\BaseDeDatos\Migracion;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';

Csrf::verificarOAbortar('../index.php?seccion=obras');
$auth = new Autenticacion;
if (!$auth->estaAutenticado() || !$auth->getUsuario()->esAdmin()) {
    header('Location: ../index.php?seccion=inicio');
    exit;
}

try {
    $data = require __DIR__ . '/../../data/franza.php';
    $log = Migracion::ejecutar($data['obras'] ?? []);
    $_SESSION['mensajeFeedback'] = '<b>Base de datos actualizada.</b>' . ($log ? '<br>' . implode('<br>', array_map(fn($l) => htmlspecialchars($l, ENT_QUOTES, 'UTF-8'), array_slice($log, 0, 12))) : '');
    $_SESSION['estadoFeedback'] = 'exito';
} catch (\Throwable $th) {
    error_log('Migración: ' . $th->getMessage());
    $_SESSION['mensajeFeedback'] = 'No se pudo actualizar la base de datos: ' . htmlspecialchars($th->getMessage(), ENT_QUOTES, 'UTF-8');
    $_SESSION['estadoFeedback'] = 'error';
}
header('Location: ../index.php?seccion=obras');
exit;
