<?php

session_start();

header('Content-Type: application/json');

$conn = pg_connect(
    "host=localhost dbname=tienda user=postgres password=1234"
);

if (!$conn) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error de conexión con la base de datos"
    ]);

    exit;
}


if (!isset($_SESSION['correo'])) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Debes iniciar sesión"
    ]);

    exit;
}


$correo = $_SESSION['correo'];


// Buscar usuario
$queryUsuario = "
    SELECT id
    FROM usuarios
    WHERE correo = $1
";

$resultUsuario = pg_query_params(
    $conn,
    $queryUsuario,
    [$correo]
);

$usuario = pg_fetch_assoc($resultUsuario);


if (!$usuario) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Usuario no encontrado"
    ]);

    exit;
}


$usuario_id = $usuario['id'];


// Buscar carrito del usuario
$queryCarrito = "
    SELECT id
    FROM carrito
    WHERE usuario = $1
";

$resultCarrito = pg_query_params(
    $conn,
    $queryCarrito,
    [$usuario_id]
);

$carrito = pg_fetch_assoc($resultCarrito);


if (!$carrito) {

    echo json_encode([
        "ok" => true,
        "mensaje" => "El carrito ya estaba vacío"
    ]);

    exit;
}


$carrito_id = $carrito['id'];


// Eliminar los items del carrito
$queryEliminar = "
    DELETE FROM item
    WHERE carrito_id = $1
";

$resultEliminar = pg_query_params(
    $conn,
    $queryEliminar,
    [$carrito_id]
);


if ($resultEliminar) {

    echo json_encode([
        "ok" => true,
        "mensaje" => "Carrito vaciado correctamente"
    ]);

} else {

    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo vaciar el carrito"
    ]);

}

?>