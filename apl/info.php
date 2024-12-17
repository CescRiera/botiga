<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Informació - Botiga Virtual</title>
</head>
<body>
    <h1>Com utilitzar l'aplicació</h1>
    <p>
        Per accedir a l'aplicació, utilitzeu el formulari de login a la pàgina d'accés.<br>
        Un cop dins, les opcions disponibles dependran del vostre rol d'usuari:
    </p>
    <ul>
        <li>Administrador: pot gestionar usuaris i accedir a les funcionalitats de gestió.</li>
        <li>Gestor: pot visualitzar els clients i enviar peticions a l'administrador.</li>
        <li>Client: accés limitat a la botiga.</li>
    </ul>
    <p><a href="index.php">Tornar a l'inici</a></p>
</body>
</html>
