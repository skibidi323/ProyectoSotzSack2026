<?php
session_start();

// 🔌 Conexión
$conn = pg_connect("host=localhost dbname=tienda user=postgres password=1234");

// 📥 Datos del formulario
$correo = $_POST['correo'];
$password = $_POST['password'];

// 🔍 Buscar usuario
$query = "SELECT * FROM usuarios WHERE correo='$correo'";
$result = pg_query($conn, $query);

if(pg_num_rows($result) > 0){

    $user = pg_fetch_assoc($result);

    // 🔐 Verifica contraseña
    if(password_verify($password, $user['clave'])){
        
        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['correo'] = $correo;

        // 🟢 Marcar activo
        pg_query($conn, "UPDATE usuarios SET activo=true WHERE correo='$correo'");

        header("Location: index.php");
        exit();

    } else {
        echo "Contraseña incorrecta";
    }

} else {
    echo "Usuario no encontrado";
}
?>