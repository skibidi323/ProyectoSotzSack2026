<?php
session_start();

$conn = pg_connect("host=localhost dbname=tienda user=postgres password=1234");

if(isset($_SESSION['clave'])){
    $clave = $_SESSION['correo'];

    // marcar inactivo
    pg_query($conn, "UPDATE usuarios SET activo=false WHERE clave='$clave'");
}

session_destroy();

header("Location: index.php");
// 🔄 Volver al inicio
header("Location: index.php");
exit();
?>