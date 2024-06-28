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
    $rol = isset($_POST['rol']) ? $_POST['rol'] : null;
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : null;
    $archivo = isset($_FILES['archivo']['name']) ? $_FILES['archivo']['name'] : null; // Nombre del archivo subido
    $date_creation = isset($_POST['date_creation']) ? $_POST['date_creation'] : null; // Fecha de creación

    // Verificar que los campos requeridos no estén vacíos
    if (!empty($nombres) && !empty($apellidos) && !empty($cedula) && !empty($telefono) && !empty($correo_electronico) && !empty($rol) && !empty($contrasena) && !empty($date_creation)) {
        
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
        $rol = mysqli_real_escape_string($conn, $rol);
        $contrasena = mysqli_real_escape_string($conn, $contrasena);
        $archivo = mysqli_real_escape_string($conn, $archivo);
        $date_creation = mysqli_real_escape_string($conn, $date_creation);

        // Preparar consulta SQL (asegúrate de tener las columnas correctas en tu base de datos)
        $sql = "INSERT INTO administrador (nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, rol, contrasena, archivo, date_creation)
                VALUES ('$nombres', '$apellidos', '$cedula', '$telefono', '$correo_electronico', '$direccion', '$fecha_nacimiento', '$genero', '$discapacidad', '$rol', '$contrasena', '$archivo', '$date_creation')";
                
        if (mysqli_query($conn, $sql)) {
            header("Location: ../../views/admin/administradores.php");
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
    <title>Agregar Administrador | Sistema de Gestión UEBF</title>
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
                <h5 class="card-title">Agregar Admin</h5>
            </div>
            <div class="card-body">
                <form action="agregar_admin.php" method="post" enctype="multipart/form-data"
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
                        <label for="rol">Rol:</label>
                        <select class="form-control" id="rol" name="rol" required>
                            <option value="">Selecciona Rol</option>
                            <option value="1">Abministrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contrasena">Contraseña:</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="contrasena" name="contrasena"
                                placeholder="Ingrese texto" aria-label="Caja de texto"
                                aria-describedby="button-generate" disabled>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="button-generate"
                                    onclick="generarClave()" required>Generar Clave</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="archivo">Archivo:</label>
                        <input type="file" class="form-control-file" id="archivo" name="archivo"
                            onchange="mostrarInfoArchivo()" required>
                        <div id="info-archivo"></div>
                    </div>
                    <div class="form-group">
                        <label for="date_creation">Fecha de Creación:</label>
                        <input type="text" class="form-control" id="date_creation" name="date_creation"
                            value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                    <a href="../../views/admin/administradores.php" class="btn btn-secondary">Regresar</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts adicionales aquí -->

    <script>
    function generarClave() {
        const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let clave = '';
        for (let i = 0; i < 12; i++) {
            const randomIndex = Math.floor(Math.random() * caracteres.length);
            clave += caracteres[randomIndex];
        }
        const input_contrasena = document.getElementById('contrasena');
        input_contrasena.value = clave;
        input_contrasena.disabled = false; // Habilitar el campo
        document.getElementById('button-generate').disabled = true; // Deshabilitar el botón de generar
        document.getElementById('submit-button').disabled = false; // Habilitar el botón de guardar
    }

    function validarFormulario() {
        const input_contrasena = document.getElementById('contrasena');
        if (input_contrasena.value === '') {
            alert('Por favor, genere una contraseña.');
            return false;
        }
        return true;
    }

    function mostrarInfoArchivo() {
        const input = document.getElementById('archivo');
        const infoArchivo = document.getElementById('info-archivo');
        if (input.files.length > 0) {
            const archivo = input.files[0];
            const tamaño = archivo.size / 1024;
            const tipo = archivo.type || 'Tipo desconocido';
            infoArchivo.innerHTML = `
                <p><strong>Nombre:</strong> ${archivo.name}</p>
                <p><strong>Tipo:</strong> ${tipo}</p>
                <p><strong>Tamaño:</strong> ${tamaño.toFixed(2)} KB</p>
            `;
        } else {
            infoArchivo.innerHTML = '';
        }
    }
    </script>
    <!-- Bootstrap core JavaScript-->
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>
</body>
</html>
