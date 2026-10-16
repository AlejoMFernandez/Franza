<?php
/**
 * admin/views/inicio.php — Login del panel de administración.
 */
require_once __DIR__ . '/../../bootstrap/init.php';
require_once __DIR__ . '/../../includes/helpers.php';

$mensajeFeedback = $mensajeFeedback ?? ($_SESSION['mensajeFeedback'] ?? null);
$estado = $estado ?? ($_SESSION['estadoFeedback'] ?? 'exito');
$loginEmail = $_SESSION['login_email'] ?? '';
unset($_SESSION['mensajeFeedback'], $_SESSION['estadoFeedback'], $_SESSION['login_email']);
?>
<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#141311">
    <link rel="icon" href="../favicon.ico" sizes="32x32">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <title>Ingresar · Panel FRANZA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Geist:wght@400..700&family=Geist+Mono:wght@400..600&display=swap">
    <link rel="stylesheet" href="../styles/css/franza.css?v=2">
    <link rel="stylesheet" href="../styles/css/admin.css?v=2">
</head>
<body class="fz fz-login">
    <main class="login">
        <section class="login__foto" aria-hidden="true">
            <img src="../<?= e(fz_img('img/obrasciviles/restauraci-on-de-fachada/ObraBmeMitre_1771019414.jpg')) ?>" alt="">
            <div class="login__velo"></div>
            <div class="login__plano"></div>
            <div class="login__foto-texto">
                <span class="fz-mono fz-oro-claro">Panel de obras</span>
                <p>Cargá una obra y mirala como la va a ver tu cliente, antes de publicarla.</p>
            </div>
        </section>

        <section class="login__panel">
            <a href="../index.php?seccion=inicio" class="login__volver"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>Volver al sitio</a>

            <div class="login__caja">
                <img src="../img/franza/logo-claro.png" alt="FRANZA Construcciones" class="login__logo" width="220" height="87">
                <h1 class="login__titulo">Ingresá al panel</h1>

                <?php if ($mensajeFeedback): ?>
                    <p class="login__msj login__msj--<?= e($estado) ?>" role="alert"><?= strip_tags($mensajeFeedback, '<b>') ?></p>
                <?php endif; ?>

                <form action="acciones/iniciar-sesion.php" method="post" class="login__form" data-cargando="Ingresando…">
                    <?= \App\Seguridad\Csrf::campo() ?>
                    <label class="login__campo">
                        <span>Email</span>
                        <input type="email" id="email" name="email" autocomplete="username" required autofocus value="<?= e($loginEmail) ?>">
                    </label>
                    <label class="login__campo">
                        <span>Contraseña</span>
                        <span class="login__clave">
                            <input type="password" id="password" name="password" autocomplete="current-password" required>
                            <button type="button" class="login__ver" data-ver-clave aria-label="Mostrar contraseña" aria-pressed="false">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </span>
                    </label>
                    <button type="submit" class="adm-btn adm-btn--oro login__entrar">Ingresar</button>
                </form>
                <p class="login__ayuda">¿Te olvidaste la contraseña? Pedile a quien administra el sitio que te la restablezca.</p>
            </div>
        </section>
    </main>
    <script>
    document.querySelectorAll('[data-ver-clave]').forEach(function (b) {
        b.addEventListener('click', function () {
            var i = document.getElementById('password'), ver = i.type === 'password';
            i.type = ver ? 'text' : 'password';
            b.setAttribute('aria-pressed', String(ver));
            b.setAttribute('aria-label', ver ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });
    </script>
</body>
</html>
