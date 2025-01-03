<?php
session_start();
include 'includes/session.php';
include 'includes/auth.php';

// Verificar si el usuario es un gestor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'gestor') {
    header("Location: login.php");
    exit();
}

// Obtener el gestor actual
$gestorUsername = $_SESSION['username']; // El nombre de usuario del gestor actual

// Obtener todos los usuarios con rol "client"
$clients = obtenirUsuarisPerRol('client');

// Filtrar los clientes que tienen al gestor actual como su gestor asignado
$clientsDelGestor = array_filter($clients, function($client) use ($gestorUsername) {
    return $client['gestor'] === $gestorUsername;
});
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell de Gestor</title>
    <link rel="stylesheet" href="../css/dashboard_gestor_css.css">
</head>
<body>
    <h1>Benvingut, <?php echo htmlspecialchars($_SESSION['username']); ?> (Gestor)</h1>

    <!-- Llista de Clients del Gestor -->
    <h2>Llista de Clients Assignats</h2>
    <ul>
        <?php foreach ($clientsDelGestor as $client): ?>
            <li id="client-<?php echo htmlspecialchars($client['username']); ?>">
                <div class="client-info">
                    Usuari: <?php echo htmlspecialchars($client['username']); ?><br>
                    Correu: <?php echo htmlspecialchars($client['email']); ?><br>
                    Nom: <?php echo htmlspecialchars($client['nom']); ?> <?php echo htmlspecialchars($client['cognoms']); ?><br>
                    Telèfon: <?php echo htmlspecialchars($client['telefon']); ?><br>
                    Adreça: <?php echo htmlspecialchars($client['adreca']); ?><br>
                    Visa: <?php echo htmlspecialchars($client['visa']); ?><br>
                    Gestor: <?php echo htmlspecialchars($client['gestor']); ?><br>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Formulari per enviar un correu a l'administrador -->
    <h2>Enviar Petició a l'Administrador</h2>
    <form method="POST" action="send_request_to_admin.php">
        <label for="subject">Assumpte:</label>
        <input type="text" id="subject" name="subject" value="Petició d'addició/modificació/esborrament de client" required><br><br>
        
        <label for="message">Missatge:</label><br>
        <textarea id="message" name="message" rows="4" cols="50" required></textarea><br><br>

        <button type="submit" name="send_request">Enviar Petició</button>
    </form>

    <p><a href="logout.php">Tancar Sessió</a></p>
</body>
</html>
