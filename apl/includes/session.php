<?php
function iniciarSessio($usuari) {
    $_SESSION['username'] = $usuari['username'];
    $_SESSION['role'] = $usuari['role'];
}

function tancarSessio() {
    session_unset();  // Unset all session variables
    session_destroy(); // Destroy the session data
    // Redirect to index.php after logging out
    header("Location: index.php");
    exit(); // Make sure no further code is executed after the redirection
}
?>
