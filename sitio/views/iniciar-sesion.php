<?php
use App\Autenticacion\Autenticacion;

require_once __DIR__ . '/../bootstrap/init.php';

$autenticacion = new Autenticacion();

// Si ya está autenticado, redirigir
if($autenticacion->estaAutenticado()) {
    header('Location: index.php?seccion=inicio');
    exit;
}

// Mensajes de feedback (si viene de index.php, ya están seteados en el scope)
if(!isset($mensajeFeedback)) {
    $mensajeFeedback = $_SESSION['mensajeFeedback'] ?? null;
    $estado = $_SESSION['estadoFeedback'] ?? 'exito';
}
$loginEmail = $_SESSION['login_email'] ?? '';
unset($_SESSION['mensajeFeedback'], $_SESSION['estadoFeedback'], $_SESSION['login_email']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="icon" href="favicon.png">
    <title>FRANZA: Iniciar Sesión</title>
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
            
            <h1 class="login-title">Iniciar sesión en FRANZA</h1>
            <p class="login-subtitle">Accede para gestionar tus obras</p>
            
            <div class="login-box">
                <form action="admin/acciones/iniciar-sesion.php" method="post" class="login-form">
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
                            value="<?= htmlspecialchars($loginEmail, ENT_QUOTES) ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Mostrar contraseña">
                                <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">Acceder</button>
                </form>
            </div>

            <div class="login-footer">
                <p>¿No tienes cuenta? <a href="index.php?seccion=registrarse">Créala</a></p>
            </div>
        </div>
    </section>
    
    <script src="js/mensaje.js"></script>
    <script>
    function togglePassword() {
        const input = document.getElementById('password');
        const btn = document.querySelector('.password-toggle');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.querySelector('.eye-open').style.display = isHidden ? 'none' : 'block';
        btn.querySelector('.eye-closed').style.display = isHidden ? 'block' : 'none';
    }
    </script>
</body>
</html>
