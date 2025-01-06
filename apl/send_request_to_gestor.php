<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Asegúrate de que PHPMailer esté instalado con Composer
include 'includes/auth.php';

session_start();

// Get all gestors and clients
$gestors = obtenirUsuarisPerRol('gestor');
$clients = obtenirUsuarisPerRol('client');

// Get the current client's username from the session
$currentClientUsername = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Find the current client's information
$currentClient = array_filter($clients, function($client) use ($currentClientUsername) {
    return $client['username'] === $currentClientUsername;
});

// Retrieve the current client's details
if (!empty($currentClient)) {
    $currentClientDetails = reset($currentClient); // Get the first (and only) match

    // Get the gestor's username from the current client's details
    $gestorUsername = $currentClientDetails['gestor'];

    // Find the gestor's information using their username
    $gestor = array_filter($gestors, function($gestor) use ($gestorUsername) {
        return $gestor['username'] === $gestorUsername;
    });

    // If the gestor exists, retrieve their email
    if (!empty($gestor)) {
        $gestorDetails = reset($gestor); // Get the first (and only) match
        $gestorEmail = $gestorDetails['email'];
        echo "The email of the gestor is: $gestorEmail";
    } else {
        echo "No gestor found for username: $gestorUsername";
    }
} else {
    echo "No client found for username: $currentClientUsername";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) {
    // Validar y sanitizar los datos del formulario
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    $gestor = isset($_SESSION['username']) ? $_SESSION['username'] : 'Gestor desconegut';
    $gestorcorreu = $gestorEmail;


    // Configurar PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambiar según el proveedor de correo
        $mail->SMTPAuth = true;
        $mail->Username = 'crieraamatriain@gmail.com'; // Tu correo
        $mail->Password = 'jqiuvwhezslokcia'; // Tu contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('crieraamatriain@gmail.com', 'Sistema de Gestió');
        $mail->addAddress('crieraamatriain@gmail.com', 'Administrador'); // Correo del administrador

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "<p><strong>Missatge del client :</strong> $gestor amb correu " . htmlspecialchars($currentClientDetails['email']) . ", per al gestor ". htmlspecialchars($gestorDetails['username']) . " amb correu: " . htmlspecialchars($gestorDetails['email']) . "</p> <p>" . htmlspecialchars($message) . "</p>";
        $mail->AltBody = "Missatge de: $gestor\n\n$message";

        // Enviar el correo
        $mail->send();
        echo "<p style='color: green;'>El correu s'ha enviat correctament.</p>";
        header("Location: dashboard_client.php"); // Redirigir si se accede de manera incorrecta
    exit();
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error en enviar el correu. Detalls: {$mail->ErrorInfo}</p>";
    }
} else {
    echo("Location: panell_gestor.php"); // Redirigir si se accede de manera incorrecta
    exit();
}
?>
