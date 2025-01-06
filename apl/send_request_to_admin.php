<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Asegúrate de que PHPMailer esté instalado con Composer

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) {
    // Validar y sanitizar los datos del formulario
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    $gestor = isset($_SESSION['username']) ? $_SESSION['username'] : 'Gestor desconegut';
    $gestorcorreu = isset($_SESSION['email']) ? $_SESSION['email'] : 'Correu de gestor desconegut';


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
        $mail->Body = "<p><strong>Missatge de:</strong> $gestor amb correu $gestorcorreu</p> <p>$message</p>";
        $mail->AltBody = "Missatge de: $gestor\n\n$message";

        // Enviar el correo
        $mail->send();
        echo "<p style='color: green;'>El correu s'ha enviat correctament.</p>";
        header("Location: dashboard_gestor.php"); // Redirigir si se accede de manera incorrecta
    exit();
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error en enviar el correu. Detalls: {$mail->ErrorInfo}</p>";
    }
} else {
    echo("Location: panell_gestor.php"); // Redirigir si se accede de manera incorrecta
    exit();
}
?>
