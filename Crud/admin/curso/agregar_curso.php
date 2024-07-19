<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

$error = ''; // Variable para almacenar mensajes de error
$success = ''; // Variable para almacenar mensajes de éxito

// Función para obtener opciones de una tabla
function getOptions($conn, $table, $id_field, $name_field) {
    // Verificar si la tabla tiene la columna 'estado'
    $check_column_query = "SHOW COLUMNS FROM $table LIKE 'estado'";
    $check_result = $conn->query($check_column_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        $query = "SELECT $id_field, $name_field FROM $table WHERE estado = 'A'";
    } else {
        $query = "SELECT $id_field, $name_field FROM $table";
    }

    $result = $conn->query($query);
    if (!$result) {
        die("Error en la consulta a la tabla $table: " . $conn->error);
    }

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = $row;
    }
    return $options;
}

$profesores = getOptions($conn, 'profesor', 'id_profesor', 'nombres');
$materias = getOptions($conn, 'materia', 'id_materia', 'nombre');
$niveles = getOptions($conn, 'nivel', 'id_nivel', 'nombre');
$paralelos = getOptions($conn, 'paralelo', 'id_paralelo', 'nombre');
$subniveles = getOptions($conn, 'subnivel', 'id_subnivel', 'nombre');
$especialidades = getOptions($conn, 'especialidad', 'id_especialidad', 'nombre');
$jornadas = getOptions($conn, 'jornada', 'id_jornada', 'nombre');
$historicos = getOptions($conn, 'historial_academico', 'id_his_academico', 'año');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_profesor = $_POST['id_profesor'];
    $id_materia = $_POST['id_materia'];
    $id_nivel = $_POST['id_nivel'];
    $id_paralelo = $_POST['id_paralelo'];
    $id_subnivel = $_POST['id_subnivel'];
    $id_especialidad = $_POST['id_especialidad'];
    $id_jornada = $_POST['id_jornada'];
    $id_his_academico = $_POST['id_his_academico'];
    $estado = $_POST['estado'];
    $usuario_ingreso = $_SESSION['cedula'];
    $fecha_ingreso = date('Y-m-d H:i:s');

    // Verificar si el registro ya existe
    $check_query = "SELECT * FROM curso WHERE id_profesor = ? AND id_materia = ? AND id_nivel = ? AND id_paralelo = ? AND id_subnivel = ? AND id_especialidad = ? AND id_jornada = ? AND id_his_academico = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("iiiiiiii", $id_profesor, $id_materia, $id_nivel, $id_paralelo, $id_subnivel, $id_especialidad, $id_jornada, $id_his_academico);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Si el registro ya existe, mostrar mensaje de error
        $error = "El curso ya está registrado en la base de datos.";
    } else {
        // Si el registro no existe, proceder con la inserción
        $sql = "INSERT INTO curso (id_profesor, id_materia, id_nivel, id_paralelo, id_subnivel, id_especialidad, id_jornada, id_his_academico, estado, usuario_ingreso, fecha_ingreso)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }

        $stmt->bind_param("iiiiiiissss", $id_profesor, $id_materia, $id_nivel, $id_paralelo, $id_subnivel, $id_especialidad, $id_jornada, $id_his_academico, $estado, $usuario_ingreso, $fecha_ingreso);

        if ($stmt->execute()) {
            $success = "Curso guardado exitosamente";
        } else {
            $error = "Error en la ejecución de la consulta: " . $stmt->error;
        }

        $stmt->close();
    }

    $stmt_check->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Curso | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
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

    /* Estilos para alinear verticalmente los elementos en una fila */
    .row.align-items-center {
        align-items: center;
        /* Alinea verticalmente los elementos */
    }

    /* Alinear el grupo de botones a la derecha */
    .button-group {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        /* Espacio entre los botones */
        align-items: center;
        /* Alinea verticalmente los botones con los campos */
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
        <h1>Formulario de Registro de Cursos | Sistema de Gestión UEBF</h1>
    </div>
    <div class="container">
        <h2><i class='bx bxs-folder-plus'></i> Registro de Curso</h2>
        <div class="card-body">
            <?php
        // Mostrar mensajes de éxito o error si están presentes
        if (!empty($error)) {
            echo '<div class="alert alert-danger">' . $error . '</div>';
        }
        if (!empty($success)) {
            echo '<div class="alert alert-success">' . $success . '</div>';
        }
        ?>
            <form action="agregar_curso.php" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_profesor">Profesor: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class='bx bxs-user'></i></div>
                                </div>
                                <select class="form-control" id="id_profesor" name="id_profesor" required>
                                    <option value="" disabled selected>Selecciona Profesor</option>
                                    <?php foreach ($profesores as $profesor): ?>
                                    <option value="<?= $profesor['id_profesor'] ?>"><?= $profesor['nombres'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_materia">Materia: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class='bx bxs-book'></i></div>
                                </div>
                                <select class="form-control" id="id_materia" name="id_materia" required>
                                    <option value="" disabled selected>Selecciona Materia</option>
                                    <?php foreach ($materias as $materia): ?>
                                    <option value="<?= $materia['id_materia'] ?>"><?= $materia['nombre'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_nivel">Nivel: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class='bx bxs-layer'></i></div>
                                </div>
                                <select class="form-control" id="id_nivel" name="id_nivel" required>
                                    <option value="" disabled selected>Selecciona Nivel</option>
                                    <?php foreach ($niveles as $nivel): ?>
                                    <option value="<?= $nivel['id_nivel'] ?>"><?= $nivel['nombre'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_paralelo">Paralelo: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class='bx bxs-group'></i></div>
                                </div>
                                <select class="form-control" id="id_paralelo" name="id_paralelo" required>
                                    <option value="" disabled selected>Selecciona Paralelo</option>
                                    <?php foreach ($paralelos as $paralelo): ?>
                                    <option value="<?= $paralelo['id_paralelo'] ?>"><?= $paralelo['nombre'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_subnivel">Subnivel: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class='bx bxs-chalkboard'></i></div>
                                </div>
                                <select class="form-control" id="id_subnivel" name="id_subnivel" required>
                                    <option value="" disabled selected>Selecciona Subnivel</option>
                                    <?php foreach ($subniveles as $subnivel): ?>
                                    <option value="<?= $subnivel['id_subnivel'] ?>"><?= $subnivel['nombre'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_especialidad">Especialidad: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class='bx bxs-bookmark-alt'></i></div>
                                </div>
                                <select class="form-control" id="id_especialidad" name="id_especialidad" required>
                                    <option value="" disabled selected>Selecciona Especialidad</option>
                                    <?php foreach ($especialidades as $especialidad): ?>
                                    <option value="<?= $especialidad['id_especialidad'] ?>">
                                        <?= $especialidad['nombre'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_jornada">Jornada: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class='bx bxs-time'></i></div>
                                </div>
                                <select class="form-control" id="id_jornada" name="id_jornada" required>
                                    <option value="" disabled selected>Selecciona Jornada</option>
                                    <?php foreach ($jornadas as $jornada): ?>
                                    <option value="<?= $jornada['id_jornada'] ?>"><?= $jornada['nombre'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_his_academico">Año Lectivo: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class='bx bxs-calendar'></i></div>
                                </div>
                                <select class="form-control" id="id_his_academico" name="id_his_academico" required>
                                    <option value="" disabled selected>Selecciona Año Lectivo</option>
                                    <?php foreach ($historicos as $historico): ?>
                                    <option value="<?= $historico['id_his_academico'] ?>"><?= $historico['año'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado">Estado: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class='bx bxs-check-square'></i></div>
                                </div>
                                <select class="form-control" id="estado" name="estado" required>
                                    <option value="" disabled selected>Selecciona Estado</option>
                                    <option value="A">Activo</option>
                                    <option value="I">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="button-group">
                            <button type="button" class="btn btn-regresar"
                                onclick="location.href='http://localhost/sistema_notas/views/admin/curso_admin.php';">
                                <i class='bx bx-arrow-back'></i> Regresar
                            </button>
                            <button type="submit" class="btn btn-crear-usuario">
                                <i class='bx bx-save'></i> Crear Curso
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <input type="hidden" name="usuario_ingreso" value="<?= $_SESSION['cedula'] ?>">
    <input type="hidden" name="fecha_ingreso" value="<?= date('Y-m-d H:i:s') ?>">

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano.
            Todos los derechos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>