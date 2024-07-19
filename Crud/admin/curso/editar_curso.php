<?php
// Iniciar la sesión para obtener datos del usuario que está logueado
session_start();

// Incluir el archivo de configuración para conectarte a la base de datos
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

// Verifica si se ha enviado el ID del curso
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Error: ID del curso no proporcionado.');
}

// Recupera el ID del curso desde la URL
$id_curso = $_GET['id'];

$error_message = '';
$success_message = '';

// Obtiene los datos actuales del curso desde la base de datos
$sql = "SELECT * FROM curso WHERE id_curso = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Error: Curso no encontrado.');
}

$curso = $result->fetch_assoc();

// Obtener nombres asociados con los IDs
$tables = [
    'profesor' => 'id_profesor',
    'materia' => 'id_materia',
    'nivel' => 'id_nivel',
    'paralelo' => 'id_paralelo',
    'subnivel' => 'id_subnivel',
    'especialidad' => 'id_especialidad',
    'jornada' => 'id_jornada',
    'historial_academico' => 'id_his_academico'
];

$names = [];

foreach ($tables as $table => $id_field) {
    $column_name = 'nombre'; // Ajusta el nombre de la columna según tu esquema real

    // Verificar si la columna existe en la tabla
    $check_col_sql = "SHOW COLUMNS FROM {$table} LIKE '{$column_name}'";
    $check_col_result = $conn->query($check_col_sql);
    
    if ($check_col_result->num_rows > 0) {
        // La columna existe
        $sql = "SELECT id_{$table}, {$column_name} FROM {$table} WHERE id_{$table} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $curso[$id_field]);
        $stmt->execute();
        $result = $stmt->get_result();
        $names[$table] = $result->fetch_assoc();
    } else {
        // Si la columna no existe, usa un valor predeterminado
        $names[$table] = ['id_' . $table => $curso[$id_field], 'nombre' => 'No disponible'];
    }
}

// Verifica si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge los datos del formulario
    $id_profesor = $_POST['id_profesor'];
    $id_materia = $_POST['id_materia'];
    $id_nivel = $_POST['id_nivel'];
    $id_paralelo = $_POST['id_paralelo'];
    $id_subnivel = $_POST['id_subnivel'];
    $id_especialidad = $_POST['id_especialidad'];
    $id_jornada = $_POST['id_jornada'];
    $id_his_academico = $_POST['id_his_academico'];
    $estado = $_POST['estado'];
    $usuario_ingreso = $_POST['usuario_ingreso'];
    $fecha_ingreso = $_POST['fecha_ingreso'];

    // Iniciar una transacción
    $conn->begin_transaction();

    try {
        // Actualiza el registro en la base de datos
        $sql = "UPDATE curso SET id_profesor = ?, id_materia = ?, id_nivel = ?, id_paralelo = ?, id_subnivel = ?, id_especialidad = ?, id_jornada = ?, id_his_academico = ?, estado = ?, usuario_ingreso = ?, fecha_ingreso = ? WHERE id_curso = ?";
        $stmt = $conn->prepare($sql);

        // Verifica si la preparación de la consulta fue exitosa
        if ($stmt === false) {
            throw new Exception('Error: Fallo en la preparación de la consulta.');
        }

        // Vincula los parámetros con la consulta
        $stmt->bind_param("iiiiiiiisssi", $id_profesor, $id_materia, $id_nivel, $id_paralelo, $id_subnivel, $id_especialidad, $id_jornada, $id_his_academico, $estado, $usuario_ingreso, $fecha_ingreso, $id_curso);

        // Ejecuta la consulta
        if ($stmt->execute()) {
            // Confirmar la transacción
            $conn->commit();
            $success_message = "Curso actualizado exitosamente.";
        } else {
            throw new Exception('Error al actualizar el curso: ' . $stmt->error);
        }
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso | Sistema de Gestión UEBF</title>
    <!-- Bootstrap CSS -->
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

    .required {
        color: red;
        /* Color rojo para los campos obligatorios */
        margin-left: 5px;
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
        <h1>Formulario de Edición de Cursos | Sistema de Gestión UEBF</h1>
    </div>
    <div class="container">
        <h2><i class='bx bxs-badge-check' ></i>Editar Curso</h2>
        <!-- Mostrar mensaje de error o éxito -->
        <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <?php elseif ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validarFormulario()">
            <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($curso['id_curso']); ?>">

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="id_profesor" class="form-label required">Profesor</label>
                    <select class="form-control" id="id_profesor" name="id_profesor" required>
                        <option value="">Seleccione un profesor</option>
                        <?php
                        $sql = "SELECT id_profesor, CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM profesor";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($curso['id_profesor'] == $row['id_profesor']) ? 'selected' : '';
                            echo "<option value=\"{$row['id_profesor']}\" {$selected}>{$row['nombre_completo']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="id_materia" class="form-label required">Materia</label>
                    <select class="form-control" id="id_materia" name="id_materia" required>
                        <option value="">Seleccione una materia</option>
                        <?php
                        $sql = "SELECT id_materia, nombre FROM materia";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($curso['id_materia'] == $row['id_materia']) ? 'selected' : '';
                            echo "<option value=\"{$row['id_materia']}\" {$selected}>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="id_nivel" class="form-label required">Nivel</label>
                    <select class="form-control" id="id_nivel" name="id_nivel" required>
                        <option value="">Seleccione un nivel</option>
                        <?php
                        $sql = "SELECT id_nivel, nombre FROM nivel";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($curso['id_nivel'] == $row['id_nivel']) ? 'selected' : '';
                            echo "<option value=\"{$row['id_nivel']}\" {$selected}>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="id_paralelo" class="form-label required">Paralelo</label>
                    <select class="form-control" id="id_paralelo" name="id_paralelo" required>
                        <option value="">Seleccione un paralelo</option>
                        <?php
                        $sql = "SELECT id_paralelo, nombre FROM paralelo";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($curso['id_paralelo'] == $row['id_paralelo']) ? 'selected' : '';
                            echo "<option value=\"{$row['id_paralelo']}\" {$selected}>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="id_subnivel" class="form-label required">Subnivel</label>
                    <select class="form-control" id="id_subnivel" name="id_subnivel" required>
                        <option value="">Seleccione un subnivel</option>
                        <?php
                        $sql = "SELECT id_subnivel, nombre FROM subnivel";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($curso['id_subnivel'] == $row['id_subnivel']) ? 'selected' : '';
                            echo "<option value=\"{$row['id_subnivel']}\" {$selected}>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="id_especialidad" class="form-label">Especialidad</label>
                    <select class="form-control" id="id_especialidad" name="id_especialidad">
                        <option value="">Seleccione una especialidad</option>
                        <?php
                        $sql = "SELECT id_especialidad, nombre FROM especialidad";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($curso['id_especialidad'] == $row['id_especialidad']) ? 'selected' : '';
                            echo "<option value=\"{$row['id_especialidad']}\" {$selected}>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="id_jornada" class="form-label">Jornada</label>
                    <select class="form-control" id="id_jornada" name="id_jornada">
                        <option value="">Seleccione una jornada</option>
                        <?php
                        $sql = "SELECT id_jornada, nombre FROM jornada";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($curso['id_jornada'] == $row['id_jornada']) ? 'selected' : '';
                            echo "<option value=\"{$row['id_jornada']}\" {$selected}>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="id_his_academico" class="form-label">Historial Académico</label>
                    <select class="form-control" id="id_his_academico" name="id_his_academico">
                        <option value="">Seleccione un historial académico</option>
                        <?php
                        $sql = "SELECT id_his_academico, año FROM historial_academico";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($curso['id_his_academico'] == $row['id_his_academico']) ? 'selected' : '';
                            echo "<option value=\"{$row['id_his_academico']}\" {$selected}>{$row['año']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="estado" class="form-label required">Estado</label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="">Seleccione un estado</option>
                        <option value="A" <?php echo ($curso['estado'] == 'activo') ? 'selected' : ''; ?>>Activo
                        </option>
                        <option value="I" <?php echo ($curso['estado'] == 'inactivo') ? 'selected' : ''; ?>>
                            Inactivo</option>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="usuario_ingreso" class="form-label required">Usuario de Ingreso</label>
                    <input type="text" class="form-control" id="usuario_ingreso" name="usuario_ingreso"
                        value="<?php echo htmlspecialchars($curso['usuario_ingreso']); ?>" required>
                </div>

                <div class="col-md-6 form-group">
                    <label for="fecha_ingreso" class="form-label required">Fecha de Ingreso</label>
                    <input type="texto" class="form-control" id="fecha_ingreso" name="fecha_ingreso"
                        value="<?php echo htmlspecialchars($curso['fecha_ingreso']); ?>" required>
                </div>
            </div>

            <div class="button-group mt-4">
                <button type="button" class="btn btn-regresar"
                    onclick="location.href='http://localhost/sistema_notas/views/admin/curso_admin.php';">
                    <i class='bx bx-arrow-back'></i> Regresar
                </button>
                <button type="submit" class="btn btn-crear-usuario">
                    <i class='bx bx-save'></i> Actualizar
                </button>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano. Todos los derechos reservados.</p>
    </footer>
</body>

</html>