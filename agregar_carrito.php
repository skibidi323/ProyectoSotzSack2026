<?php

session_start();

header('Content-Type: application/json');

$conn = pg_connect(
    "host=localhost dbname=tienda user=postgres password=1234"
);

if (!$conn) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo conectar a la base de datos"
    ]);
    exit;
}


/* =====================================
   VERIFICAR SESIÓN
===================================== */

if (!isset($_SESSION['correo'])) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Debes iniciar sesión para agregar productos al carrito"
    ]);

    exit;
}


$correo = $_SESSION['correo'];


/* =====================================
   RECIBIR PRODUCTO
===================================== */

$datos = json_decode(
    file_get_contents("php://input"),
    true
);

$producto_id = isset($datos['producto_id'])
    ? intval($datos['producto_id'])
    : 0;


if ($producto_id <= 0) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Producto inválido"
    ]);

    exit;
}


/* =====================================
   OBTENER USUARIO
===================================== */

$query = "
SELECT id
FROM usuarios
WHERE correo = $1
";

$result = pg_query_params(
    $conn,
    $query,
    [$correo]
);


$usuario = pg_fetch_assoc($result);


if (!$usuario) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Usuario no encontrado"
    ]);

    exit;
}


$usuario_id = intval($usuario['id']);


/* =====================================
   VERIFICAR PRODUCTO Y STOCK
===================================== */

$query = "
SELECT id, nombre, precio, stock
FROM productos
WHERE id = $1
";

$result = pg_query_params(
    $conn,
    $query,
    [$producto_id]
);


$producto = pg_fetch_assoc($result);


if (!$producto) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "El producto no existe"
    ]);

    exit;
}


$stock = intval($producto['stock']);


if ($stock <= 0) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "No hay stock disponible"
    ]);

    exit;
}


/* =====================================
   BUSCAR CARRITO DEL USUARIO
===================================== */

$query = "
SELECT id
FROM carrito
WHERE usuario = $1
ORDER BY id DESC
LIMIT 1
";

$result = pg_query_params(
    $conn,
    $query,
    [$usuario_id]
);


$carrito = pg_fetch_assoc($result);


/* =====================================
   CREAR CARRITO SI NO EXISTE
===================================== */

if (!$carrito) {

    $query = "
    INSERT INTO carrito
    (usuario, fecha, hora)

    VALUES
    ($1, CURRENT_DATE, CURRENT_TIME)

    RETURNING id
    ";

    $result = pg_query_params(
        $conn,
        $query,
        [$usuario_id]
    );


    $carrito = pg_fetch_assoc($result);
}


$carrito_id = intval($carrito['id']);


/* =====================================
   BUSCAR SI EL PRODUCTO YA ESTÁ
===================================== */

$query = "
SELECT cantidad
FROM item
WHERE carrito_id = $1
AND producto_id = $2
";

$result = pg_query_params(
    $conn,
    $query,
    [
        $carrito_id,
        $producto_id
    ]
);


$item = pg_fetch_assoc($result);


/* =====================================
   SI YA EXISTE, AUMENTAR CANTIDAD
===================================== */

if ($item) {

    $cantidad_actual =
        intval($item['cantidad']);


    if ($cantidad_actual >= $stock) {

        echo json_encode([
            "ok" => false,
            "mensaje" => "No hay más stock disponible"
        ]);

        exit;
    }


    $nueva_cantidad =
        $cantidad_actual + 1;


    $query = "
    UPDATE item

    SET cantidad = $1

    WHERE carrito_id = $2
    AND producto_id = $3
    ";


    pg_query_params(
        $conn,
        $query,
        [
            $nueva_cantidad,
            $carrito_id,
            $producto_id
        ]
    );


} else {


    /* =====================================
       CREAR NUEVO ITEM
    ===================================== */

    $query = "
    INSERT INTO item
    (carrito_id, producto_id, cantidad)

    VALUES
    ($1, $2, 1)
    ";


    pg_query_params(
        $conn,
        $query,
        [
            $carrito_id,
            $producto_id
        ]
    );

}


/* =====================================
   RESPUESTA
===================================== */

echo json_encode([

    "ok" => true,

    "mensaje" =>
        "Producto agregado al carrito"

]);

?>