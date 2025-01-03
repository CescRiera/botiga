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

// Ruta al archivo JSON
$rutaArchivo = '../productes.json';
$productes = file_exists($rutaArchivo) ? json_decode(file_get_contents($rutaArchivo), true) : [];

// Manejar la creación de productos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_producte'])) {
    $nomProducte = htmlspecialchars($_POST['nom_producte']);
    $idProducte = htmlspecialchars($_POST['id_producte']);
    $preuProducte = floatval($_POST['preu_producte']);
    $ivaProducte = floatval($_POST['iva_producte']);
    $disponibilitatProducte = isset($_POST['disponibilitat_producte']) ? 'si' : 'no';

    // Crear un array para el producto
    $producte = [
        'nom' => $nomProducte,
        'id' => $idProducte,
        'preu' => $preuProducte,
        'iva' => $ivaProducte,
        'disponibilitat' => $disponibilitatProducte
    ];

    // Añadir el nuevo producto
    $productes[] = $producte;

    // Guardar los productos en el archivo JSON
    file_put_contents($rutaArchivo, json_encode($productes, JSON_PRETTY_PRINT));

    $mensajeExito = "El producte s'ha creat correctament.";
}

// Manejar la edición de productos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_producte'])) {
    $idProducte = htmlspecialchars($_POST['id_producte']);

    foreach ($productes as &$producte) {
        if ($producte['id'] === $idProducte) {
            $producte['nom'] = htmlspecialchars($_POST['nom_producte']);
            $producte['preu'] = floatval($_POST['preu_producte']);
            $producte['iva'] = floatval($_POST['iva_producte']);
            $producte['disponibilitat'] = isset($_POST['disponibilitat_producte']) ? 'si' : 'no';
            break;
        }
    }

    // Guardar los productos actualizados en el archivo JSON
    file_put_contents($rutaArchivo, json_encode($productes, JSON_PRETTY_PRINT));

    $mensajeExito = "El producte s'ha editat correctament.";
}

// Manejar la eliminación de productos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_producte'])) {
    $idProducte = htmlspecialchars($_POST['id_producte']);

    // Filtrar los productos para eliminar el seleccionado
    $productes = array_filter($productes, function($producte) use ($idProducte) {
        return $producte['id'] !== $idProducte;
    });

    // Guardar los productos actualizados en el archivo JSON
    file_put_contents($rutaArchivo, json_encode(array_values($productes), JSON_PRETTY_PRINT));

    $mensajeExito = "El producte s'ha eliminat correctament.";
}

// Manejar la visualización de productos
$productosOrdenados = $productes;
usort($productosOrdenados, function ($a, $b) {
    return strcmp($a['nom'], $b['nom']);
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

    <!-- Secció per crear productes -->
    <h2>Crear Producte</h2>
    <?php if (isset($mensajeExito)): ?>
        <p style="color: green;"> <?php echo $mensajeExito; ?> </p>
    <?php endif; ?>
    <form method="POST">
        <label for="nom_producte">Nom del Producte:</label>
        <input type="text" id="nom_producte" name="nom_producte" required><br><br>

        <label for="id_producte">Número Identificador:</label>
        <input type="text" id="id_producte" name="id_producte" required><br><br>

        <label for="preu_producte">Preu:</label>
        <input type="number" step="0.01" id="preu_producte" name="preu_producte" required><br><br>

        <label for="iva_producte">IVA:</label>
        <input type="number" step="0.01" id="iva_producte" name="iva_producte" required><br><br>

        <label for="disponibilitat_producte">Disponibilitat:</label>
        <input type="checkbox" id="disponibilitat_producte" name="disponibilitat_producte"><br><br>

        <button type="submit" name="crear_producte">Crear Producte</button>
    </form>

    <!-- Secció per editar productes -->
    <h2>Editar Producte</h2>
    <form method="POST">
        <label for="id_producte">Número Identificador del Producte a Editar:</label>
        <input type="text" id="id_producte" name="id_producte" required><br><br>

        <label for="nom_producte">Nou Nom del Producte:</label>
        <input type="text" id="nom_producte" name="nom_producte" required><br><br>

        <label for="preu_producte">Nou Preu:</label>
        <input type="number" step="0.01" id="preu_producte" name="preu_producte" required><br><br>

        <label for="iva_producte">Nou IVA:</label>
        <input type="number" step="0.01" id="iva_producte" name="iva_producte" required><br><br>

        <label for="disponibilitat_producte">Nova Disponibilitat:</label>
        <input type="checkbox" id="disponibilitat_producte" name="disponibilitat_producte"><br><br>

        <button type="submit" name="editar_producte">Editar Producte</button>
    </form>

    <!-- Secció per eliminar productes -->
    <h2>Eliminar Producte</h2>
    <form method="POST">
        <label for="id_producte">Número Identificador del Producte a Eliminar:</label>
        <input type="text" id="id_producte" name="id_producte" required><br><br>

        <button type="submit" name="eliminar_producte">Eliminar Producte</button>
    </form>

    <!-- Secció per llistar productes -->
    <h2>Llistar Productes</h2>
    <button onclick="document.getElementById('llista-productes').style.display='block';">Mostrar Llista de Productes</button>
    <div id="llista-productes" style="display:none; margin-top: 20px;">
        <h3>Llista de Productes</h3>
        <?php if (empty($productosOrdenados)): ?>
            <p>No hi ha productes disponibles.</p>
        <?php else: ?>
            <?php foreach ($productosOrdenados as $producte): ?>
                <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                    <strong>Nom:</strong> <?php echo htmlspecialchars($producte['nom']); ?><br>
                    <strong>ID:</strong> <?php echo htmlspecialchars($producte['id']); ?><br>
                    <strong>Preu:</strong> <?php echo htmlspecialchars(number_format($producte['preu'], 2)); ?>€<br>
                    <strong>IVA:</strong> <?php echo htmlspecialchars(number_format($producte['iva'], 2)); ?>%<br>
                    <strong>Disponibilitat:</strong> <?php echo htmlspecialchars($producte['disponibilitat']); ?><br>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <p><a href="logout.php">Tancar Sessió</a></p>
</body>
</html>
