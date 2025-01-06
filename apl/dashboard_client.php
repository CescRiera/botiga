<?php
session_start();
include 'includes/session.php';
include 'includes/auth.php';

// Get all clients
$clients = obtenirUsuarisPerRol('client');

// Get the current client's username from the session
$currentClientUsername = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Find the current client's information
$currentClient = array_filter($clients, function ($client) use ($currentClientUsername) {
    return $client['username'] === $currentClientUsername;
});

// Retrieve the current client's details
$currentClientDetails = !empty($currentClient) ? reset($currentClient) : null;

// Handle form submission to toggle the display of personal data
$showData = isset($_POST['toggle_personal_data']) && $_POST['toggle_personal_data'] === 'show';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Enviar Peticions al Gestor</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Interficide Client</h1>

    <!-- Formulari per demanar la modificació o esborrament del compte -->
    <h2>Petició de Modificació/Esborrament del Compte</h2>
    <form method="POST" action="send_request_to_gestor.php">
        <label for="subject_mod">Assumpte:</label>
        <input type="text" id="subject_mod" name="subject" value="petició de modificació/esborrament del compte de client" readonly required><br><br>
        
        <label for="message_mod">Missatge:</label><br>
        <textarea id="message_mod" name="message" rows="4" cols="50" placeholder="Escriu aquí el teu missatge..." required></textarea><br><br>
        
        <button type="submit" name="send_request">Enviar Petició</button>
    </form>

    <!-- Formulari per demanar la justificació d'una comanda rebutjada -->
    <h2>Petició de Justificació de Comanda Rebutjada</h2>
    <form method="POST" action="send_request_to_gestor.php">
        <label for="subject_just">Assumpte:</label>
        <input type="text" id="subject_just" name="subject" value="petició de justificació de comanda rebutjada" readonly required><br><br>
        
        <label for="message_just">Missatge:</label><br>
        <textarea id="message_just" name="message" rows="4" cols="50" placeholder="Escriu aquí el teu missatge..." required></textarea><br><br>
        
        <button type="submit" name="send_request">Enviar Petició</button>
    </form>

    <!-- Botó per mostrar/ocultar les dades personals -->
    <h2>Dades Personals</h2>
    <form method="POST">
        <button type="submit" name="toggle_personal_data" value="<?php echo $showData ? 'hide' : 'show'; ?>">
            <?php echo $showData ? 'Ocultar Dades Personals' : 'Mostrar Dades Personals'; ?>
        </button>
    </form>

    <?php if ($showData && $currentClientDetails): ?>
        <h3>Les teves dades personals</h3>
        <ul>
            <li><strong>Nom d'usuari:</strong> <?php echo htmlspecialchars($currentClientDetails['username']); ?></li>
            <li><strong>Identificador:</strong> <?php echo htmlspecialchars($currentClientDetails['idnentificador']); ?></li>
            <li><strong>Correu electrònic:</strong> <?php echo htmlspecialchars($currentClientDetails['email']); ?></li>
            <li><strong>Nom:</strong> <?php echo htmlspecialchars($currentClientDetails['nom']); ?></li>
            <li><strong>Cognoms:</strong> <?php echo htmlspecialchars($currentClientDetails['cognoms']); ?></li>
            <li><strong>Telèfon:</strong> <?php echo htmlspecialchars($currentClientDetails['telefon']); ?></li>
            <li><strong>Adreça:</strong> <?php echo htmlspecialchars($currentClientDetails['adreca']); ?></li>
            <li><strong>Visa:</strong> <?php echo htmlspecialchars($currentClientDetails['visa']); ?></li>
            <li><strong>Gestor assignat:</strong> <?php echo htmlspecialchars($currentClientDetails['gestor']); ?></li>
        </ul>
    <?php elseif ($showData): ?>
        <p style="color: red;">No s'han trobat dades personals per al client actual.</p>
    <?php endif; ?>

    <p><a href="login.php">Tancar Sessio</a></p>
</body>
</html>
