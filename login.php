<?php

session_start();


// ==========================================
// 🔌 CONEXIÓN
// ==========================================

$conn = pg_connect(
    "host=localhost dbname=tienda user=postgres password=1234"
);

if (!$conn) {
    die("Error de conexión con la base de datos");
}


// ==========================================
// 📥 DATOS DEL FORMULARIO
// ==========================================

$correo = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');


// ==========================================
// 👑 COMPROBAR ADMINISTRADOR
// ==========================================

$correoAdmin = "garciadarosae04@gmail.com";
$passwordAdmin = "Enzo 040707";


if (
    strtolower($correo) === strtolower($correoAdmin) &&
    $password === $passwordAdmin
) {

    // Crear sesión de administrador

    $_SESSION['usuario'] = "Administrador";
    $_SESSION['correo'] = $correoAdmin;
    $_SESSION['admin'] = true;


    // Ir al panel

    header("Location: admin.php");
    exit();

}


// ==========================================
// 👤 USUARIO NORMAL
// ==========================================

$query = "
    SELECT *
    FROM usuarios
    WHERE correo='$correo'
";

$result = pg_query($conn, $query);


if (!$result) {

    die(
        "Error al consultar el usuario: " .
        pg_last_error($conn)
    );

}


if (pg_num_rows($result) > 0) {

    $user = pg_fetch_assoc($result);


    // ======================================
    // 🔐 VERIFICAR CONTRASEÑA
    // ======================================

    if (password_verify($password, $user['clave'])) {


        // ==================================
        // SESIÓN USUARIO NORMAL
        // ==================================

        $_SESSION['usuario'] = $user['nombre'];

        $_SESSION['correo'] = $correo;

        $_SESSION['admin'] = false;


        // ==================================
        // 🟢 MARCAR ACTIVO
        // ==================================

        pg_query(
            $conn,
            "UPDATE usuarios
             SET activo=true
             WHERE correo='$correo'"
        );


        // ==================================
        // 🏠 IR A LA TIENDA
        // ==================================

        header("Location: index.php");
        exit();


    } else {

        echo "Contraseña incorrecta";

    }


} else {

    echo "Usuario no encontrado";

}

?>