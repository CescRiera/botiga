<?php
session_start();
include 'includes/session.php';

// Tancar la sessió
tancarSessio(); // Just call the function, no need for extra redirection

// After tancarSessio(), it will automatically redirect to index.php
?>
