<?php
session_start();
include 'includes/session.php';
include 'includes/auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Exemple: Llistat de gestors
$gestors = obtenirUsuarisPerRol('gestor');
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell d'Administrador</title>
</head>
<body>
    <h1>Benvingut, <?php echo htmlspecialchars($_SESSION['username']); ?> (Administrador)</h1>

    <h2>Llista de gestors</h2>
    <ul>
        <?php foreach ($gestors as $gestor): ?>
            <li><?php echo htmlspecialchars($gestor['username']); ?> - <?php echo htmlspecialchars($gestor['email']); ?></li>
        <?php endforeach; ?>
    </ul>

    <p><a href="logout.php">Tancar Sessió</a></p>
</body>
</html>
