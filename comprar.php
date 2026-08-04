<?php
session_start();

$conn = pg_connect("host=localhost dbname=tienda user=postgres password=1234");

if(!isset($_SESSION["correo"])){
    die("Debe iniciar sesión");
}

$correo=$_SESSION["correo"];

$res=pg_query($conn,"SELECT id FROM usuarios WHERE correo='$correo'");
$usuario=pg_fetch_assoc($res);

$usuario_id=$usuario["id"];

$datos=json_decode(file_get_contents("php://input"),true);

$total=0;

foreach($datos as $p){
    $total+=$p["precio"]*$p["cantidad"];
}

$res=pg_query($conn,"
INSERT INTO compras(usuario_id,total)
VALUES($usuario_id,$total)
RETURNING id
");

$compra=pg_fetch_assoc($res);

$compra_id=$compra["id"];

foreach($datos as $p){

    pg_query($conn,"
    INSERT INTO detalle_compra
    (compra_id,producto_id,cantidad,precio)

    VALUES(
    $compra_id,
    {$p["id"]},
    {$p["cantidad"]},
    {$p["precio"]}
    )
    ");

    pg_query($conn,"
    UPDATE productos
    SET stock=stock-{$p["cantidad"]}
    WHERE id={$p["id"]}
    ");
}

echo "ok";