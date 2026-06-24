<?php
session_start();

$conn = pg_connect("host=localhost dbname=tienda user=postgres password=1234");

$correo = $_SESSION['correo'];

$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$dni = $_POST['dni'];
$telefono = $_POST['telefono'];
$localidad = $_POST['localidad'];
$direccion = $_POST['direccion'];

$query = "UPDATE usuarios SET
nombre='$nombre',
apellido='$apellido',
dni='$dni',
telefono='$telefono',
localidad='$localidad',
direccion='$direccion'
WHERE correo='$correo'";

$result = pg_query($conn, $query);

if($result){
    $_SESSION['usuario'] = $nombre;
    header("Location: index.php");
    exit();
} else {
    echo "Error al actualizar";
}
?>