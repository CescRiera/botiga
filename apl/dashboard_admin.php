<?php
session_start();
include 'includes/session.php';
include 'includes/auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Obtener gestores y clientes
$gestors = obtenirUsuarisPerRol('gestor');
$clients = obtenirUsuarisPerRol('client');

// Manejar creación de un nuevo usuario (gestor o cliente)
if (isset($_POST['create_user'])) {
    $role = $_POST['role']; // Get the role (gestor or client)
    $newUser = [
        'username' => $_POST['username'],
        'password' => password_hash($_POST['password'], PASSWORD_BCRYPT), // Use a comma instead of a semicolon
        'role' => $role,
        'email' => $_POST['email']
    ];
    afegirUsuari($newUser); // Add the new user to usuaris.json
}

// Manejar eliminación de usuario
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    eliminarUsuari($userId);
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell d'Administrador</title>
</head>
<body>
    <h1>Benvingut, <?php echo htmlspecialchars($_SESSION['username']); ?> (Administrador)</h1>

    <!-- Formulario de creación de un usuario (gestor o client) -->
    <h2>Crear Usuari</h2>
    <form method="POST">
        <label>Nom d'usuari: <input type="text" name="username" required></label><br>
        <label>Contrasenya: <input type="password" name="password" required></label><br>
        <label>Correu electrònic: <input type="email" name="email" required></label><br>
        
        <label>Rol: 
            <select name="role" required>
                <option value="gestor">Gestor</option>
                <option value="client">Client</option>
            </select>
        </label><br>

        <button type="submit" name="create_user">Crear Usuari</button>
    </form>

    <!-- Listado de Gestores -->
    <h2>Llista de Gestors</h2>
    <ul>
        <?php foreach ($gestors as $gestor): ?>
            <li>
                <?php echo htmlspecialchars($gestor['username']); ?> - <?php echo htmlspecialchars($gestor['email']); ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?php echo $gestor['username']; ?>">
                    <button type="submit" name="delete_user">Eliminar</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Listado de Clientes -->
    <h2>Llista de Clients</h2>
    <ul>
        <?php foreach ($clients as $client): ?>
            <li>
                <?php echo htmlspecialchars($client['username']); ?> - <?php echo htmlspecialchars($client['email']); ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?php echo $client['username']; ?>">
                    <button type="submit" name="delete_user">Eliminar</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <p><a href="logout.php">Tancar Sessió</a></p>
</body>
</html>
