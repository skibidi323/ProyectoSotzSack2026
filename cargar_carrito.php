<?php

session_start();

header('Content-Type: application/json');

$conn = pg_connect(
    "host=localhost dbname=tienda user=postgres password=1234"
);


if (!isset($_SESSION['correo'])) {

    echo json_encode([]);

    exit;
}


$correo = $_SESSION['correo'];


/* =====================================
   OBTENER USUARIO
===================================== */

$result = pg_query_params(

    $conn,

    "SELECT id FROM usuarios WHERE correo = $1",

    [$correo]

);


$usuario = pg_fetch_assoc($result);


if (!$usuario) {

    echo json_encode([]);

    exit;
}


$usuario_id = intval($usuario['id']);


/* =====================================
   OBTENER CARRITO
===================================== */

$query = "

SELECT

    i.id AS item_id,

    i.producto_id,

    i.cantidad,

    p.nombre,

    p.precio,

    p.stock

FROM item i

INNER JOIN carrito c
    ON c.id = i.carrito_id

INNER JOIN productos p
    ON p.id = i.producto_id

WHERE c.usuario = $1

ORDER BY i.id

";


$result = pg_query_params(

    $conn,

    $query,

    [$usuario_id]

);


$carrito = [];


while ($row = pg_fetch_assoc($result)) {

    $carrito[] = [

        "item_id" =>
            intval($row['item_id']),

        "id" =>
            intval($row['producto_id']),

        "nombre" =>
            $row['nombre'],

        "precio" =>
            floatval($row['precio']),

        "cantidad" =>
            intval($row['cantidad']),

        "stock" =>
            intval($row['stock'])

    ];

}


echo json_encode($carrito);

?>