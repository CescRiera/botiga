<?php
session_start();
include 'includes/session.php';
include 'includes/auth.php';

// Get all clients and the current client's username from the session
$clients = obtenirUsuarisPerRol('client');
$currentClientUsername = $_SESSION['username'] ?? null;

// Find the current client's information
$currentClientDetails = array_values(array_filter($clients, fn($client) => $client['username'] === $currentClientUsername))[0] ?? null;

// Handle form submission to toggle the display of personal data
$showData = $_POST['toggle_personal_data'] ?? null === 'show';
$productes = json_decode(file_get_contents('../productes.json'), true) ?? [];
usort($productes, fn($a, $b) => strcmp($a['nom'], $b['nom']));

$cistella = [];
$totalPreu = 0.00;

// Process selected products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productes_seleccionats'])) {
    foreach ($_POST['productes_seleccionats'] as $producteId) {
        $producte = current(array_filter($productes, fn($p) => $p['id'] == $producteId));
        if ($producte) {
            $cistella[] = $producte;
            $totalPreu += $producte['preu'];
        }
    }
}

if (isset($_POST['quantitats'])) {
    $updatedCart = [];
    $totalPreu = 0;

    foreach ($_POST['quantitats'] as $producteId => $quantitat) {
        $quantitat = max(1, intval($quantitat)); // Ensure quantity is at least 1
        $producte = current(array_filter($productes, fn($p) => $p['id'] == $producteId));

        if ($producte) {
            $producte['quantitat'] = $quantitat;
            $updatedCart[] = $producte;
            $totalPreu += $producte['preu'] * $quantitat;
        }
    }

    $cistella = $updatedCart;
    $_SESSION['cistella'] = $updatedCart; // Save the updated cart in session
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

    <!-- Formularis de peticions -->
    <h2>Petició de Modificació/Esborrament del Compte</h2>
    <form method="POST" action="send_request_to_gestor.php">
        <label>Assumpte</label><br/>
        <input type="text" name="subject" value="petició de modificació/esborrament del compte de client" readonly required><br/><br/>
        <label>Missatge</label><br/>
        <textarea name="message" rows="4" cols="50" placeholder="Escriu aquí el teu missatge..." required></textarea><br/><br/>
        <button type="submit" name="send_request">Enviar Petició</button>
    </form>

    <h2>Petició de Justificació de Comanda Rebutjada</h2>
    <form method="POST" action="send_request_to_gestor.php">
    <label>Assumpte</label><br/>
        <input type="text" name="subject" value="petició de justificació de comanda rebutjada" readonly required><br/><br/>
        <label>Missatge</label><br/>
        <textarea name="message" rows="4" cols="50" placeholder="Escriu aquí el teu missatge..." required></textarea><br/><br/>
        <button type="submit" name="send_request">Enviar Petició</button>
    </form>

    <!-- Botó per mostrar/ocultar les dades personals -->
    <h2>Dades Personals</h2>
    <form method="POST">
        <button type="submit" name="toggle_personal_data" value="<?= $showData ? 'hide' : 'show'; ?>">
            <?= $showData ? 'Ocultar Dades Personals' : 'Mostrar Dades Personals'; ?>
        </button>
    </form>

    <?php if ($showData && $currentClientDetails): ?>
        <h3>Les teves dades personals</h3>
        <ul>
            <?php foreach (['username', 'idnentificador', 'email', 'nom', 'cognoms', 'telefon', 'adreca', 'visa', 'gestor'] as $field): ?>
                <li><strong><?= ucfirst($field); ?>:</strong> <?= htmlspecialchars($currentClientDetails[$field]); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($showData): ?>
        <p style="color: red;">No s'han trobat dades personals per al client actual.</p>
    <?php endif; ?>

    <!-- Llistar Productes -->
    <h2>Llistar Productes</h2>
    <button onclick="document.getElementById('llista-productes').style.display='block';">Mostrar Llista de Productes</button>
    <div id="llista-productes" style="display:none; margin-top: 20px;">
        <h3>Llista de Productes</h3>
        <form method="post" action="">
            <?php if (empty($productes)): ?>
                <p>No hi ha productes disponibles.</p>
            <?php else: ?>
                <?php foreach ($productes as $producte): ?>
                    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                        <input type="checkbox" name="productes_seleccionats[]" value="<?= $producte['id']; ?>">
                        <strong>Nom:</strong> <?= htmlspecialchars($producte['nom']); ?><br>
                        <strong>Preu:</strong> <?= htmlspecialchars(number_format($producte['preu'], 2)); ?>€<br>
                        <strong>Disponibilitat:</strong> <?= htmlspecialchars($producte['disponibilitat']); ?><br>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <button type="submit" style="margin-top: 10px;">Afegir a la Cistella</button>
        </form>
    </div>
    <?php
    ?>
    <?php if ($cistella): ?>
        <div id="cistella" style="margin-top: 20px; border: 1px solid #000; padding: 10px;">
            <h3>Cistella de Productes</h3>
            <ul>
                <?php foreach ($cistella as $producte): ?>
                    <li><?= htmlspecialchars($producte['nom']); ?> - <?= htmlspecialchars(number_format($producte['preu'], 2)); ?>€
                        Quantitat: <input type="number" name="quantitats[<?= $producte['id']; ?>]" value="1" min="1">
                    </li>
                <?php endforeach; ?> 
            </ul>
            <p><strong>Total:</strong> <?= htmlspecialchars(number_format($totalPreu, 2)); ?>€</p>
            <button type="submit" style="margin-top: 10px;">Actualitzar Quantitats</button>
        </div>
    <?php endif; ?>

    <p><a href="login.php">Tancar Sessio</a></p>
</body>

</html>
