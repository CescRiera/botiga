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



function obtenirUsuari($userId) {
    $usuaris = carregarUsuaris();
    foreach ($usuaris as $usuari) {
        if ($usuari['username'] === $userId) {
            return $usuari;
        }
    }
    return null; // If the user is not found, return null
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
    // Comprobar si la contraseña cumple con los requisitos de seguridad
    if (!preg_match('/[A-Z]/', $dadesUsuari['password']) || !preg_match('/\d/', $dadesUsuari['password']) || !preg_match('/[^\w]/', $dadesUsuari['password'])) {
        echo "La contrasenya ha de tenir almenys una lletra majúscula, un número i un caràcter especial.";
        return;
    }

    $usuaris = carregarUsuaris(); // Cargar usuarios existentes

    // Comprobar si el nombre de usuario ya existe
    foreach ($usuaris as $usuari) {
        if ($usuari['username'] === $dadesUsuari['username']) {
            echo "Error: Ja existeix un usuari amb aquest nom d'usuari.";
            return;
        }
    }

    // Añadir el nuevo usuario
    $usuaris[] = $dadesUsuari;
    guardarUsuaris($usuaris); // Guardar la lista actualizada
}

function editarUsuari($username, $dadesActualitzades) {
    $usuaris = carregarUsuaris();

    // Buscar y actualizar los datos
    foreach ($usuaris as $key => $usuari) {
        if ($usuari['username'] === $username) {
            // Validar nueva contraseña
            if (!preg_match('/[A-Z]/', $dadesActualitzades['password']) || !preg_match('/\d/', $dadesActualitzades['password']) || !preg_match('/[^\w]/', $dadesActualitzades['password'])) {
                echo "La contrasenya ha de tenir almenys una lletra majúscula, un número i un caràcter especial.";
                return;
            }

            // Actualizar los datos del usuario
            $usuaris[$key]['username'] = $dadesActualitzades['username'];
            $usuaris[$key]['password'] = $dadesActualitzades['password']; // Encriptar la nueva contraseña
            $usuaris[$key]['email'] = $dadesActualitzades['email'];

            guardarUsuaris($usuaris); // Guardar la lista actualizada
            return;
        }
    }

    echo "Usuari no trobat!";
}

function editarClient($username, $dadesActualitzades) {
    // Carregar clients des del fitxer o base de dades
    $clients = carregarUsuaris(); // Load the clients data (you should create this function)

    // Buscar y actualizar los datos
    foreach ($clients as $key => $client) {
        if ($client['username'] === $username) {
            // Validar nueva contrasenya si está presente
            if (isset($dadesActualitzades['password']) && !empty($dadesActualitzades['password'])) {
                if (!preg_match('/[A-Z]/', $dadesActualitzades['password']) || 
                    !preg_match('/\d/', $dadesActualitzades['password']) || 
                    !preg_match('/[^\w]/', $dadesActualitzades['password'])) {
                    echo "La contrasenya ha de tenir almenys una lletra majúscula, un número i un caràcter especial.";
                    return;
                }
                // Actualizar contrasenya (encriptar)
                $dadesActualitzades['password'] = password_hash($dadesActualitzades['password'], PASSWORD_BCRYPT);
            } else {
                // Si no se proporciona nueva contraseña, mantenemos la original
                $dadesActualitzades['password'] = $client['password'];
            }

            // Actualitzar altres dades del client
            $clients[$key]['username'] = $dadesActualitzades['username'];
            $clients[$key]['password'] = $dadesActualitzades['password'];
            $clients[$key]['email'] = $dadesActualitzades['email'];

            $clients[$key]['telefon'] = $dadesActualitzades['telefon'];
            $clients[$key]['adreca'] = $dadesActualitzades['adreca']; // Address
            $clients[$key]['visa'] = $dadesActualitzades['visa']; // Visa number
            $clients[$key]['gestor'] = $dadesActualitzades['gestor']; // Gestor assigned

            // Guardar els canvis
            guardarUsuaris($clients); // Save the updated client list
            echo "Client actualitzat correctament!";
            return;
        }
    }

    echo "Client no trobat!";
}

function editarGestor($username, $dadesActualitzades) {
    // Carregar gestors des del fitxer o base de dades
    $gestors = carregarUsuaris(); // Load the gestor data (you should create this function)

    // Buscar y actualizar los datos
    foreach ($gestors as $key => $gestor) {
        if ($gestor['username'] === $username) {
            // Validar nova contrasenya si està present
            if (isset($dadesActualitzades['password']) && !empty($dadesActualitzades['password'])) {
                if (!preg_match('/[A-Z]/', $dadesActualitzades['password']) || 
                    !preg_match('/\d/', $dadesActualitzades['password']) || 
                    !preg_match('/[^\w]/', $dadesActualitzades['password'])) {
                    echo "La contrasenya ha de tenir almenys una lletra majúscula, un número i un caràcter especial.";
                    return;
                }
                // Actualitzar contrasenya (encriptar)
                $dadesActualitzades['password'] = password_hash($dadesActualitzades['password'], PASSWORD_BCRYPT);
            } else {
                // Si no se proporciona nueva contraseña, mantenemos la original
                $dadesActualitzades['password'] = $gestor['password'];
            }

            // Actualitzar altres dades del gestor
            $gestors[$key]['username'] = $dadesActualitzades['username'];
            $gestors[$key]['password'] = $dadesActualitzades['password'];
            $gestors[$key]['email'] = $dadesActualitzades['email'];
           
            $gestors[$key]['telefon'] = isset($dadesActualitzades['telefon']) ? $dadesActualitzades['telefon'] : $gestor['telefon']; // Optional

            // Guardar els canvis
            guardarUsuaris($gestors); // Save the updated gestor list
            echo "Gestor actualitzat correctament!";
            return;
        }
    }

    echo "Gestor no trobat!";
}



function obtenirUsuarisPerRol($role) {
    return array_filter(carregarUsuaris(), function($usuari) use ($role) {
        return $usuari['role'] === $role;
    });
}

function cargarComanda($username) {
    $filePath = __DIR__ . "/../../comandes/{$username}.json";
    
    if (!file_exists($filePath)) {
        // Informar que no existe el archivo y salir de la función
        echo "El usuario '{$username}' no tiene ninguna comanda activa.";
        return null; // Retornar null para indicar que no hay comanda
    }
    
    $comanda = json_decode(file_get_contents($filePath), true);

    if (!is_array($comanda)) {
        throw new Exception("El archivo de la comanda para el usuario '{$username}' tiene un formato inválido.");
    }

    return $comanda;
}


function guardarComanda($username, $comanda) {
    $filePath = __DIR__ . "/../../comandes/{$username}.json";
    file_put_contents($filePath, json_encode($comanda, JSON_PRETTY_PRINT));
}

function actualizarEstadoComanda($username, $idComanda, $clave) {
    // Cargar la comanda del usuario
    $comanda = cargarComanda($username);

    // Verificar si la ID coincide
    if (isset($comanda['id']) && (string)$comanda['id'] === (string)$idComanda) {
        // Verificar si la clave existe y está en False
        if (isset($comanda[$clave]) && $comanda[$clave] === false) {
            // Actualizar la clave a True
            $comanda[$clave] = true;
            guardarComanda($username, $comanda);
        } else {
            throw new Exception("La clave '{$clave}' ya está en True o no existe en la comanda.");
        }
    } else {
        throw new Exception("No se encontró una comanda con ID '{$idComanda}' para el usuario '{$username}'.");
    }
}

function obtenerComandaPorUsuario($username) {
    return cargarComanda($username);
}

function borrarComanda($username) {
    $filePath = __DIR__ . "/../../comandes/{$username}.json";

    // Verificar si el archivo existe
    if (file_exists($filePath)) {
        // Intentar borrar el archivo
        if (unlink($filePath)) {
            echo "La comanda del usuario '{$username}' ha sido eliminada correctamente.";
        } else {
            throw new Exception("No se pudo eliminar la comanda del usuario '{$username}'. Verifica los permisos del sistema.");
        }
    } else {
        echo "No se encontró ninguna comanda para el usuario '{$username}'.";
    }
}





?>
