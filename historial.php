<?php
session_start();

$conn=pg_connect("host=localhost dbname=tienda user=postgres password=1234");

$correo=$_SESSION["correo"];

$res=pg_query($conn,"SELECT id FROM usuarios WHERE correo='$correo'");
$user=pg_fetch_assoc($res);

$idUsuario=$user["id"];

$compras=pg_query($conn,"
SELECT *
FROM compras
WHERE usuario_id=$idUsuario
ORDER BY fecha DESC
");

while($c=pg_fetch_assoc($compras)){

echo "<div style='background:white;padding:15px;border-radius:10px;margin:20px;'>";

echo "<h3>Compra #".$c["id"]."</h3>";

echo "<b>Fecha:</b> ".$c["fecha"]."<br><br>";

$detalle=pg_query($conn,"
SELECT
productos.nombre,
detalle_compra.cantidad,
detalle_compra.precio

FROM detalle_compra

JOIN productos

ON productos.id=detalle_compra.producto_id

WHERE compra_id=".$c["id"]);

while($d=pg_fetch_assoc($detalle)){

echo $d["nombre"]." x".$d["cantidad"];

echo " - $".$d["precio"]*$d["cantidad"];

echo "<br>";

}

echo "<br><b>Total: $".$c["total"]."</b>";

echo "</div>";

}
?>