<?php
session_start();
include 'includes/auth.php';
include 'includes/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $usuari = validarUsuari($username, $password);
    if ($usuari) {
        iniciarSessio($usuari);
        
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: dashboard_admin.php");
                break;
            case 'gestor':
                header("Location: dashboard_gestor.php");
                break;
            case 'client':
                header("Location: dashboard_client.php");
                break;
        }
        exit();
    } else {
        $error = "Nom d'usuari o contrasenya incorrectes.";
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Iniciar Sessió</h1>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <label>Nom d'usuari: <input type="text" name="username" required></label><br>
        <label>Contrasenya: <input type="password" name="password" required></label><br>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="index.php">Tornar a l'inici</a></p>
</body>
</html>
