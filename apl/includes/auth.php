<?php
function carregarUsuaris() {
    return json_decode(file_get_contents(__DIR__ . '/../../usuaris.json'), true);
}

function guardarUsuaris($usuaris) {
    file_put_contents(__DIR__ . '/../../usuaris.json', json_encode($usuaris, JSON_PRETTY_PRINT));
}

function eliminarUsuari($username) {
    // Load the users from the JSON file
    $usuaris = carregarUsuaris();
    
    // Search for the user by username
    foreach ($usuaris as $key => $usuari) {
        if ($usuari['username'] === $username) {
            // Remove the user from the array
            unset($usuaris[$key]);
            
            // Reindex the array to avoid potential issues with array keys
            $usuaris = array_values($usuaris);
            
            // Save the updated user list back to the file
            guardarUsuaris($usuaris);
            return; // Exit the function after removing the user
        }
    }
    
    // If no user with that username is found, you can optionally handle it here (e.g., show an error message)
    echo "Usuari no trobat!";
}

function validarUsuari($username, $password) {
    $usuaris = carregarUsuaris();
    echo password_hash('FjeClot2425#', PASSWORD_BCRYPT);

    foreach ($usuaris as $usuari) {
        echo "Entered password: " . htmlspecialchars($password) . "<br>";
        echo "Stored password hash: " . htmlspecialchars($usuari["password"]) . "<br>";

        if ($usuari['username'] === $username && password_verify($password, $usuari["password"])) {
            echo("yeah perdone");
            return $usuari; // Usuari vàlid
        }
    }
    return false; // No trobat
}


function afegirUsuari($dadesUsuari) {
    $usuaris = carregarUsuaris(); // Load existing users

    // Check if the username already exists
    foreach ($usuaris as $usuari) {
        if ($usuari['username'] === $dadesUsuari['username']) {
            // If the username already exists, return an error or handle the duplication
            echo "Error: Ja existeix un usuari amb aquest nom d'usuari.";
            return; // Exit the function if the username is found
        }
    }

    // If the username is unique, add the new user
    $usuaris[] = $dadesUsuari;

    // Save the updated list of users to usuaris.json
    guardarUsuaris($usuaris);
}

function obtenirUsuarisPerRol($role) {
    return array_filter(carregarUsuaris(), function($usuari) use ($role) {
        return $usuari['role'] === $role;
    });
}
?>
