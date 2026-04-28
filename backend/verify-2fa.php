<?php
session_start();
require_once 'db.php';
require_once '/vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;

// Solo accesible si hay una autenticación 2FA pendiente
if (empty($_SESSION['2fa_pending_user_id'])) {
    header('Location: login.php');
    exit;
}

$pendingUserId = $_SESSION['2fa_pending_user_id'];
$pdo  = getDB();
$tfa  = new TwoFactorAuth(new EndroidQrCodeProvider(), 'Web de Ale y Pau');
$error = '';

// Obtener el secreto TOTP del usuario
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$pendingUserId]);
$user = $stmt->fetch();

if (!$user || !$user['totp_enabled']) {
    // Algo fue mal, limpiar sesión y redirigir
    unset($_SESSION['2fa_pending_user_id'], $_SESSION['2fa_pending_email']);
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if (strlen($code) !== 6 || !ctype_digit($code)) {
        $error = 'El código debe ser un número de 6 dígitos.';
    } elseif (!$tfa->verifyCode($user['totp_secret'], $code)) {
        $error = 'Código incorrecto o expirado. Inténtalo de nuevo.';
    } else {
        // Verificación correcta: completar el login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email']   = $user['email'];

        // Limpiar estado temporal de 2FA
        unset($_SESSION['2fa_pending_user_id'], $_SESSION['2fa_pending_email']);

        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/frontend/css/styles.css">
    <title>Verificar 2FA</title>
</head>
<body>
    <section>
        <h1>Verificar autenticación en dos pasos</h1>
        <p>Introduce el código de 6 dígitos generado por tu app de autenticación</p>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="verify-2fa.php" method="post">
            <label for="code">Código de autenticación:</label>
            <input type="text" id="code" name="code" required maxlength="6" inputmode="numeric" autocomplete="one-time-code">
            <button type="submit">Verificar</button>
        </form>
        <a href="login.php" class="text-link">Volver al inicio de sesión</a>
    </section>
    <script src="/frontend/js/verify-2fa.js"></script>
</body>
</html>
