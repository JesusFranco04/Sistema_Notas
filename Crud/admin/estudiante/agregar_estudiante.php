<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil');

// Habilitar informes de errores para depuración
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mensaje = array();

$sql_nivel = "SELECT id_nivel, nombre FROM nivel";
$result_nivel = $conn->query($sql_nivel);
$niveles = $result_nivel->fetch_all(MYSQLI_ASSOC);

$sql_paralelo = "SELECT id_paralelo, nombre FROM paralelo";
$result_paralelo = $conn->query($sql_paralelo);
$paralelos = $result_paralelo->fetch_all(MYSQLI_ASSOC);

$sql_jornada = "SELECT id_jornada, nombre FROM jornada";
$result_jornada = $conn->query($sql_jornada);
$jornadas = $result_jornada->fetch_all(MYSQLI_ASSOC);

$sql_historial = "SELECT id_his_academico, año FROM historial_academico";
$result_historial = $conn->query($sql_historial);
$historiales = $result_historial->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
    $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
    $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $correo_electronico = isset($_POST['correo_electronico']) ? trim($_POST['correo_electronico']) : null;
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? trim($_POST['fecha_nacimiento']) : '';
    $genero = isset($_POST['genero']) ? $_POST['genero'] : '';
    $discapacidad = isset($_POST['discapacidad']) ? trim($_POST['discapacidad']) : '';
    $estado_calificacion = isset($_POST['estado_calificacion']) ? $_POST['estado_calificacion'] : '';
    $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
    $id_nivel = isset($_POST['id_nivel']) ? (int)$_POST['id_nivel'] : 0;
    $id_paralelo = isset($_POST['id_paralelo']) ? (int)$_POST['id_paralelo'] : 0;
    $id_jornada = isset($_POST['id_jornada']) ? (int)$_POST['id_jornada'] : 0;
    $id_his_academico = isset($_POST['id_his_academico']) ? (int)$_POST['id_his_academico'] : 0;
    $fecha_ingreso = date('Y-m-d H:i:s');

    if (!empty($nombres) && !empty($apellidos) && !empty($cedula) && !empty($direccion) && !empty($fecha_nacimiento) && !empty($genero) && !empty($discapacidad) && !empty($estado_calificacion) && !empty($estado) && !empty($id_nivel) && !empty($id_paralelo) && !empty($id_jornada) && !empty($id_his_academico)) {
        $sql = "SELECT * FROM estudiante WHERE cedula = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $cedula);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $mensaje = array(
                    'texto' => 'El estudiante con esta cédula ya está registrado.',
                    'clase' => 'alert-danger'
                );
            } else {
                $sql_insert = "INSERT INTO estudiante ( nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, estado_calificacion, estado, id_nivel, id_paralelo, id_jornada, id_his_academico, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
                
                $stmt_insert = $conn->prepare($sql_insert);
                if ($stmt_insert) {
                    // Bind parameters: "ssssssssssiiiiiss"
                    $stmt_insert->bind_param(
                        "sssssssssssiiiis",
                        $nombres,
                        $apellidos,
                        $cedula,
                        $telefono,
                        $correo_electronico,
                        $direccion,
                        $fecha_nacimiento,
                        $genero,
                        $discapacidad,
                        $estado_calificacion,
                        $estado,
                        $id_nivel,
                        $id_paralelo,
                        $id_jornada,
                        $id_his_academico,
                        $fecha_ingreso
                    );
                    

                    if ($stmt_insert->execute()) {
                        $mensaje = array(
                            'texto' => 'Estudiante registrado correctamente.',
                            'clase' => 'alert-success'
                        );
                    } else {
                        $mensaje = array(
                            'texto' => 'Error al registrar el estudiante. Inténtalo nuevamente.',
                            'clase' => 'alert-danger'
                        );
                    }
                    $stmt_insert->close();
                } else {
                    $mensaje = array(
                        'texto' => 'Error al preparar la consulta para registro.',
                        'clase' => 'alert-danger'
                    );
                }
            }
            $stmt->close();
        } else {
            $mensaje = array(
                'texto' => 'Error al preparar la consulta para verificación de registro.',
                'clase' => 'alert-danger'
            );
        }
    } else {
        $mensaje = array(
            'texto' => 'Por favor, complete todos los campos obligatorios.',
            'clase' => 'alert-danger'
        );
    }
}

if (isset($conn)) {
    $conn->close();
}
?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Estudiante | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Boxicons CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.0.7/css/boxicons.min.css">

    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .header-banner {
        background-color: #c1121f;
        color: #fff;
        text-align: center;
        padding: 20px 0;
    }

    .header-banner h1 {
        margin: 0;
        font-size: 24px;
    }

    .container {
        max-width: 800px;
        margin: 50px auto;
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
        background-color: #e71b2a;
        padding: 10px;
        border-radius: 10px;
        color: #fff;
    }

    .form-label.required::after {
        content: " *";
        color: red;
        margin-left: 5px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .btn-cancelar {
        background-color: #6c757d;
        color: #fff;
    }

    .btn-registrar {
        background-color: #e71b2a;
        color: #fff;
    }

    .form-label {
        font-weight: bold;
        color: #333;
    }

    .bx {
        margin-right: 10px;
    }

    #button-generate {
        background-color: #e71b2a;
        color: #fff;
        border-color: #e71b2a;
        width: 100%;
    }

    #button-generate:hover {
        background-color: #c1121f;
        border-color: #c1121f;
    }

    .optional-text {
        font-size: 12px;
        /* Tamaño pequeño de la letra */
        color: #999;
        /* Color gris */
        margin-left: 5px;
    }


    footer {
        background-color: #c1121f;
        color: #fff;
        text-align: center;
        padding: 20px 0;
        width: 100%;
    }

    footer p {
        margin: 0;
    }

    .error-message,
    .success-message {
        margin: 10px 0;
        padding: 10px;
        border-radius: 5px;
        text-align: center;
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
    }

    .success-message {
        background-color: #d4edda;
        color: #155724;
    }

    /* Alinear el grupo de botones a la derecha */
    .button-group {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        /* Espacio entre los botones */
    }

    /* Estilos generales para los botones */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        /* Tamaño de fuente reducido */
        padding: 8px 16px;
        /* Reducción de relleno */
        border-radius: 6px;
        /* Borde más suave */
        border: none;
        cursor: pointer;
        /* Cambia el cursor a una mano cuando se pasa el ratón sobre el botón */
        transition: background-color 0.3s ease;
        /* Añade una transición suave al color de fondo cuando cambia */
        text-transform: uppercase;
        font-weight: bold;
        color: white;
        /* Color del texto en todos los botones */
    }

    /* Estilos para el botón Regresar */
    .btn-regresar {
        background-color: #6c757d;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-regresar:hover {
        background-color: #5a6268;
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-regresar:active {
        background-color: #545b62;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    /* Estilos para el botón Crear Usuario */
    .btn-crear-usuario {
        background-color: #e71b2a;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-crear-usuario:hover {
        background-color: #c21623;
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-crear-usuario:active {
        background-color: #a0121d;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    /* Icono dentro del botón */
    .btn i {
        margin-right: 8px;
    }
    </style>
</head>

<body>
    <div class="header-banner">
        <h1>Formulario de Registro de Estudiantes | Sistema de Gestión UEBF</h1>
    </div>
    <div class="container">
        <h2><i class='bx bx-user-pin'></i>Registro de Estudiante</h2>
        <div class="card-body">
            <!-- Mostrar mensajes de alerta -->
            <?php if (!empty($mensaje)): ?>
            <div class="alert <?php echo $mensaje['clase']; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje['texto']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            <form action="" method="POST" onsubmit="return validarFormulario()">
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombres" class="form-label required"><i class='bx bxs-user'></i> Nombre:</label>
                            <input type="text" class="form-control" id="nombres" name="nombres"
                                pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras y espacios" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="apellidos" class="form-label required"><i class='bx bxs-user-detail'></i>
                                Apellidos:</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos"
                                pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras y espacios" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cedula" class="form-label required"><i class='bx bxs-id-card'></i>
                                Cédula:</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" maxlength="10"
                                pattern="[0-9]{10}" title="Ingrese exactamente 10 dígitos numéricos" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefono" class="form-label"><i class='bx bxs-phone'></i> Teléfono: <span
                                    class="optional-text">(Opcional)</span></label>
                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="10"
                                pattern="09[0-9]{8}"
                                title="El teléfono debe iniciar con 09 seguido de 8 dígitos numéricos">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="correo_electronico" class="form-label"><i class='bx bxs-envelope'></i> Correo
                                Electrónico: <span class="optional-text">(Opcional)</span></label>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="direccion" class="form-label required"><i class='bx bxs-location-plus'></i>
                                Dirección:</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_nacimiento" class="form-label required"><i class='bx bxs-calendar'></i>
                                Fecha de Nacimiento:</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="genero" class="form-label required"><i class='bx bx-female-sign'></i>
                                Género:</label>
                            <select class="form-control" id="genero" name="genero" required>
                                <option value="">Seleccionar género</option>
                                <option value="femenino">Femenino</option>
                                <option value="masculino">Masculino</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discapacidad" class="form-label required"><i class='bx bx-handicap'></i>
                                Discapacidad:</label>
                            <select class="form-control" id="discapacidad" name="discapacidad" required>
                                <option value="">Seleccionar discapacidad</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado_calificacion" class="form-label required"><i
                                    class='bx bxs-check-shield'></i> Estado de Calificación:</label>
                            <select class="form-control" id="estado_calificacion" name="estado_calificacion" required>
                                <option value="">Seleccionar Estado de la Calificación</option>
                                <option value="P">Pendiente</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado" class="form-label required"><i class='bx bxs-check-square'></i>
                                Estado:</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="">Seleccionar estado</option>
                                <option value="A">Activo</option>
                                <option value="I">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_nivel" class="form-label required"><i class='bx bxs-school'></i>
                                Nivel:</label>
                            <select class="form-control" id="id_nivel" name="id_nivel" required>
                                <option value="">Selecciona Nivel</option>
                                <?php foreach ($niveles as $nivel): ?>
                                <option value="<?= $nivel['id_nivel'] ?>"><?= $nivel['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_paralelo" class="form-label required"><i class='bx bxs-user-check'></i>
                                Paralelo:</label>
                            <select class="form-control" id="id_paralelo" name="id_paralelo" required>
                                <option value="">Selecciona Paralelo</option>
                                <?php foreach ($paralelos as $paralelo): ?>
                                <option value="<?= $paralelo['id_paralelo'] ?>"><?= $paralelo['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_jornada" class="form-label required"><i class='bx bxs-calendar-week'></i>
                                Jornada:</label>
                            <select class="form-control" id="id_jornada" name="id_jornada" required>
                                <option value="">Selecciona Jornada</option>
                                <?php foreach ($jornadas as $jornada): ?>
                                <option value="<?= $jornada['id_jornada'] ?>"><?= $jornada['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_his_academico" class="form-label required"><i class='bx bxs-time'></i>
                                Historial Académico:</label>
                            <select class="form-control" id="id_his_academico" name="id_his_academico" required>
                                <option value="">Selecciona Historial Académico</option>
                                <?php foreach ($historiales as $historial): ?>
                                <option value="<?= $historial['id_his_academico'] ?>"><?= $historial['año'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="button-group mt-4">
                    <button type="button" class="btn btn-regresar"
                        onclick="location.href='http://localhost/sistema_notas/views/admin/estudiantes.php';">
                        <i class='bx bx-arrow-back'></i> Regresar
                    </button>
                    <button type="submit" class="btn btn-crear-usuario">
                        <i class='bx bx-save'></i> Crear Estudiante
                    </button>
                </div>
            </form>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano.
            Todos los derechos reservados.</p>
    </footer>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function validarFormulario() {
        var fechaNacimiento = document.getElementById('fecha_nacimiento').value.trim();
        var hoy = new Date();
        var fechaNac = new Date(fechaNacimiento);
        var edad = hoy.getFullYear() - fechaNac.getFullYear();
        var mes = hoy.getMonth() - fechaNac.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }

        // Validar edad entre 11 y 18 años
        if (edad < 11 || edad > 18) {
            alert('La fecha de nacimiento no es válida. Debe tener entre 11 y 18 años.');
            return false;
        }

        return true;
    }
    </script>
</body>

</html>