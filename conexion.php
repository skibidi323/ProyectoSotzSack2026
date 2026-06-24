<?php
session_start();

// 🔌 Conexión
$conn = pg_connect("host=localhost dbname=tienda user=postgres password=1234");

// 📥 Datos
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$dni = $_POST['dni'];
$telefono = $_POST['telefono'];
$correo = $_POST['correo'];
$localidad = $_POST['localidad'];
$direccion = $_POST['direccion'];

// 🔐 Encriptar contraseña
$clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);

// 📝 Insertar
$query = "INSERT INTO usuarios 
(nombre, apellido, dni, telefono, correo, localidad, direccion, clave, activo)
VALUES 
('$nombre', '$apellido', '$dni', '$telefono', '$correo', '$localidad', '$direccion', '$clave', true)";

$result = pg_query($conn, $query);

// ✅ Verificar
if($result){
    $_SESSION['usuario'] = $nombre;
    $_SESSION['correo'] = $correo;

    header("Location: index.php");
    exit();
} else {
    echo "Error al registrar";
}
?>