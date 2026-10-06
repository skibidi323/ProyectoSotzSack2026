<?php

session_start();


// ==========================================
// COMPROBAR QUE SEA ADMINISTRADOR
// ==========================================

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: index.php");
    exit;
}


// ==========================================
// CONEXIÓN A LA BASE DE DATOS
// ==========================================

$conn = pg_connect(
    "host=localhost dbname=tienda user=postgres password=1234"
);

if (!$conn) {
    die("Error de conexión con la base de datos");
}


// ==========================================
// FILTROS
// ==========================================

$fechaDesde = $_GET['fecha_desde'] ?? '';
$fechaHasta = $_GET['fecha_hasta'] ?? '';

$precioMin = $_GET['precio_min'] ?? '';
$precioMax = $_GET['precio_max'] ?? '';


// ==========================================
// CONSULTA DE COMPRAS
// ==========================================

$query = "
    SELECT
        c.id AS compra_id,
        c.fecha,
        c.total,

        u.nombre,
        u.apellido,
        u.correo,
        u.telefono,
        u.localidad,
        u.direccion,

        STRING_AGG(
            dc.nombre_producto || ' (x' || dc.cantidad || ')',
            ', '
        ) AS productos

    FROM compras c

    INNER JOIN usuarios u
        ON c.usuario_id = u.id

    INNER JOIN detalle_compra dc
        ON c.id = dc.compra_id

    WHERE 1=1
";


// ==========================================
// FILTRO POR FECHA DESDE
// ==========================================

if ($fechaDesde !== '') {

    $query .= "
        AND c.fecha >= $1
    ";

}


// ==========================================
// FILTRO POR FECHA HASTA
// ==========================================

if ($fechaHasta !== '') {

    if ($fechaDesde !== '') {

        $query .= "
            AND c.fecha <= $2
        ";

    } else {

        $query .= "
            AND c.fecha <= $1
        ";

    }

}


// ==========================================
// FILTRO POR PRECIO MÍNIMO
// ==========================================

if ($precioMin !== '') {

    $query .= "
        AND c.total >= $" .
        (($fechaDesde !== '' ? 1 : 0) +
         ($fechaHasta !== '' ? 1 : 0) + 1) . "
    ";

}


// ==========================================
// FILTRO POR PRECIO MÁXIMO
// ==========================================

if ($precioMax !== '') {

    $numeroParametro =
        ($fechaDesde !== '' ? 1 : 0) +
        ($fechaHasta !== '' ? 1 : 0) +
        ($precioMin !== '' ? 1 : 0) +
        1;

    $query .= "
        AND c.total <= $" . $numeroParametro . "
    ";

}


// ==========================================
// AGRUPAR
// ==========================================

$query .= "

    GROUP BY
        c.id,
        c.fecha,
        c.total,
        u.nombre,
        u.apellido,
        u.correo,
        u.telefono,
        u.localidad,
        u.direccion

    ORDER BY c.fecha DESC, c.id DESC
";


// ==========================================
// PREPARAR PARÁMETROS
// ==========================================

$parametros = [];

if ($fechaDesde !== '') {
    $parametros[] = $fechaDesde;
}

if ($fechaHasta !== '') {
    $parametros[] = $fechaHasta;
}

if ($precioMin !== '') {
    $parametros[] = $precioMin;
}

if ($precioMax !== '') {
    $parametros[] = $precioMax;
}


// ==========================================
// EJECUTAR CONSULTA
// ==========================================

$result = pg_query_params(
    $conn,
    $query,
    $parametros
);

if (!$result) {
    die(
        "Error al consultar las compras: " .
        pg_last_error($conn)
    );
}


// ==========================================
// CANTIDAD DE RESULTADOS
// ==========================================

$cantidadResultados = pg_num_rows($result);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel de administrador</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f1f1;
            color: #2c3338;
        }


        /* ==========================================
           CABECERA
           ========================================== */

        .admin-header {

            background: #ffffff;

            border-bottom: 1px solid #c3c4c7;

            padding: 18px 25px;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .admin-header h1 {

            margin: 0;

            font-size: 24px;

            font-weight: 500;
        }


        .btn-salir {

            background: #2271b1;

            color: white;

            text-decoration: none;

            padding: 9px 16px;

            border-radius: 4px;

            font-size: 14px;
        }


        .btn-salir:hover {

            background: #135e96;
        }


        /* ==========================================
           CONTENEDOR
           ========================================== */

        .admin-container {

            padding: 20px;
        }


        /* ==========================================
           CATEGORÍAS
           ========================================== */

        .admin-tabs {

            display: flex;

            align-items: center;

            gap: 7px;

            margin-bottom: 12px;

            font-size: 15px;
        }


        .admin-tabs a {

            color: #2271b1;

            text-decoration: none;
        }


        .admin-tabs a:hover {

            color: #135e96;
        }


        .admin-tabs .activo {

            color: #1d2327;

            font-weight: bold;
        }


        /* ==========================================
           FILTROS
           ========================================== */

        .filtros {

            display: flex;

            align-items: center;

            gap: 8px;

            flex-wrap: wrap;

            margin-bottom: 10px;
        }


        .filtros select,
        .filtros input {

            height: 36px;

            border: 1px solid #8c8f94;

            border-radius: 4px;

            background: white;

            padding: 0 10px;

            font-size: 14px;
        }


        .filtros select {

            min-width: 190px;
        }


        .filtros input {

            width: 160px;
        }


        .filtros label {

            font-size: 14px;
        }


        .btn-filtrar {

            height: 36px;

            padding: 0 15px;

            border: 1px solid #2271b1;

            background: #f6f7f7;

            color: #2271b1;

            border-radius: 4px;

            cursor: pointer;

            font-size: 14px;
        }


        .btn-filtrar:hover {

            background: #f0f0f1;
        }


        .btn-aplicar {

            height: 36px;

            padding: 0 15px;

            border: 1px solid #2271b1;

            background: #2271b1;

            color: white;

            border-radius: 4px;

            cursor: pointer;

            font-size: 14px;
        }


        .btn-aplicar:hover {

            background: #135e96;
        }


        .cantidad-resultados {

            margin-left: auto;

            font-size: 14px;

            color: #50575e;
        }


        /* ==========================================
           TABLA
           ========================================== */

        .tabla-contenedor {

            background: white;

            border: 1px solid #c3c4c7;

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 1100px;
        }


        th {

            text-align: left;

            font-weight: 500;

            color: #2c3338;

            padding: 12px;

            border-bottom: 1px solid #c3c4c7;

            white-space: nowrap;
        }


        td {

            padding: 14px 12px;

            border-bottom: 1px solid #f0f0f1;

            vertical-align: middle;
        }


        tbody tr:hover {

            background: #f6f7f7;
        }


        .checkbox {

            width: 18px;

            height: 18px;

            cursor: pointer;
        }


        .cliente {

            color: #2271b1;

            font-weight: 500;
        }


        .correo {

            color: #50575e;
        }


        .productos {

            max-width: 300px;

            line-height: 1.5;
        }


        .precio {

            font-weight: 500;

            white-space: nowrap;
        }


        .fecha {

            white-space: nowrap;
        }


        /* ==========================================
           RESPONSIVE
           ========================================== */

        @media (max-width: 900px) {

            .admin-container {

                padding: 10px;
            }


            .admin-header {

                padding: 15px;
            }


            .cantidad-resultados {

                margin-left: 0;
            }

        }

    </style>

</head>


<body>


<!-- ==========================================
     CABECERA
     ========================================== -->

<header class="admin-header">

    <h1>
        Panel de administrador
    </h1>


    <a
        href="logout.php"
        class="btn-salir"
    >
        Cerrar sesión
    </a>

</header>


<!-- ==========================================
     CONTENIDO
     ========================================== -->

<main class="admin-container">


    <!-- ======================================
         CATEGORÍAS
         ====================================== -->

    <div class="admin-tabs">

        <span class="activo">
            Todas
        </span>

        |

        <a href="#">
            Compras
        </a>

        |

        <a href="#">
            Clientes
        </a>

    </div>


    <!-- ======================================
         FORMULARIO DE FILTROS
         ====================================== -->

    <form
        method="GET"
        action="admin.php"
    >

        <div class="filtros">


            <!-- ACCIONES -->

            <select name="accion">

                <option value="">
                    Acciones en lote
                </option>

                <option value="seleccionar">
                    Seleccionar
                </option>

            </select>


            <button
                type="button"
                class="btn-aplicar"
            >
                Aplicar
            </button>


            <!-- FECHA -->

            <label>
                Filtrar por fecha
            </label>


            <input
                type="date"
                name="fecha_desde"
                value="<?php echo htmlspecialchars($fechaDesde); ?>"
            >


            <input
                type="date"
                name="fecha_hasta"
                value="<?php echo htmlspecialchars($fechaHasta); ?>"
            >


            <!-- PRECIO -->

            <label>
                Filtrar por precio
            </label>


            <input
                type="number"
                name="precio_min"
                placeholder="Mínimo"
                value="<?php echo htmlspecialchars($precioMin); ?>"
            >


            <input
                type="number"
                name="precio_max"
                placeholder="Máximo"
                value="<?php echo htmlspecialchars($precioMax); ?>"
            >


            <!-- FILTRAR -->

            <button
                type="submit"
                class="btn-filtrar"
            >
                Filtro
            </button>


            <!-- RESTABLECER -->

            <a
                href="admin.php"
                class="btn-filtrar"
                style="text-decoration:none;"
            >
                Restablecer el filtro
            </a>


            <!-- CANTIDAD -->

            <span class="cantidad-resultados">

                <?php echo $cantidadResultados; ?>

                elementos

            </span>

        </div>

    </form>


    <!-- ======================================
         TABLA
         ====================================== -->

    <div class="tabla-contenedor">

        <table>

            <thead>

                <tr>

                    <th>

                        <input
                            type="checkbox"
                            class="checkbox"
                        >

                    </th>


                    <th>
                        Compra
                    </th>


                    <th>
                        Cliente
                    </th>


                    <th>
                        Correo electrónico
                    </th>


                    <th>
                        Productos
                    </th>


                    <th>
                        Total
                    </th>


                    <th>
                        Fecha
                    </th>

                </tr>

            </thead>


            <tbody>


                <?php

                while ($row = pg_fetch_assoc($result)) {

                ?>

                    <tr>


                        <td>

                            <input
                                type="checkbox"
                                class="checkbox"
                                value="<?php echo htmlspecialchars($row['compra_id']); ?>"
                            >

                        </td>


                        <td>

                            #<?php
                            echo htmlspecialchars(
                                $row['compra_id']
                            );
                            ?>

                        </td>


                        <td class="cliente">

                            <?php

                            echo htmlspecialchars(
                                $row['nombre'] .
                                ' ' .
                                $row['apellido']
                            );

                            ?>

                        </td>


                        <td class="correo">

                            <?php

                            echo htmlspecialchars(
                                $row['correo']
                            );

                            ?>

                        </td>


                        <td class="productos">

                            <?php

                            echo htmlspecialchars(
                                $row['productos']
                            );

                            ?>

                        </td>


                        <td class="precio">

                            $

                            <?php

                            echo htmlspecialchars(
                                $row['total']
                            );

                            ?>

                        </td>


                        <td class="fecha">

                            <?php

                            echo htmlspecialchars(
                                $row['fecha']
                            );

                            ?>

                        </td>


                    </tr>

                <?php

                }

                ?>


            </tbody>

        </table>

    </div>


</main>


</body>

</html>