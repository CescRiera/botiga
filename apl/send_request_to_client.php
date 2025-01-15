<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Asegúrate de que PHPMailer esté instalado con Composer
include 'includes/auth.php';


session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) {


    $username = $_POST['username'];
    $cestaId = $_POST['cesta_id'];
    $accion = $_POST['subject'];
    
    // Determinar la clave a actualizar según la acción
    $claveActualizar = null;
    if ($accion === 'Tramitar Comanda') {
        $claveActualizar = 'tramitada';
    } elseif ($accion === 'Finalitzar Comanda') {
        $claveActualizar = 'finalizada';
    }

    // Si hay una clave que actualizar
    if ($claveActualizar) {
        // Llamar a la función para actualizar el estado de la cesta
        echo($username);
        echo($cestaId);

        echo($claveActualizar);

        actualizarEstadoCesta($username, $cestaId, $claveActualizar);
    }
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
        $mail->Body = "<p><strong>Missatge del Gestor:</strong> $gestor amb correu $gestorcorreu</p> <p>$message</p>";
        $mail->AltBody = "Missatge de: $gestor\n\n$message";

        // Enviar el correo
        $mail->send();
        echo "<p style='color: green;'>El correu s'ha enviat correctament.</p>";

        // Enviar el mensaje de Telegram
        $telegramBotToken = '7930893797:AAEp4VVaSDKozxvXcuq51uRXn8q9cmvItZo';  // Tu token de bot
        $telegramChatId = '6477112490';  // Tu chat ID
        $telegramMessage = "Missatge del Gestor: $gestor amb correu $gestorcorreu\n\n$message";

        $telegramUrl = "https://api.telegram.org/bot$telegramBotToken/sendMessage?chat_id=$telegramChatId&text=" . urlencode($telegramMessage);
        
        // Realizar la solicitud para enviar el mensaje
        file_get_contents($telegramUrl);  // Puedes usar cURL si prefieres

        // Redirigir al panel de gestión
        header("Location: dashboard_gestor.php");
        exit();
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error en enviar el correu. Detalls: {$mail->ErrorInfo}</p>";
    }
} else {
    header("Location: panell_gestor.php"); // Redirigir si se accede de manera incorrecta
    exit();
}
?>
