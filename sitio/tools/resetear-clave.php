<?php
/**
 * resetear-clave.php — Pone una contraseña nueva a un usuario del panel.
 * Solo se usa desde la consola, nunca desde el navegador:
 *
 *   cd C:\xampp\htdocs\2 - CLIENTES\Franza-main\sitio
 *   C:\xampp\php\php.exe tools\resetear-clave.php email@ejemplo.com NuevaClave123
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Prohibido.');
}

require_once __DIR__ . '/../bootstrap/init.php';

use App\BaseDeDatos\DBConexion;

[$script, $email, $clave] = array_pad($argv, 3, null);
if (!$email || !$clave) {
    fwrite(STDERR, "Uso: php tools/resetear-clave.php email nuevaClave\n");
    exit(1);
}
if (strlen($clave) < 8) {
    fwrite(STDERR, "La clave tiene que tener al menos 8 caracteres.\n");
    exit(1);
}

$db = DBConexion::getConexion();
$stmt = $db->prepare('UPDATE usuarios SET password = ? WHERE email = ?');
$stmt->execute([password_hash($clave, PASSWORD_DEFAULT), $email]);

if ($stmt->rowCount() === 0) {
    fwrite(STDERR, "No hay ningún usuario con el email $email.\n");
    $lista = $db->query('SELECT email FROM usuarios ORDER BY usuario_id')->fetchAll(PDO::FETCH_COLUMN);
    fwrite(STDERR, "Usuarios cargados: " . implode(', ', $lista) . "\n");
    exit(1);
}
echo "Listo: la clave de $email se cambió.\n";
