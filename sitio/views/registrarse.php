<?php
use App\Autenticacion\Autenticacion;

require_once __DIR__ . '/../bootstrap/init.php';

$autenticacion = new Autenticacion();

// Si ya está autenticado, redirigir
if($autenticacion->estaAutenticado()) {
    header('Location: index.php?seccion=inicio');
    exit;
}

// Mensajes de feedback y errores (si viene de index.php, ya están seteados en el scope)
if(!isset($mensajeFeedback)) {
    $mensajeFeedback = $_SESSION['mensajeFeedback'] ?? null;
    $estado = $_SESSION['estadoFeedback'] ?? 'exito';
}
$emailAnterior = $_SESSION['email_anterior'] ?? '';
$errores = $_SESSION['errores'] ?? [];
$datosViejos = $_SESSION['datos_viejos'] ?? [];
unset($_SESSION['mensajeFeedback'], $_SESSION['estadoFeedback'], $_SESSION['email_anterior'], $_SESSION['errores'], $_SESSION['datos_viejos']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="icon" href="favicon.png">
    <title>FRANZA: Crear Cuenta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="styles/css/acceder.css">

</head>
<body>
    <a href="index.php?seccion=inicio" class="btn-volver">← Volver</a>
    
    <?php if($mensajeFeedback): ?>
        <div class="msj msj-<?= htmlspecialchars($estado, ENT_QUOTES) ?>"><?= $mensajeFeedback;?></div>
    <?php endif; ?>
    
    <section class="login-section">
        <div class="login-container">
            <div class="login-logo">
                <img src="favicon.png" alt="FRANZA Logo">
            </div>
            
            <h1 class="login-title">Crear cuenta en FRANZA</h1>
            <p class="login-subtitle">Regístrate para gestionar tus obras</p>
            
            <div class="login-box">
                <form action="admin/acciones/registrarse.php" method="post" class="login-form">
                    <?= \App\Seguridad\Csrf::campo() ?>
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="tu@email.com"
                            required
                            autofocus
                            value="<?= htmlspecialchars($emailAnterior ?: ($datosViejos['email'] ?? ''), ENT_QUOTES) ?>"
                            class="<?= isset($errores['email']) ? 'input-error' : '' ?>"
                        >
                        <?php if(isset($errores['email'])): ?>
                            <span class="error-message"><?= $errores['email'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••"
                            required
                            class="<?= isset($errores['password']) ? 'input-error' : '' ?>"
                        >
                        <?php if(isset($errores['password'])): ?>
                            <span class="error-message"><?= $errores['password'] ?></span>
                        <?php else: ?>
                            <small class="form-hint">Mínimo 6 caracteres</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirmar contraseña</label>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            placeholder="••••••••"
                            required
                            class="<?= isset($errores['password_confirm']) ? 'input-error' : '' ?>"
                        >
                        <?php if(isset($errores['password_confirm'])): ?>
                            <span class="error-message"><?= $errores['password_confirm'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn-login">Crear cuenta</button>
                </form>
                
            </div>
            <div class="login-footer">
                <p>¿Ya tienes cuenta? <a href="index.php?seccion=iniciar-sesion">Accede</a></p>
            </div>
        </div>
    </section>
    
    <script src="js/mensaje.js"></script>
</body>
</html>
