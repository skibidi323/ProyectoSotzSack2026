<?php
session_start();

$conn = pg_connect("host=localhost dbname=tienda user=postgres password=1234");

if (!$conn) {
    die("Error de conexión con la base de datos.");
}

// Comprobar que el usuario haya iniciado sesión
if (!isset($_SESSION["correo"])) {
    header("Location: index.php");
    exit;
}

$correo = $_SESSION["correo"];

// Buscar el ID del usuario
$res = pg_query_params(
    $conn,
    "SELECT id FROM usuarios WHERE correo = $1",
    [$correo]
);

$user = pg_fetch_assoc($res);

if (!$user) {
    die("No se encontró el usuario.");
}

$idUsuario = $user["id"];

// Buscar las compras del usuario
$compras = pg_query_params(
    $conn,
    "SELECT *
     FROM compras
     WHERE usuario_id = $1
     ORDER BY fecha DESC",
    [$idUsuario]
);

if (!$compras) {
    die("Error al consultar las compras.");
}

// Mostrar cada compra
while ($c = pg_fetch_assoc($compras)) {

    echo "<div style='background:white;padding:15px;border-radius:10px;margin:20px;'>";

    echo "<h3>Compra #" . htmlspecialchars($c["id"]) . "</h3>";

    echo "<b>Fecha:</b> " . htmlspecialchars($c["fecha"]) . "<br><br>";

    // Buscar los productos de esta compra
    $detalle = pg_query_params(
        $conn,
        "SELECT
            detalle_compra.nombre_producto,
            detalle_compra.cantidad,
            detalle_compra.precio
         FROM detalle_compra
         WHERE detalle_compra.compra_id = $1",
        [$c["id"]]
    );

    if (!$detalle) {
        echo "Error al consultar los productos de la compra.";
    } else {

        // Mostrar cada producto
        while ($d = pg_fetch_assoc($detalle)) {

            echo htmlspecialchars($d["nombre_producto"])
                . " x"
                . (int)$d["cantidad"];

            $subtotal = (float)$d["precio"] * (int)$d["cantidad"];

            echo " - $" . number_format($subtotal, 0, ',', '.');

            echo "<br>";
        }
    }

    // Mostrar el total de la compra
    echo "<br><b>Total: $"
        . number_format((float)$c["total"], 0, ',', '.')
        . "</b>";

    echo "</div>";
}

pg_close($conn);
?>