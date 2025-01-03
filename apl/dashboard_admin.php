<?php
// Suponiendo que ya se incluye la librería FPDF
require('fpdf186/fpdf.php');

session_start();
include 'includes/session.php';
include 'includes/auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Obtener gestores, clientes y admins
$gestors = obtenirUsuarisPerRol('gestor');
$clients = obtenirUsuarisPerRol('client');
$admins = obtenirUsuarisPerRol('admin'); // Obtener administradores

// Manejar creación de un nuevo usuario (gestor o cliente)
if (isset($_POST['create_user'])) {
    $role = $_POST['role']; // Obtener el rol (gestor o cliente)
    if ($role == 'gestor') {

        $newUser = [
            'username' => $_POST['username'],
            'idnentificador' => $_POST['identificador'],
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT), // Utilizamos password_hash
            'role' => $role,
            'email' => $_POST['email'],
            'nom' => $_POST['nom'], // Añadimos nombre
            'cognoms' => $_POST['cognoms'], // Apellidos
            'telefon' => $_POST['telefon'], // Teléfono
        ];
        afegirUsuari($newUser); // Añadir el nuevo usuario a usuaris.json

    }

    // Si es un cliente, añadir los campos adicionales
    if ($role == 'client') {
        if (empty($_POST['adreca']) || empty($_POST['visa']) || empty($_POST['gestor'])) {
            echo "Tots els camps per al client són obligatoris!";
        } else {
            $newUser = [
                'username' => $_POST['username'],
                'idnentificador' => $_POST['identificador'],
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT), // Utilizamos password_hash
                'role' => $role,
                'email' => $_POST['email'],
                'nom' => $_POST['nom'], // Añadimos nombre
                'cognoms' => $_POST['cognoms'], // Apellidos
                'telefon' => $_POST['telefon'], // Teléfono
            ];
            $newUser['adreca'] = $_POST['adreca']; // Dirección postal
            $newUser['visa'] = $_POST['visa']; // Número de visa
            $newUser['gestor'] = $_POST['gestor']; // Gestor asignado
            afegirUsuari($newUser); // Añadir el nuevo usuario a usuaris.json
        }
    }
}

// Manejar eliminación de usuario
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    eliminarUsuari($userId);
}

// Manejar la edición de un administrador
if (isset($_POST['edit_admin'])) {
    $adminId = $_POST['admin_id'];
    $updatedAdmin = [
        'username' => $_POST['username'],
        'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
        'email' => $_POST['email']
    ];
    editarUsuari($adminId, $updatedAdmin); // Actualizar el usuario en usuaris.json
}

// Manejar la edición de un gestor
// Handle the editing of a gestor
if (isset($_POST['edit_gestor'])) {
    $gestorId = $_POST['admin_id']; // Get the gestor's username or ID
    $updatedGestor = [
        'username' => $_POST['username'],
        'password' => $_POST['password'], // Raw password, will be hashed in the function
        'email' => $_POST['email'],

        'telefon' => $_POST['telefon'],
    ];
    editarGestor($gestorId, $updatedGestor); // Call the editarGestor function
}

// Handle the editing of a client
if (isset($_POST['edit_client'])) {
    $clientId = $_POST['admin_id']; // Get the client's username or ID
    $updatedClient = [
        'username' => $_POST['username'],
        'password' => $_POST['password'], // Raw password, will be hashed in the function
        'email' => $_POST['email'],

        'telefon' => $_POST['telefon'],
        'adreca' => $_POST['adreca'], // Address
        'visa' => $_POST['visa'], // Visa
        'gestor' => $_POST['gestor'], // Gestor
    ];
    editarClient($clientId, $updatedClient); // Call the editarClient function
}



// Generar PDF de la lista de usuarios
if (isset($_POST['generate_pdf'])) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    $pdf->Cell(200, 10, "Llista de Gestors i Clients", 0, 1, 'C');
    
    // Añadir gestores al PDF
    $pdf->Cell(50, 10, "Gestors", 0, 1);
    foreach ($gestors as $gestor) {
        $pdf->Cell(50, 10, "Usuari: " . $gestor['username'], 0, 1);
        $pdf->Cell(50, 10, "Correu: " . $gestor['email'], 0, 1);
        $pdf->Cell(50, 10, "Nom: " . $gestor['nom'] . ' ' . $gestor['cognoms'], 0, 1);
        $pdf->Cell(50, 10, "Telèfon: " . $gestor['telefon'], 0, 1);
    }

    // Añadir clientes al PDF
    $pdf->Cell(50, 10, "Clients", 0, 1);
    foreach ($clients as $client) {
        $pdf->Cell(50, 10, "Usuari: " . $client['username'], 0, 1);
        $pdf->Cell(50, 10, "Correu: " . $client['email'], 0, 1);
        $pdf->Cell(50, 10, "Nom: " . $client['nom'] . ' ' . $client['cognoms'], 0, 1);
        $pdf->Cell(50, 10, "Telèfon: " . $client['telefon'], 0, 1);
        $pdf->Cell(50, 10, "Adreça: " . $client['adreca'], 0, 1);
        $pdf->Cell(50, 10, "Visa: " . $client['visa'], 0, 1);
        $pdf->Cell(50, 10, "Gestor Assignat: " . $client['gestor'], 0, 1);
    }

    // Output del PDF
    $pdf->Output('D', 'llista_usuaris.pdf');
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell d'Administrador</title>
    <link rel="stylesheet" href="../css/dashboard_admin_css.css">

</head>
<body>
    <h1>Benvingut, <?php echo htmlspecialchars($_SESSION['username']); ?> (Administrador)</h1>

    <!-- Mostrar el administrador actual en la parte superior derecha -->
    <div style="text-align: right;">
        <h2>Administrador: <?php echo htmlspecialchars($admins[0]['username']); ?></h2>
        <form method="POST">
            <input type="hidden" name="admin_id" value="<?php echo $admins[0]['username']; ?>">
            <label>Nom d'usuari: <input type="text" name="username" value="<?php echo htmlspecialchars($admins[0]['username']); ?>" required></label>
            <label>Contrasenya: <input type="password" name="password" required></label>
            <label>Correu electrònic: <input type="email" name="email" value="<?php echo htmlspecialchars($admins[0]['email']); ?>" required></label>
            <button type="submit" name="edit_admin">Guardar canvis</button>
        </form>
    </div>

    <!-- Formulario de creación de un usuario (gestor o client) -->
    <h2>Crear Usuari</h2>
    <form method="POST">
        <label>Nom d'usuari: <input type="text" name="username" required></label>
        <label>Identificador Numeric: <input type="text" name="identificador" required></label>

        <label>Contrasenya: <input type="password" name="password" required></label>
        <label>Correu electrònic: <input type="email" name="email" required></label>
        <label>Nom: <input type="text" name="nom" required></label>
        <label>Cognoms: <input type="text" name="cognoms" required></label>
        <label>Telèfon: <input type="text" name="telefon" required></label>

        <!-- Campos específicos para el rol -->
        <label>Rol: 
            <select name="role" id="role" required onchange="toggleRoleFields()">
                <option value="gestor">Gestor</option>
                <option value="client">Client</option>
            </select>
        </label>

        <!-- Campos para Cliente -->
        <div id="clientFields" style="display: none;">
            <label>Adreça Postal: <input type="text" name="adreca" ></label>
            <label>Número de Visa: <input type="text" name="visa" ></label>
            <label>Gestor Assignat: 
                <select name="gestor" required>
                    <?php foreach ($gestors as $gestor): ?>
                        <option value="<?php echo $gestor['username']; ?>"><?php echo htmlspecialchars($gestor['username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <!-- Campos para Gestor -->
        <div id="gestorFields" style="display: none;">
            <!-- No additional fields needed for Gestor -->
        </div>

        <button type="submit" name="create_user">Crear Usuari</button>
    </form>

    <!-- Botón para generar PDF -->
    <form method="POST">
        <button type="submit" name="generate_pdf">Generar PDF</button>
    </form>

    <!-- Listado de Gestores -->
<!-- Listado de Gestores -->
<h2>Llista de Clients</h2>
<ul>
    <?php foreach ($clients as $client): ?>
        <li id="client-<?php echo htmlspecialchars($client['username']); ?>">
            <!-- Display Client Info -->
            <div class="client-info" id="client-info-<?php echo htmlspecialchars($client['username']); ?>">
                <?php echo htmlspecialchars($client['username']); ?> - <?php echo htmlspecialchars($client['email']); ?>
                Nom: <?php echo htmlspecialchars($client['nom']); ?> <?php echo htmlspecialchars($client['cognoms']); ?>
                Telèfon: <?php echo htmlspecialchars($client['telefon']); ?>
                Adreça: <?php echo htmlspecialchars($client['adreca']); ?>
                Visa: <?php echo htmlspecialchars($client['visa']); ?>
                Gestor: <?php echo htmlspecialchars($client['gestor']); ?>
                <!-- Edit Button -->
                <button type="button" onclick="editClient('<?php echo htmlspecialchars($client['username']); ?>')">Edita</button>
            </div>

            <!-- Edit Form for Client -->
            <div class="client-edit-form" id="client-edit-form-<?php echo htmlspecialchars($client['username']); ?>" style="display:none;">
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $client['username']; ?>">
                    <input type="hidden" name="admin_id" value="<?php echo $client['username']; ?>">

                    <label>Nom d'usuari: <input type="text" name="username" value="<?php echo htmlspecialchars($client['username']); ?>" required></label>
                    <label>Contrasenya: <input type="password" name="password" required></label>
                    
                    
                    <label>Correu electrònic: <input type="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required></label>
                    <label>Telèfon: <input type="text" name="telefon" value="<?php echo htmlspecialchars($client['telefon']); ?>" required></label>
                    <label>Adreça: <input type="text" name="adreca" value="<?php echo htmlspecialchars($client['adreca']); ?>" required></label>
                    <label>Visa: <input type="text" name="visa" value="<?php echo htmlspecialchars($client['visa']); ?>" required></label>
                    <label>Gestor: <input type="text" name="gestor" value="<?php echo htmlspecialchars($client['gestor']); ?>" required></label>

                    <button type="submit" name="edit_client">Guardar Canvis</button>
                </form>
            </div>

            <!-- Delete Form for Client -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $client['username']; ?>">
                <button type="submit" name="delete_user">Eliminar</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>

<h2>Llista de Gestors</h2>
<ul>
    <?php foreach ($gestors as $gestor): ?>
        <li id="gestor-<?php echo htmlspecialchars($gestor['username']); ?>">
            <!-- Display Gestor Info -->
            <div class="gestor-info" id="gestor-info-<?php echo htmlspecialchars($gestor['username']); ?>">
                <?php echo htmlspecialchars($gestor['username']); ?> - <?php echo htmlspecialchars($gestor['email']); ?>
                Nom: <?php echo htmlspecialchars($gestor['nom']); ?> <?php echo htmlspecialchars($gestor['cognoms']); ?>
                Telèfon: <?php echo htmlspecialchars($gestor['telefon']); ?>
                <!-- Edit Button -->
                <button type="button" onclick="editGestor('<?php echo htmlspecialchars($gestor['username']); ?>')">Edita</button>
            </div>

            <!-- Edit Form for Gestor -->
            <div class="gestor-edit-form" id="gestor-edit-form-<?php echo htmlspecialchars($gestor['username']); ?>" style="display:none;">
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $gestor['username']; ?>">
                    <input type="hidden" name="admin_id" value="<?php echo $gestor['username']; ?>">

                    <label>Nom d'usuari: <input type="text" name="username" value="<?php echo htmlspecialchars($gestor['username']); ?>" required></label>
                    <label>Contrasenya: <input type="password" name="password" required></label>
                    <label>Correu electrònic: <input type="email" name="email" value="<?php echo htmlspecialchars($gestor['email']); ?>" required></label>
                    <label>Telèfon: <input type="text" name="telefon" value="<?php echo htmlspecialchars($gestor['telefon']); ?>" required></label>

                    <button type="submit" name="edit_gestor">Guardar Canvis</button>
                </form>
            </div>

            <!-- Delete Form for Gestor -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $gestor['username']; ?>">
                <button type="submit" name="delete_user">Eliminar</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>

<script>
    function editClient(username) {
        // Hide current client info
        document.getElementById('client-info-' + username).style.display = 'none';
        // Show edit form
        document.getElementById('client-edit-form-' + username).style.display = 'block';
    }

    function editGestor(username) {
        // Hide current gestor info
        document.getElementById('gestor-info-' + username).style.display = 'none';
        // Show edit form
        document.getElementById('gestor-edit-form-' + username).style.display = 'block';
    }
</script>



    <p><a href="logout.php">Tancar Sessió</a></p>

    <script>
        function toggleRoleFields() {
            var role = document.getElementById("role").value;
            if (role === "client") {
                document.getElementById("clientFields").style.display = "block";
                document.getElementById("gestorFields").style.display = "none";
            } else {
                document.getElementById("clientFields").style.display = "none";
                document.getElementById("gestorFields").style.display = "none";
            }
        }
    </script>
</body>
</html>
