<?php
session_start();
include 'includes/session.php';
include 'includes/auth.php';

$clients = obtenirUsuarisPerRol('client');
$currentClientUsername = $_SESSION['username'] ?? null;

$currentClientDetails = array_values(array_filter($clients, fn($client) => $client['username'] === $currentClientUsername))[0] ?? null;

$showData = $_POST['toggle_personal_data'] ?? null === 'show';
$productes = json_decode(file_get_contents('../productes.json'), true) ?? [];
usort($productes, fn($a, $b) => strcmp($a['nom'], $b['nom']));

$cistella = [];
$preuSenseIVA = 0.00;
$valorIVA = 0.00;
$totalPreu = 0.00;
$fecha = date("Y-m-d H:i");

$cistellesDir = '../cistelles';
if ($currentClientUsername && is_dir($cistellesDir)) {
    $jsonFilePath = "$cistellesDir/{$currentClientUsername}.json";
    if (file_exists($jsonFilePath)) {
        $cistellaData = json_decode(file_get_contents($jsonFilePath), true);
        if ($cistellaData) {
            $cistella = $cistellaData['productes'] ?? [];
            $preuSenseIVA = $cistellaData['preuSenseIVA'] ?? 0.00;
            $valorIVA = $cistellaData['valorIVA'] ?? 0.00;
            $totalPreu = $cistellaData['totalPreu'] ?? 0.00;
            $fecha = $cistellaData['fecha'] ?? $fecha;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantitats'])) {
    foreach ($_POST['quantitats'] as $producteId => $quantitat) {
        $quantitat = max(0, intval($quantitat));
        $producte = current(array_filter($productes, fn($p) => $p['id'] == $producteId));

        if ($producte && $quantitat > 0) {
            $producte['quantitat'] = $quantitat;
            $cistella[] = $producte;
            $totalPreu += ($producte['preu'] * (1 + $producte['iva'] / 100)) * $quantitat;
            $preuSenseIVA += $producte['preu'] * $quantitat;
            $valorIVA += ($producte['preu'] * $producte['iva'] / 100) * $quantitat;
            $fecha = date("Y-m-d H:i");
        }
    }

    $_SESSION['cistella'] = $cistella;

    if ($currentClientUsername && !empty($cistella)) {
        $cistellaData = [
            'username' => $currentClientUsername,
            'fecha' => $fecha,
            'preuSenseIVA' => $preuSenseIVA,
            'valorIVA' => $valorIVA,
            'totalPreu' => $totalPreu,
            'productes' => $cistella,
        ];

        if (!is_dir($cistellesDir)) {
            mkdir($cistellesDir, 0755, true);
        }

        $jsonFilePath = "$cistellesDir/{$currentClientUsername}.json";
        file_put_contents($jsonFilePath, json_encode($cistellaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'confirm_purchase') {
        $message = "Compra confirmada! Gràcies per la teva compra.";
        $_SESSION['cistella'] = []; 
        unlink($jsonFilePath); 
    } elseif ($_POST['action'] === 'show_confirmation') {
        $showConfirmation = true;
    }
}


?>

<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <title>Enviar Peticions al Gestor</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script>
        function updateQuantity(productId, increment) {
            const quantityInput = document.getElementById(`quantity-${productId}`);
            let currentQuantity = parseInt(quantityInput.value) || 0;
            currentQuantity = Math.max(0, currentQuantity + increment);
            quantityInput.value = currentQuantity;
        }
        function mostrarConfirmacio() {
            const confirmacioDiv = document.getElementById("confirmacio");
            confirmacioDiv.style.display = "block";
        }
    </script>
</head>

<body>
    <h1>Interficide Client</h1>

    <h2>Petició de Modificació/Esborrament del Compte</h2>
    <form method="POST" action="send_request_to_gestor.php">
        <label>Assumpte</label><br />
        <input type="text" name="subject" value="petició de modificació/esborrament del compte de client" readonly
            required><br /><br />
        <label>Missatge</label><br />
        <textarea name="message" rows="4" cols="50" placeholder="Escriu aquí el teu missatge..."
            required></textarea><br /><br />
        <button type="submit" name="send_request">Enviar Petició</button>
    </form>

    <h2>Petició de Justificació de Comanda Rebutjada</h2>
    <form method="POST" action="send_request_to_gestor.php">
        <label>Assumpte</label><br />
        <input type="text" name="subject" value="petició de justificació de comanda rebutjada" readonly
            required><br /><br />
        <label>Missatge</label><br />
        <textarea name="message" rows="4" cols="50" placeholder="Escriu aquí el teu missatge..."
            required></textarea><br /><br />
        <button type="submit" name="send_request">Enviar Petició</button>
    </form>

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

    <h2>Llistar Productes</h2>
    <form method="post" action="">
        <div id="llista-productes" style="margin-top: 20px;">
            <h3>Llista de Productes</h3>
            <?php if (empty($productes)): ?>
                <p>No hi ha productes disponibles.</p>
            <?php else: ?>
                <?php foreach ($productes as $producte): ?>
                    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                        <strong>Nom:</strong> <?= htmlspecialchars($producte['nom']); ?><br>
                        <strong>Preu:</strong> <?= htmlspecialchars(number_format($producte['preu'], 2)); ?>€<br>
                        <strong>Disponibilitat:</strong> <?= htmlspecialchars($producte['disponibilitat']); ?><br>
                        <label>Quantitat:</label>
                        <button type="button" onclick="updateQuantity(<?= $producte['id']; ?>, -1)">-</button>
                        <input type="number" id="quantity-<?= $producte['id']; ?>" name="quantitats[<?= $producte['id']; ?>]"
                            value="0" min="0" style="width: 50px; text-align: center;">
                        <button type="button" onclick="updateQuantity(<?= $producte['id']; ?>, 1)">+</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <button type="submit" style="margin-top: 10px;">Afegir a la Cistella</button>
        </div>
    </form>

    <?php if (!empty($cistella)): ?>
        <div id="cistella" style="margin-top: 20px; border: 1px solid #000; padding: 10px;">
            <h3>Cistella de Productes</h3>
            <ul>
                <?php foreach ($cistella as $producte): ?>
                    <li><?= htmlspecialchars($producte['nom']); ?> -
                        <?= htmlspecialchars(number_format($producte['preu'], 2)); ?>€
                        (Quantitat: <?= htmlspecialchars($producte['quantitat']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
            <p><?php echo $fecha; ?></p>
            <p><strong>Preu Sense IVA:</strong> <?= htmlspecialchars(number_format($preuSenseIVA, 2)); ?>€</p>
            <p><strong>Valor IVA:</strong> <?= htmlspecialchars(number_format($valorIVA, 2)); ?>€</p>
            <p><strong>Total:</strong> <?= htmlspecialchars(number_format($totalPreu, 2)); ?>€</p>

            <button type="button" onclick="mostrarConfirmacio()">Acceptar Comanda</button>
            <button type="button">Esborrar Cistella</button>
        </div>

        <div id="confirmacio" style=" display: none; margin-top: 10px; padding: 10px; border: 1px solid green; background-color: #f0fff0;">
            <p><strong>Estàs segur que vols confirmar la compra per el valor de <?= htmlspecialchars(number_format($totalPreu, 2)); ?>€?</strong></p>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="confirm_purchase">
                <button type="submit">Sí, confirmar</button>
            </form>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="cancel_confirmation">
                <button type="submit">No, cancel·lar</button>
            </form>
        </div>

    <?php endif; ?>

    <p><a href="login.php">Tancar Sessio</a></p>
</body>

</html>