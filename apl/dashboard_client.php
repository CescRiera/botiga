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
$rutaArchivo = '../productes.json';
$productes = file_exists($rutaArchivo) ? json_decode(file_get_contents($rutaArchivo), true) : [];
$productosOrdenados = $productes;
usort($productosOrdenados, function ($a, $b) {
    return strcmp($a['nom'], $b['nom']);
});

$cistella = [];
$totalPreu = 0.00;

// Procesar el formulario cuando se envíe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productes_seleccionats'])) {
    foreach ($_POST['productes_seleccionats'] as $producteId) {
        // Buscar el producto en la lista original (simula datos desde la base de datos o array)
        foreach ($productosOrdenados as $producte) {
            if ($producte['id'] == $producteId) {
                $cistella[] = $producte;
                $totalPreu += $producte['preu'];
                break;
            }
        }
    }
}
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
        <input type="text" id="subject_mod" name="subject"
            value="petició de modificació/esborrament del compte de client" readonly required><br><br>

        <label for="message_mod">Missatge:</label><br>
        <textarea id="message_mod" name="message" rows="4" cols="50" placeholder="Escriu aquí el teu missatge..."
            required></textarea><br><br>

        <button type="submit" name="send_request">Enviar Petició</button>
    </form>

    <!-- Formulari per demanar la justificació d'una comanda rebutjada -->
    <h2>Petició de Justificació de Comanda Rebutjada</h2>
    <form method="POST" action="send_request_to_gestor.php">
        <label for="subject_just">Assumpte:</label>
        <input type="text" id="subject_just" name="subject" value="petició de justificació de comanda rebutjada"
            readonly required><br><br>

        <label for="message_just">Missatge:</label><br>
        <textarea id="message_just" name="message" rows="4" cols="50" placeholder="Escriu aquí el teu missatge..."
            required></textarea><br><br>

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
            <li><strong>Identificador:</strong> <?php echo htmlspecialchars($currentClientDetails['idnentificador']); ?>
            </li>
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
    
    <h2>Llistar Productes</h2>
    <button onclick="document.getElementById('llista-productes').style.display='block';">Mostrar Llista de
        Productes</button>
    <div id="llista-productes" style="display:none; margin-top: 20px;">
        <h3>Llista de Productes</h3>
        <form method="post" action="">
            <?php if (empty($productosOrdenados)): ?>
                <p>No hi ha productes disponibles.</p>
            <?php else: ?>
                <?php foreach ($productosOrdenados as $producte): ?>
                    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                        <input type="checkbox" name="productes_seleccionats[]"
                            value="<?php echo htmlspecialchars($producte['id']); ?>">
                        <strong>Nom:</strong> <?php echo htmlspecialchars($producte['nom']); ?><br>
                        <strong>Preu:</strong> <?php echo htmlspecialchars(number_format($producte['preu'], 2)); ?>€<br>
                        <strong>Disponibilitat:</strong> <?php echo htmlspecialchars($producte['disponibilitat']); ?><br>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <button type="submit" style="margin-top: 10px;">Afegir a la Cistella</button>
        </form>
    </div>

    <?php if (!empty($cistella)): ?>
        <div id="cistella" style="margin-top: 20px; border: 1px solid #000; padding: 10px;">
            <h3>Cistella de Productes</h3>
            <ul>
                <?php foreach ($cistella as $producte): ?>
                    <li><?php echo htmlspecialchars($producte['nom']); ?> -
                        <?php echo htmlspecialchars(number_format($producte['preu'], 2)); ?>€</li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Total:</strong> <?php echo htmlspecialchars(number_format($totalPreu, 2)); ?>€</p>
        </div>
    <?php endif; ?>
    <p><a href="login.php">Tancar Sessio</a></p>

</html>