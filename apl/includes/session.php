<?php
function iniciarSessio($usuari) {
    $_SESSION['username'] = $usuari['username'];
    $_SESSION['role'] = $usuari['role'];
}

function tancarSessio() {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
