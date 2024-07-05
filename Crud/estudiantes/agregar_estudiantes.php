<?php
include '../config.php';

// Verificar que se han enviado datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $nombres = isset($_POST['nombres']) ? $_POST['nombres'] : null;
    $apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : null;
    $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : null;
    $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : null;
    $correo_electronico = isset($_POST['correo_electronico']) ? $_POST['correo_electronico'] : null;
    $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : null;
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
    $genero = isset($_POST['genero']) ? $_POST['genero'] : null;
    $discapacidad = isset($_POST['discapacidad']) ? $_POST['discapacidad'] : null;
    $date_creation = isset($_POST['date_creation']) ? $_POST['date_creation'] : null; // Fecha de creación

    // Verificar que los campos requeridos no estén vacíos
    if (!empty($nombres) && !empty($apellidos) && !empty($cedula) && !empty($telefono) && !empty($correo_electronico) && !empty($date_creation)) {
        
        // Escapar caracteres especiales y evitar inyección SQL
        $nombres = mysqli_real_escape_string($conn, $nombres);
        $apellidos = mysqli_real_escape_string($conn, $apellidos);
        $cedula = mysqli_real_escape_string($conn, $cedula);
        $telefono = mysqli_real_escape_string($conn, $telefono);
        $correo_electronico = mysqli_real_escape_string($conn, $correo_electronico);
        $direccion = mysqli_real_escape_string($conn, $direccion);
        $fecha_nacimiento = mysqli_real_escape_string($conn, $fecha_nacimiento);
        $genero = mysqli_real_escape_string($conn, $genero);
        $discapacidad = mysqli_real_escape_string($conn, $discapacidad);
        $date_creation = mysqli_real_escape_string($conn, $date_creation);

        // Preparar consulta SQL (asegúrate de tener las columnas correctas en tu base de datos)
        $sql = "INSERT INTO estudiantes (nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, date_creation)
                VALUES ('$nombres', '$apellidos', '$cedula', '$telefono', '$correo_electronico', '$direccion', '$fecha_nacimiento', '$genero', '$discapacidad', '$date_creation')";
                
        if (mysqli_query($conn, $sql)) {
            header("Location: ../../views/admin/vistaestudiante_admin.php");
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
    <title>Agregar Estudiantes| Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
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
                <h5 class="card-title">Agregar Estudiante</h5>
            </div>
            <div class="card-body">
                <form action="agregar_estudiantes.php" method="post" enctype="multipart/form-data"
                    onsubmit="return validarFormulario()">
                    <div class="form-group">
                        <label for="nombres">Nombres:</label>
                        <input type="text" class="form-control" id="nombres" name="nombres" required>
                    </div>
                    <div class="form-group">
                        <label for="apellidos">Apellidos:</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                    </div>
                    <div class="form-group">
                        <label for="cedula">Cédula:</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" required>
                    </div>
                    <div class="form-group">
                        <label for="correo_electronico">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="correo_electronico" name="correo_electronico"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" class="form-control" id="direccion" name="direccion">
                    </div>
                    <div class="form-group">
                        <label for="fecha_de_nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                    </div>
                    <div class="form-group">
                        <label for="genero">Género:</label>
                        <select class="form-control" id="genero" name="genero">
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="discapacidad">Discapacidad:</label>
                        <select class="form-control" id="discapacidad" name="discapacidad">
                            <option value="">Seleccionar</option>
                            <option value="Si">Si</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_creation">Fecha de Creación:</label>
                        <input type="text" class="form-control" id="date_creation" name="date_creation"
                            value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                    <a href="../../views/admin/vistaestudiante_admin.php" class="btn btn-secondary">Regresar</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>
</body>
</html>