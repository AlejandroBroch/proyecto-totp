<?php
session_start();
require_once 'db.php';

// Si ya está autenticado, redirigir al dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Por favor, rellena todos los campos.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            // Mensaje genérico para no revelar si el email existe
            $error = 'Credenciales incorrectas.';
        } elseif ($user['totp_enabled']) {
            // 2FA activado: guardar estado temporal y redirigir a verificación
            $_SESSION['2fa_pending_user_id'] = $user['id'];
            $_SESSION['2fa_pending_email']   = $user['email'];
            header('Location: verify-2fa.php');
            exit;
        } else {
            // Login directo sin 2FA
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email']   = $user['email'];
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/frontend/css/styles.css">
    <title>Iniciar sesión</title>
</head>
<body>
    <section>
        <h1>Iniciar sesión</h1>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Iniciar sesión</button>
        </form>
        <p>¿No tienes una cuenta? <a href="register.php" class="text-link">Regístrate aquí</a>.</p>
    </section>
    <script src="/frontend/js/login.js"></script>
</body>
</html>
