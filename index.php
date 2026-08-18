<!DOCTYPE html>

<?php 
session_start();

$usuarioData = null;

if(isset($_SESSION['correo'])){

    $conn = pg_connect(
        "host=localhost dbname=tienda user=postgres password=1234"
    );

    $correo = $_SESSION['correo'];

    $query = "SELECT * FROM usuarios WHERE correo='$correo'";

    $result = pg_query($conn,$query);

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
* GENERALES
*************************************/

*{
margin:0;
padding:0;
box-sizing:border-box;
}


body{

font-family:Arial;
background:#E1E8ED;
color:#333;

}


/*************************************
* HEADER
*************************************/

header{

display:flex;
justify-content:space-between;
align-items:center;

padding:15px 30px;

border-bottom:2px solid black;

}



/*************************************
* PRODUCTOS
*************************************/


.products{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:20px;
    padding:20px;
}

@media(max-width:900px){

    .products{
        grid-template-columns:repeat(2,1fr);
    }

}


@media(max-width:600px){

    .products{
        grid-template-columns:1fr;
    }

}

.product{

background:white;

padding:15px;

border-radius:10px;

text-align:center;

}



.product img{

max-width:150px;

}



/*************************************
* MODALES
*************************************/


.modal{

display:none;

position:fixed;

width:100%;

height:100%;

top:0;

left:0;

background:rgba(0,0,0,.5);

justify-content:center;

align-items:center;

z-index:1000;

}



.modal-content{

background:white;

padding:20px;

border-radius:10px;

}



/*************************************
* CARRITO E HISTORIAL
*************************************/


.cart-container{

background:white;

width:400px;

height:90%;

overflow-y:auto;

padding:20px;

border-radius:15px;

}



.btn-close{

margin-bottom:15px;

padding:8px;

cursor:pointer;

}



.btn-comprar{

width:100%;

padding:12px;

background:#1e90ff;

color:white;

border:none;

border-radius:8px;

cursor:pointer;

font-size:16px;

}


.btn-comprar:hover{

background:#187bcd;

}
footer{
    background:black;
    color:white;
    text-align:center;
    padding:20px;
}

footer a{
    color:white;
    text-decoration:none;
    font-weight:bold;
}

footer a:hover{
    text-decoration:underline;
}
footer{
    border-top:2px solid black;
}


</style>


</head>


<body>



<!-- ===========================
     CARRITO
=========================== -->


<div class="modal" id="cartModal">


<div class="cart-container">


<button class="btn-close" onclick="closeCart()">

← Continuar compra

</button>



<div class="qr-box">

<p>
Escanea el QR para realizar el pago
</p>


<img src="QR/QRparaproyecto.png"
width="200">


</div>



<div id="cartItems"></div>



<p class="mensaje-compra">

Una vez confirmado el pago, recibirás en tu correo electrónico la confirmación de tu compra junto con los datos del envío.

</p>



<h2 id="totalCarrito">

Total: $0

</h2>



<button class="btn-comprar"
onclick="comprar()">

Comprar ahora

</button>


</div>

</div>




<!-- ===========================
     HISTORIAL
=========================== -->


<div class="modal" id="historialModal">


<div class="cart-container">


<button class="btn-close"
onclick="closeHistorial()">

← Volver

</button>


<h2>
Historial de compras
</h2>


<div id="historialContenido">

</div>


</div>

</div>





<!-- ===========================
HEADER
=========================== -->


<header>



<img src="main-logo2.png" width="200">



<div class="icons" 
style="display:flex;gap:10px;">



<button class="btn-login"
onclick="openCart()">

🛒 Carrito

</button>



<?php if(isset($_SESSION['usuario'])): ?>


<button class="btn-login"
onclick="openHistorial()">

📜 Historial

</button>



<div class="user-menu">


<button class="btn-login"
onclick="toggleMenu()">

👤 <?php echo $_SESSION['usuario']; ?>

</button>



<div class="dropdown"
id="dropdownMenu">


<a href="#"
onclick="editarPerfil()">

Editar perfil

</a>


<a href="logout.php">

Cerrar sesión

</a>


</div>


</div>



<?php else: ?>


<button class="btn-login"
onclick="openLogin()">

🔒 Registrarse

</button>


<?php endif; ?>



</div>


</header>





<!-- PRODUCTOS -->

<section class="products">
<?php

$conn = pg_connect(
"host=localhost dbname=tienda user=postgres password=1234"
);


$query="SELECT * FROM productos";

$result=pg_query($conn,$query);



while($row=pg_fetch_assoc($result)){


echo '

<div class="product">


<img src="'.$row['imagen'].'" 
onerror="this.style.display=\'none\'">


<p>
'.$row['nombre'].'
</p>


<p>
$'.$row['precio'].'
</p>


<p id="stock-'.$row['id'].'">

Stock:
'.$row['stock'].'

</p>



<button onclick="agregarAlCarrito(
'.$row['id'].',
\''.$row['nombre'].'\',
'.$row['precio'].',
'.$row['stock'].'
)">

Agregar al carrito

</button>



</div>

';


}

?>


</section>





<footer>

<a href="https://www.instagram.com/jym_mprendimiento">

Instagram

</a>

</footer>






<!-- ===========================
LOGIN / REGISTRO
=========================== -->


<div class="modal" id="login">


<div class="modal-content login-box">



<h2>

Registrarse

</h2>




<form id="formUsuario"

action="<?php 

echo isset($_SESSION['usuario']) 
? 'actualizar.php' 
: 'conexion.php';

?>"

method="POST">



<?php if(isset($_SESSION['usuario'])): ?>


<p style="color:red">

⚠️ El correo y contraseña no se pueden modificar.

</p>


<?php endif; ?>




<input 
type="email"
name="correo"

value="<?php echo $usuarioData['correo'] ?? ''; ?>"

placeholder="Correo electrónico"

required

<?php echo isset($_SESSION['usuario'])?'readonly':''; ?>

>




<input

type="text"

name="nombre"

value="<?php echo $usuarioData['nombre'] ?? ''; ?>"

placeholder="Nombre"

required

>




<input

type="text"

name="apellido"

value="<?php echo $usuarioData['apellido'] ?? ''; ?>"

placeholder="Apellido"

required

>




<input

type="text"

name="dni"

value="<?php echo $usuarioData['dni'] ?? ''; ?>"

placeholder="DNI"

required

>




<input

type="tel"

name="telefono"

value="<?php echo $usuarioData['telefono'] ?? ''; ?>"

placeholder="Teléfono"

required

>




<input

type="text"

name="localidad"

value="<?php echo $usuarioData['localidad'] ?? ''; ?>"

placeholder="Localidad"

required

>




<input

type="text"

name="direccion"

value="<?php echo $usuarioData['direccion'] ?? ''; ?>"

placeholder="Dirección"

required

>




<input

type="password"

name="clave"

placeholder="Contraseña"

<?php echo isset($_SESSION['usuario'])?'disabled':'required'; ?>

>




<button type="submit">

<?php

echo isset($_SESSION['usuario'])

?'Guardar cambios'

:'Registrarse';

?>

</button>



</form>





<p class="extra"
onclick="cambiarModo()">

o iniciar sesión

</p>




<button class="btn-close"
onclick="closeLogin()">

Cerrar

</button>



</div>

</div>






<script>


let carrito = [];




/********************************
AGREGAR PRODUCTO
********************************/


function agregarAlCarrito(id, nombre, precio, stock){

    fetch("agregar_carrito.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify({

            producto_id: id

        })

    })

    .then(res => res.json())

    .then(data => {

        if(data.ok){

            cargarCarrito();

            alert("Producto agregado al carrito");

        }else{

            alert(data.mensaje);

        }

    })

    .catch(error => {

        console.error(error);

        alert("Ocurrió un error al agregar el producto");

    });

}








/********************************
LOGIN
********************************/


function openLogin(){

document.getElementById("login")
.style.display="flex";

}



function closeLogin(){

document.getElementById("login")
.style.display="none";

}




let modoLogin=false;



function cambiarModo(){


let form=document.getElementById(
"formUsuario"
);



let titulo=document.querySelector(
".login-box h2"
);



let texto=document.querySelector(
".extra"
);




if(!modoLogin){



titulo.innerText=
"Iniciar sesión";



form.innerHTML=`

<input 
type="email"
name="correo"
placeholder="Correo electrónico"
required>


<input 
type="password"
name="password"
placeholder="Contraseña"
required>



<button>

Iniciar sesión

</button>

`;



form.action="login.php";


texto.innerText=
"o registrarse";


modoLogin=true;


}else{


location.reload();


}


}






/********************************
MENU USUARIO
********************************/


function toggleMenu(){


let menu=document.getElementById(
"dropdownMenu"
);



menu.style.display=
menu.style.display==="block"
?"none"
:"block";


}



function editarPerfil(){

openLogin();

document.querySelector(
".login-box h2"
).innerText="Editar perfil";

}






/********************************
CARRITO MODAL
********************************/


function openCart(){

    document.getElementById(
        "cartModal"
    ).style.display="flex";

    cargarCarrito();

}

function cargarCarrito(){

    fetch("cargar_carrito.php")

    .then(res => res.json())

    .then(data => {

        carrito = data;

        actualizarCarrito();

    })

    .catch(error => {

        console.error(
            "Error cargando carrito:",
            error
        );

    });

} 





function closeCart(){

document.getElementById(
"cartModal"
).style.display="none";

}






/********************************
HISTORIAL
********************************/


function openHistorial(){


document.getElementById(
"historialModal"
).style.display="flex";



fetch("historial.php")

.then(r=>r.text())

.then(data=>{


document.getElementById(
"historialContenido"
).innerHTML=data;


});


}



function closeHistorial(){


document.getElementById(
"historialModal"
).style.display="none";


}
/********************************
ACTUALIZAR CARRITO
********************************/


function actualizarCarrito(){

    let contenedor = document.getElementById("cartItems");
    let total = document.getElementById("totalCarrito");

    contenedor.innerHTML = "";

    let suma = 0;

    carrito.forEach(p => {

        let cantidad = Number(p.cantidad);
        let precio = Number(p.precio);

        let subtotal = precio * cantidad;

        suma += subtotal;

        contenedor.innerHTML += `
            <div style="
                background:#f5f5f5;
                padding:10px;
                margin:10px;
                border-radius:10px;
            ">

                <b>${p.nombre}</b>

                <br>

                Cantidad: ${cantidad}

                <br>

                Precio: $${precio}

                <br>

                Subtotal: $${subtotal}

            </div>
        `;

    });

    total.innerText = "Total: $" + suma;

}




Object.values(agrupados).forEach(p=>{


let subtotal=
p.precio*p.cantidad;


suma+=subtotal;



contenedor.innerHTML+=`

<div style="
background:#f5f5f5;
padding:10px;
margin:10px;
border-radius:10px;
">


<b>${p.nombre}</b>


<br>


Cantidad:
${p.cantidad}


<br>


Precio:
$${subtotal}


</div>

`;



});



total.innerText=
"Total: $"+suma;



}






/********************************
REALIZAR COMPRA
********************************/


function comprar(){



if(carrito.length===0){


alert(
"El carrito está vacío"
);


return;


}




let agrupados={};




carrito.forEach(p=>{


if(!agrupados[p.id]){


agrupados[p.id]={

id:p.id,

nombre:p.nombre,

precio:p.precio,

cantidad:1

};


}else{


agrupados[p.id].cantidad++;


}


});







fetch(
"comprar.php",
{


method:"POST",


headers:{


"Content-Type":
"application/json"


},


body:JSON.stringify(
Object.values(agrupados)
)


}

)





.then(res=>res.text())



.then(resultado=>{


if(resultado.trim()=="ok"){



alert(
"Compra realizada correctamente"
);



carrito=[];



actualizarCarrito();



closeCart();



location.reload();



}else{


alert(resultado);


}



});



}



cargarCarrito();


</script>


</body>


</html>