<?php
include '../config.php';

// Verificar que se han enviado datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : null;
    $fecha_ingreso = isset($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : null; // Fecha de creación

    // Verificar que los campos requeridos no estén vacíos
    if (!empty($nombre) && !empty($fecha_ingreso)) {

        // Escapar caracteres especiales y evitar inyección SQL
        $nombre = mysqli_real_escape_string($conn, $nombre);
        $fecha_ingreso = mysqli_real_escape_string($conn, $fecha_ingreso);

        // Preparar consulta SQL (asegúrate de tener las columnas correctas en tu base de datos)
        $sql = "INSERT INTO jornada (nombre, fecha_ingreso)
                VALUES ('$nombre', '$fecha_ingreso')";
                
        if (mysqli_query($conn, $sql)) {
            header("Location: ../../views/admin/jornada_admin.php");
            exit; // Salir del script después de la redirección
        } else {
            echo "Error al ejecutar la consulta: " . mysqli_error($conn);
        }        

    } else {
        echo "Todos los campos marcados como requeridos deben estar llenos.";
    }
}

// Cerrar conexión si es necesario
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Agregar Jornada | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Estilos personalizados -->
    <style>
    .sidebar-heading .collapse-header .bx {
        color: #ff8b97;
        /* Color rosa claro para los iconos en los encabezados de sección */
    }

    .bg-gradient-primary {
        background-color: #a2000e;
        /* Color rojo oscuro para el fondo de la barra lateral */
        background-image: none;
        /* Asegurar que no haya imagen de fondo (gradiente) */
    }

    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .card {
        width: 80%;
        max-width: 600px;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #f8f9fc;
        padding: 10px;
    }

    .card-title {
        margin-bottom: 0;
    }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Agregar Jornada</h5>
            </div>
            <div class="card-body">
                <form action="agregar_jornada.php" method="post" enctype="multipart/form-data" onsubmit="return validarFormulario()">
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required>
                        <!-- Añadir un atributo 'maxlength' para limitar la longitud -->
                    </div>
                    <div class="form-group">
                        <label for="fecha_ingreso">Fecha de ingreso:</label>
                        <input type="text" class="form-control" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                    <a href="../../views/admin/jornada_admin.php" class="btn btn-secondary">Regresar</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>
</html>
