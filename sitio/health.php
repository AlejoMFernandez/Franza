<?php
// Health check to diagnose loading issues locally.
// This file is safe to keep only in local dev; do not deploy publicly.
use App\BaseDeDatos\DBConexion;

require_once __DIR__ . '/bootstrap/init.php';

header('Content-Type: text/plain; charset=utf-8');
set_time_limit(10);

echo "PHP OK\n";

try {
    $db = DBConexion::getConexion();
    // Small test query (no table assumed): select 1
    $stmt = $db->query('SELECT 1');
    $val = $stmt->fetchColumn();
    echo "DB OK (SELECT 1 => $val)\n";
} catch (Throwable $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}

echo "SESSION: ";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "active\n";
} else {
    echo "not active\n";
}

echo "DONE\n";
