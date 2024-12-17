<?php
function carregarUsuaris() {
    return json_decode(file_get_contents(__DIR__ . '/../../usuaris.json'), true);
}

function guardarUsuaris($usuaris) {
    file_put_contents(__DIR__ . '/../../usuaris.json', json_encode($usuaris, JSON_PRETTY_PRINT));
}

function validarUsuari($username, $password) {
    $usuaris = carregarUsuaris();

    foreach ($usuaris as $usuari) {
        if ($usuari['username'] === $username && password_verify($password, $usuari['password'])) {
            return $usuari; // Usuari vàlid
        }
    }
    return false; // No trobat
}

function afegirGestor($dadesGestor) {
    $usuaris = carregarUsuaris();
    $usuaris[] = $dadesGestor;
    guardarUsuaris($usuaris);
}

function obtenirUsuarisPerRol($role) {
    return array_filter(carregarUsuaris(), function($usuari) use ($role) {
        return $usuari['role'] === $role;
    });
}
?>
