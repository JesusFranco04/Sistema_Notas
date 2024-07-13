<?php
// Iniciar la sesión para obtener datos del usuario que está logueado
session_start();
// Incluir el archivo de configuración para conectarte a la base de datos
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificación del método de solicitud
    // Validación de campos requeridos
    $required_fields = ['cedula', 'nombres', 'apellidos', 'telefono', 'correo_electronico', 'direccion', 'fecha_nacimiento', 'genero', 'discapacidad', 'contraseña', 'id_rol', 'estado'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            die("Error: Todos los campos son obligatorios.");
        }
    }

    // Validación del formato de correo electrónico
    $correo = $_POST['correo_electronico'];
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die("Error: El correo electrónico no tiene un formato válido.");
    }

    // Validación de fecha de nacimiento y edad
    $fechaNacimiento = $_POST['fecha_nacimiento'];
    $hoy = new DateTime();
    $fechaNac = new DateTime($fechaNacimiento);
    $edad = $hoy->diff($fechaNac)->y;

    if ($edad < 18 || $edad > 80 || $fechaNac > $hoy) {
        die("Error: La fecha de nacimiento no es válida. Debe tener entre 18 y 80 años.");
    }

    // Captura de datos del formulario
    $cedula = $_POST['cedula'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $telefono = $_POST['telefono'];
    $correo_electronico = $_POST['correo_electronico'];
    $direccion = $_POST['direccion'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $discapacidad = $_POST['discapacidad'];
    $contraseña = $_POST['contraseña'];
    $id_rol = $_POST['rol'];
    $estado = $_POST['estado'];
    $usuario_ingreso = $_SESSION['cedula']; // Tomar el usuario de sesión
    $fecha_ingreso = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual

    // Preparación de la consulta SQL
    $stmt = $conn->prepare("INSERT INTO administrador (cedula, nombres, apellidos, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, contraseña, id_rol, estado, usuario_ingreso, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssss", $cedula, $nombres, $apellidos, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $contraseña, $id_rol, $estado, $usuario_ingreso, $fecha_ingreso);

    // Preparación y ejecución segura de la consulta
    if ($stmt->execute()) {
        // Redireccionar a alguna página de éxito o mostrar un mensaje
        header("Location: http://localhost/sistema_notas/views/admin/index_admin.php");
        exit();
    } else {
        // Mostrar un mensaje de error si la consulta no se ejecuta correctamente
        echo "Error al ejecutar la consulta: " . $stmt->error;
    }

    // Cierre de statement y conexión
    $stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Administradores | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Incluye Bootstrap CSS para estilos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Incluye Boxicons CSS para iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/css/boxicons.min.css">
    <style>
    .required::after {
        content: '*';
        color: red;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 800px;
        margin: 20px auto;
        /* Añadimos margen y centramos verticalmente */
        padding: 20px;
        background-color: #fff;
        /* Fondo blanco */
        border: 1px solid #ccc;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #dc3545;
        /* Rojo claro */
        color: #fff;
        padding: 15px;
        border-radius: 10px 10px 0 0;
    }

    .card-title {
        margin: 0;
        font-size: 1.5rem;
    }

    .card-body {
        padding: 20px;
    }

    .form-label {
        font-weight: bold;
    }

    .form-label.required::after {
        content: " *";
        color: #dc3545;
    }

    .input-group-append .btn {
        background-color: #007bff;
        /* Azul oscuro bonito */
        border-color: #007bff;
        color: #fff;
    }

    .input-group-append .btn:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    .form-label.required::after {
        content: " *";
        color: #dc3545;
    }

    input[type="text"],
    input[type="password"],
    input[type="email"],
    input[type="date"],
    select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px;
        width: 100%;
        box-sizing: border-box;
    }

    input[type="radio"] {
        margin-right: 5px;
    }

    .text-center {
        text-align: center;
    }

    .mt-4 {
        margin-top: 1.5rem;
    }

    .mt-5 {
        margin-top: 3rem;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
    }

    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .col-md-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Formulario de Registro de Administrador</h5>
            </div>
            <div class="card-body">
                <!-- Formulario de Registro -->
                <form action="" method="POST" onsubmit="return validarFormulario()">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="form-label required"><i class='bx bx-user'></i> Nombres:</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                        <div class="col-md-6">
                            <label for="form-label required"><i class='bx bx-user'></i> Apellidos:</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cedula"><i class='bx bx-id-card'></i> Cédula:</label>
                            <select id="cedulaSelect" name="cedula" onchange="fetchUserData(this.value)"
                                class="form-control">
                                <option value="">Seleccione un usuario</option>
                                <?php
                                $result = $conn->query("SELECT cedula FROM usuario WHERE id_rol = 1 ORDER BY fecha_ingreso DESC LIMIT 5");
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="'.$row['cedula'].'">'.$row['cedula'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="form-label required"><i class='bx bx-phone'></i> Teléfono:</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="10"
                                pattern="[0-9]{10}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="form-label required"><i class='bx bx-envelope'></i> Correo Electrónico:</label>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label for="form-label required"><i class='bx bx-map'></i> Dirección:</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_nacimiento"><i class='bx bx-calendar'></i> Fecha de Nacimiento:</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label for="form-label required"><i class='bx bx-user-circle'></i> Género:</label>
                            <select class="form-control" id="genero" name="genero" required>
                                <option value="">Seleccionar Género</option>
                                <option value="femenino">Femenino</option>
                                <option value="masculino">Masculino</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="form-label required"><i class='bx bx-handicap'></i> ¿Tiene Discapacidad?</label>
                            <select class="form-control" id="discapacidad" name="discapacidad" required>
                                <option value="">Seleccionar</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="id_rol"><i class='bx bx-user-check'></i> Rol:</label>
                            <input type="text" id="id_rol" name="id_rol" readonly disabled class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="estado"><i class='bx bx-toggle-right'></i> Estado:</label>
                            <input type="text" id="estado" name="estado" readonly disabled class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="usuario_ingreso"><i class='bx bx-user'></i> Usuario de Ingreso:</label>
                            <input type="text" class="form-control" id="usuario_ingreso" name="usuario_ingreso"
                                value="<?php echo $_SESSION['cedula']; ?>" readonly disabled>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_ingreso"><i class='bx bx-calendar'></i> Fecha de Ingreso:</label>
                            <input type="text" class="form-control" id="fecha_ingreso" name="fecha_ingreso"
                                value="<?php echo date('Y-m-d H:i:s'); ?>" readonly disabled>
                        </div>
                    </div>
                    <div class="button-group mt-4">
                        <button type="button" class="btn btn-secondary"
                            onclick="location.href='http://localhost/sistema_notas/views/admin/index_admin.php';"><i
                                class='bx bx-arrow-back'></i> Regresar</button>
                        <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Crear
                            Administrador</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Incluye Bootstrap JS para funcionalidades -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Incluye Boxicons JS para iconos -->
    <script src="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/js/boxicons.min.js"></script>
    <!-- Script para validar el formulario -->
    <script>
    function validarFormulario() {
        var nombres = document.getElementById('nombres').value.trim();
        var apellidos = document.getElementById('apellidos').value.trim();
        var cedula = document.getElementById('cedula').value.trim();
        var telefono = document.getElementById('telefono').value.trim();
        var correo = document.getElementById('correo_electronico').value.trim();
        var direccion = document.getElementById('direccion').value.trim();
        var fechaNacimiento = document.getElementById('fecha_nacimiento').value.trim();
        var genero = document.getElementById('genero').value.trim();
        var discapacidad = document.getElementById('discapacidad').value.trim();
        var rol = document.getElementById('id_rol').value.trim();
        var contraseña = document.getElementById('contraseña').value.trim();
        var estado = document.querySelector('input[name="estado"]:checked').value.trim();
        var usuarioIngreso = document.getElementById('usuario_ingreso').value.trim();

        if (nombres === '' || apellidos === '' || cedula === '' || telefono === '' || correo === '' ||
            direccion === '' || fechaNacimiento === '' || genero === '' || discapacidad === '' || rol === '' ||
            contraseña === '' || estado === '' || usuarioIngreso === '') {
            alert('Por favor, complete todos los campos obligatorios.');
            return false;
        }


        var fechaNacimiento = document.getElementById('fecha_nacimiento').value.trim();
        var hoy = new Date();
        var fechaNac = new Date(fechaNacimiento);
        var edad = hoy.getFullYear() - fechaNac.getFullYear();
        var mes = hoy.getMonth() - fechaNac.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }
        if (edad < 18 || edad > 80) {
            alert('La fecha de nacimiento no es válida. Debe tener entre 18 y 80 años.');
            return false;
        }
        return true;
    } <
    !--Incluye Bootstrap JS para funcionalidad-- >
    <
    script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" >
    </script>
    <script>
    $(document).ready(function() {
                // Cargar las últimas 5 cédulas al cargar la página
                cargarCedulas();

                // Función para cargar las últimas 5 cédulas desde get_cedulas.php
                function cargarCedulas() {
                    $.ajax({
                        url: 'get_cedulas.php', // Ruta relativa al archivo get_cedulas.php
                        method: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            // Limpiar opciones actuales del select
                            $('#cedula').empty();
                            // Llenar el select con las cédulas obtenidas
                            $.each(data, function(index, cedula) {
                                $('#cedula').append('<option value="' + cedula + '">' + cedula +
                                    '</option>');
                            });
                            // Cargar automáticamente los datos al seleccionar una cédula
                            $('#cedula').change(function() {
                                var selectedCedula = $(this).val();
                                obtenerDatosUsuario(selectedCedula);
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al obtener las cédulas:', error);
                        }
                    });
                }
    }
       

    </script>
    <!-- Bootstrap core JavaScript-->
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>

</html>