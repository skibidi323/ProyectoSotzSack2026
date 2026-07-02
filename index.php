<!DOCTYPE html>
<?php session_start(); ?>
<?php
$usuarioData = null;

if(isset($_SESSION['correo'])){
    $conn = pg_connect("host=localhost dbname=tienda user=postgres password=1234");

    $correo = $_SESSION['correo'];
    $query = "SELECT * FROM usuarios WHERE correo='$correo'";
    $result = pg_query($conn, $query);

    $usuarioData = pg_fetch_assoc($result);
}
?>
<html lang="es">
<head>
<link rel="stylesheet" href="css/style.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tienda</title>

<style>

/*************************************
 * 🎨 ESTILOS GENERALES
 *************************************/
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
   background-color: #E1E8ED;
    color: #333; /* Texto gris oscuro para mejor lectura */
}

/*************************************
 * 🔝 HEADER (ENCABEZADO)
 *************************************/
header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 30px;
    border-bottom: 2px solid black;
}

/*************************************
 * 🛍️ PRODUCTOS
 *************************************/
.products{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
    padding:20px;
}

/* 📦 Tarjeta de producto */
.product{
    background:white;
    padding:10px;
    border-radius:10px;
    text-align:center;
}

/*************************************
 * 🔻 FOOTER
 *************************************/
footer{
    background:black;
    color:white;
    text-align:center;
    padding:20px;
}

/*************************************
 * 🪟 MODAL (VENTANA EMERGENTE)
 *************************************/
.modal{
    display:none;
    position:fixed;
    width:100%;
    height:100%;
    top:0;
    left:0;
    background:rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
}

.modal-content{
    background:white;
    padding:20px;
    border-radius:10px;
}
.cart-box{
    background: #fff;
    width: 400px;
    height: 100%;
    position: fixed;
    right: 0;
    top: 0;
    padding: 20px;
    overflow-y: auto;
    box-shadow: -5px 0 10px rgba(0,0,0,0.2);
    border-radius: 10px 0 0 10px;
}

.cart-item{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:15px;
    border-bottom:1px solid #ddd;
    padding-bottom:10px;
}

.cart-item img{
    width:60px;
    border-radius:5px;
}

.btn-close{
    margin-bottom:10px;
}
.cart-right input{
    width:100%;
    padding:10px;
    margin:8px 0;
    border-radius:8px;
    border:1px solid #ccc;
}

.btn-comprar{
    width:100%;
    padding:12px;
    background:#1e90ff;
    color:white;
    border:none;
    border-radius:8px;
    font-size:16px;
    cursor:pointer;
}

.btn-comprar:hover{
    background:#187bcd;
}

</style>
</head>

<body>

    <div class="modal" id="cartModal">

    <!-- 🔥 ESTE ES EL CONTENEDOR NUEVO -->
    <div class="cart-container">

        <!-- 🛒 CARRITO -->
        
            <button class="btn-close" onclick="closeCart()">← Continuar compra</button>
            <div id="cartItems"></div>
        <!-- 💳 FORMULARIO -->
        <div class="cart-right">

            <h3>Datos de pago</h3>

            <input type="text" placeholder="Nombre del titular" required>

            <input type="text" placeholder="Número de tarjeta" maxlength="16" required>

            <div style="display:flex; gap:10px;">
                <input type="text" placeholder="MM/AA" style="width:50%;" required>
                <input type="text" placeholder="CVV" style="width:50%;" required>
            </div>

            <h2 id="totalCarrito">Total: $0</h2>

            <button class="btn-comprar" onclick="comprar()">Comprar</button>

        </div>

    </div> <!-- 🔚 FIN cart-container -->

</div>


<!-- 🔝 ENCABEZADO -->
<header>
    <!-- 🖼️ Logo -->
    <img src="main-logo2.png" width="200" >

    <!-- 🔐 Botón login -->
    <div class="icons" style="display: flex; gap: 15px;"> <button class="btn-login" onclick="openCart()">
    <span class="icon">🛒</span>
    Carrito
</button>
     <?php if(isset($_SESSION['usuario'])): ?>

<div class="user-menu">
    <button class="btn-login" onclick="toggleMenu()">
        👤 <?php echo $_SESSION['usuario']; ?>
    </button>

    <!-- 🔽 Menú desplegable -->
    <div class="dropdown" id="dropdownMenu">
        <a href="#" onclick="editarPerfil()">Editar perfil</a>
        <a href="logout.php">Cerrar sesión</a>
    </div>
</div>

<?php else: ?>

<button class="btn-login" onclick="openLogin()">
    <span class="icon">
        <img src="candado.png" width="20">
    </span>
    Registrarse
</button>

<?php endif; ?>
    </div>
</header>

<!-- 🛍️ SECCIÓN DE PRODUCTOS -->
<section class="products">

<?php
// 🔌 Conexión a la base de datos
$conn = pg_connect("host=localhost dbname=tienda user=postgres password=1234");

// 📦 Consulta de productos
$query = "SELECT * FROM productos";
$result = pg_query($conn, $query);

// 🔁 Mostrar productos
while ($row = pg_fetch_assoc($result)) {
    echo '
    <div class="product">

        <!-- 🖼️ Imagen -->
       <img src="'.$row['imagen'].'" onerror="this.style.display=\"none\"">

        <!-- 📛 Nombre -->
        <p>'.$row['nombre'].'</p>

        <!-- 💲 Precio -->
        <p>$'.$row['precio'].'</p>

        <!-- 📦 Stock -->
        <p id="stock-'.$row['id'].'">Stock: '.$row['stock'].'</p>

        <!-- 🛒 Botón agregar -->
        <button onclick="agregarAlCarrito('.$row['id'].', \''.$row['nombre'].'\', '.$row['precio'].', '.$row['stock'].')">
            Agregar al carrito
        </button>

    </div>';
}
?>

</section> <!-- 🔚 Fin productos -->

<!-- 🔻 FOOTER -->
<footer>
    <a href="https://www.instagram.com/jym_mprendimiento">Instagram</a>
</footer>

<!-- 🪟 MODAL DE REGISTRO -->
<div class="modal" id="login">
   <div class="modal-content login-box">

    <h2>Registrarse</h2>

    <form id="formUsuario" 
    
    
action="<?php echo isset($_SESSION['usuario']) ? 'actualizar.php' : 'conexion.php'; ?>" 
method="POST">
<?php if(isset($_SESSION['usuario'])): ?>
    <p style="color:red; font-size:14px;">
        ⚠️ El correo y la contraseña no se pueden modificar.
    </p>
<?php endif; ?>

       <input type="email" name="correo" 
value="<?php echo $usuarioData['correo'] ?? ''; ?>" 
placeholder ="Correo electrónico" required 
<?php echo isset($_SESSION['usuario']) ? 'readonly' : ''; ?>>

<input type="text" name="nombre" 
value="<?php echo $usuarioData['nombre'] ?? ''; ?>" 
placeholder="Nombre" required>

<input type="text" name="apellido" 
value="<?php echo $usuarioData['apellido'] ?? ''; ?>" 
placeholder="Apellido" required>

<input type="text" name="dni" 
value="<?php echo $usuarioData['dni'] ?? ''; ?>" 
placeholder="DNI" required>

<input type="tel" name="telefono" 
value="<?php echo $usuarioData['telefono'] ?? ''; ?>" 
placeholder="Teléfono" required>

<input type="text" name="localidad" 
value="<?php echo $usuarioData['localidad'] ?? ''; ?>" 
placeholder="Localidad" required>

<input type="text" name="direccion" 
value="<?php echo $usuarioData['direccion'] ?? ''; ?>" 
placeholder="Dirección" required>

<input type="password" name="clave" placeholder="Contraseña" 
<?php echo isset($_SESSION['usuario']) ? 'disabled' : 'required'; ?>>

        <button type="submit">
<?php echo isset($_SESSION['usuario']) ? 'Guardar cambios' : 'Registrarse'; ?>
</button>
    </form>

    <!-- 🔽 Texto registrarse -->
    <p class="extra" onclick="cambiarModo()">o iniciar sesión</p>

    <!-- ❌ Botón cerrar -->
    <button class="btn-close" onclick="closeLogin()">Cerrar</button>

</div>
</div>


<script>
let carrito = [];

/*************************************
 * ➕ AGREGAR AL CARRITO
 *************************************/
function agregarAlCarrito(id, nombre, precio, stock){

    // 🔍 contar cuántos de este producto ya hay en el carrito
    let cantidadEnCarrito = carrito.filter(p => p.id === id).length;

    // 🚫 si ya alcanzó el stock, no deja agregar más
    if(cantidadEnCarrito >= stock){
        alert("No hay más stock disponible de este producto");
        return;
    }

    // ✅ agregar producto
    carrito.push({id, nombre, precio});
    actualizarCarrito();
}
/*************************************
 * 🪟 CONTROL MODAL
 *************************************/
function openLogin(){
    document.getElementById("login").style.display="flex";
}

function closeLogin(){
    document.getElementById("login").style.display="none";
}
// 🔽 REGISTRO 
let modoLogin = false;

function cambiarModo(){
    let form = document.getElementById("formUsuario");
    let titulo = document.querySelector(".login-box h2");
    let texto = document.querySelector(".extra");

    if(!modoLogin){
        // 👉 MODO LOGIN
        titulo.innerText = "Iniciar sesión";

        form.innerHTML = `
    <input type="email" name="correo" placeholder="Correo electrónico" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Iniciar sesión</button>
           `;

        form.action = "login.php";
        texto.innerText = "o registrarse";
        modoLogin = true;

    } else {
        // 👉 MODO REGISTRO (volver)
        location.reload(); // recarga todo a estado original
    }
}
// 🔽 Mostrar / ocultar menú
function toggleMenu(){
    let menu = document.getElementById("dropdownMenu");

    if(menu.style.display === "block"){
        menu.style.display = "none";
    } else {
        menu.style.display = "block";
    }
}
function editarPerfil(){
    openLogin();
    document.querySelector(".login-box h2").innerText = "Editar perfil";
}
function openCart(){
    document.getElementById("cartModal").style.display = "flex";
    actualizarCarrito();
}

function closeCart(){
    document.getElementById("cartModal").style.display = "none";
}
function actualizarCarrito(){
    let contenedor = document.getElementById("cartItems");
    let total = document.getElementById("totalCarrito");
    let totalPago = document.getElementById("totalCarritoPago");

    contenedor.innerHTML = "";
    let suma = 0;

    let agrupados = {};

    carrito.forEach(p => {
        if(!agrupados[p.id]){
            agrupados[p.id] = {...p, cantidad:1};
        } else {
            agrupados[p.id].cantidad++;
        }
    });

    for(let id in agrupados){
        let p = agrupados[id];
        suma += p.precio * p.cantidad;

        contenedor.innerHTML += `
            <div style="background:white; margin:10px; padding:10px; border-radius:10px;">
                ${p.nombre} x${p.cantidad} - $${p.precio * p.cantidad}
            </div>
        `;
    }

    total.innerText = "Total: $" + suma;
    totalPago.innerText = "Total: $" + suma;
}
function comprar(){
    if(carrito.length === 0){
        alert("El carrito está vacío");
    } else {
        alert("Compra realizada con éxito");
        carrito = [];
        actualizarCarrito();
        closeCart();
    }
}

</script>

</body>
</html>