<?php
require_once __DIR__ . '/../../bootstrap/init.php';

// Mensajes de feedback
$mensajeFeedback = $_SESSION['mensajeFeedback'] ?? null;
$estado = $_SESSION['estadoFeedback'] ?? 'exito';
$emailAnterior = $_SESSION['email_anterior'] ?? '';
unset($_SESSION['mensajeFeedback'], $_SESSION['estadoFeedback'], $_SESSION['email_anterior']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="icon" href="../favicon.png">
    <title>Registro Admin - FRANZA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="../styles/css/acceder.css">
    <style>
        /* Estilos específicos de admin registro (si los necesita) */
    </style>
</head>
<body>
    <a href="../index.php?seccion=inicio" class="btn-volver">← Volver</a>
    
    <?php if($mensajeFeedback): ?>
        <div class="msj msj-<?= htmlspecialchars($estado, ENT_QUOTES) ?> animate__animated animate__bounceInDown"><?= $mensajeFeedback;?></div>
    <?php endif; ?>
    
    <section class="login-section">
        <div class="login-container">
            <div class="login-logo">
                <img src="../favicon.png" alt="FRANZA Logo">
            </div>
            
            <h1 class="login-title">Crear cuenta en FRANZA</h1>
            <p class="login-subtitle">Regístrate para acceder al sistema</p>
            
            <div class="login-box">
                <form action="acciones/registrarse.php" method="post" class="login-form">
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
                            value="<?= htmlspecialchars($emailAnterior, ENT_QUOTES) ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••"
                            required
                        >
                        <small class="form-hint">Mínimo 6 caracteres</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirmar contraseña</label>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            placeholder="••••••••"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn-login">Crear cuenta</button>
                </form>
                
                <div class="login-footer">
                    <p>¿Ya tienes cuenta? <a href="index.php?seccion=inicio">Accede</a></p>
                </div>
            </div>
        </div>
    </section>
    
    <script src="../js/mensaje.js"></script>
</body>
</html>