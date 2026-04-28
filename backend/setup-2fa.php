<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;
use RobThree\Auth\Algorithm; // <--- Añade esta línea

// Requiere sesión iniciada
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$email  = $_SESSION['email'];
$pdo    = getDB();

// El orden correcto es: (Issuer, Digits, Period, Algorithm, Provider)
$tfa = new TwoFactorAuth('Web de Ale y Pau', 6, 30, Algorithm::Sha1, new EndroidQrCodeProvider());$error  = '';
$success = '';

// Obtener usuario actual
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Si ya tiene 2FA activado, redirigir al dashboard
if ($user['totp_enabled']) {
    header('Location: dashboard.php');
    exit;
}

// Generar o recuperar clave secreta temporal (guardada en sesión hasta confirmar)
if (empty($_SESSION['totp_temp_secret'])) {
    $_SESSION['totp_temp_secret'] = $tfa->createSecret();
}
$secret = $_SESSION['totp_temp_secret'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if (strlen($code) !== 6 || !ctype_digit($code)) {
        $error = 'El código debe ser un número de 6 dígitos.';
    } elseif (!$tfa->verifyCode($secret, $code)) {
        $error = 'Código incorrecto. Asegúrate de haber escaneado el QR correctamente.';
    } else {
        // Guardar el secreto en la BD y activar 2FA
        $stmt = $pdo->prepare('UPDATE users SET totp_secret = ?, totp_enabled = 1 WHERE id = ?');
        $stmt->execute([$secret, $userId]);

        unset($_SESSION['totp_temp_secret']);
        $success = '¡Autenticación en dos pasos activada correctamente!';
    }
}

// Generar URL del QR para la app de autenticación
$qrCodeUrl = $tfa->getQRCodeImageAsDataUri($email, $secret);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/frontend/css/styles.css">
    <title>Configurar 2FA</title>
</head>
<body>
    <section>
        <h1>Activar autenticación en dos pasos</h1>

        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
            <a href="dashboard.php" class="btn">Ir al dashboard</a>
        <?php else: ?>
            <p>Escanea el código QR con tu app de autenticación (Google Authenticator, Aegis, etc.)</p>

            <img src="<?= $qrCodeUrl ?>" alt="Código QR para 2FA" style="width:200px;height:200px;margin:15px auto;display:block;">

            <div class="secret-box">Clave secreta manual: <strong><?= htmlspecialchars($secret) ?></strong></div>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form action="setup-2fa.php" method="post">
                <label for="code">Introduce el código de 6 dígitos para confirmar:</label>
                <input type="text" id="code" name="code" required maxlength="6" inputmode="numeric" autocomplete="one-time-code">
                <button type="submit">Confirmar activación</button>
            </form>

            <a href="dashboard.php" class="text-link">Volver al dashboard</a>
        <?php endif; ?>
    </section>
    <script src="/frontend/js/setup-2fa.js"></script>
</body>
</html>
