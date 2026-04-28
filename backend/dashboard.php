<?php
session_start();

// Requiere sesión iniciada
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';
$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/frontend/css/styles.css">
    <title>Panel de usuario</title>
</head>
<body>
    <section>
        <h1>Dashboard</h1>
        <p>Bienvenido, <strong><?= htmlspecialchars($user['email']) ?></strong>.</p>
        <p>Aquí puedes gestionar tu cuenta.</p>

        <?php if ($user['totp_enabled']): ?>
            <p class="success">✔ Autenticación en dos pasos <strong>activada</strong>.</p>
        <?php else: ?>
            <p>La autenticación en dos pasos no está activada.</p>
            <a href="setup-2fa.php" class="btn">Activar 2FA</a>
        <?php endif; ?>

        <a href="logout.php" class="btn">Cerrar sesión</a>
    </section>
</body>
</html>
