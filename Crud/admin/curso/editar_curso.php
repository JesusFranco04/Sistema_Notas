<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil');

// Inicializar variables
$profesores = [];
$materias = [];
$niveles = [];
$paralelos = [];
$subniveles = [];
$especialidades = [];
$jornadas = [];
$historiales = [];
$curso = [];
$error = '';
$success = '';

// ID del curso a editar (debe ser pasado como parámetro GET)
$id_curso = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los valores del formulario
    $id_profesor = $_POST['id_profesor'];
    $id_materias = $_POST['id_materias']; // Esto recogerá un array de materias seleccionadas
    $id_nivel = $_POST['id_nivel'];
    $id_paralelo = $_POST['id_paralelo'];
    $id_subnivel = $_POST['id_subnivel'];
    $id_especialidad = $_POST['id_especialidad'];
    $id_jornada = $_POST['id_jornada'];
    $id_his_academico = $_POST['id_his_academico'];
    $estado = $_POST['estado'];
    $usuario_ingreso = $_SESSION['cedula'];
    $fecha_ingreso = date('Y-m-d H:i:s');

    // Verificar si existe algún registro duplicado
    $verificarConsulta = "SELECT * FROM curso WHERE id_profesor = ? AND id_materia = ? AND id_nivel = ? AND id_paralelo = ? AND id_subnivel = ? AND id_especialidad = ? AND id_jornada = ? AND id_his_academico = ? AND estado = ? AND id_curso <> ?";

    if ($verificarStmt = $conn->prepare($verificarConsulta)) {
        $existeRegistro = false;
        $todosInsertados = true;

        foreach ($id_materias as $materia) {
            $verificarStmt->bind_param("iiiiiiissi", $id_profesor, $materia, $id_nivel, $id_paralelo, $id_subnivel, $id_especialidad, $id_jornada, $id_his_academico, $estado, $id_curso);

            $verificarStmt->execute();
            $result = $verificarStmt->get_result();

            if ($result->num_rows > 0) {
                $existeRegistro = true;
                break;
            }
        }

        if ($existeRegistro) {
            $error = "Uno o más cursos ya están registrados para las materias seleccionadas.";
        } else {
            // Actualizar los datos en la base de datos
            $consulta = "UPDATE curso SET id_profesor = ?, id_nivel = ?, id_paralelo = ?, id_subnivel = ?, id_especialidad = ?, id_jornada = ?, id_his_academico = ?, estado = ?, usuario_ingreso = ?, fecha_ingreso = ? WHERE id_curso = ?";

            if ($stmt = $conn->prepare($consulta)) {
                $stmt->bind_param("iiiiiiisssi", $id_profesor, $id_nivel, $id_paralelo, $id_subnivel, $id_especialidad, $id_jornada, $id_his_academico, $estado, $usuario_ingreso, $fecha_ingreso, $id_curso);

                if ($stmt->execute()) {
                    $success = "Curso actualizado con éxito.";
                } else {
                    $error = "Error al actualizar el curso: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $error = "Error al preparar la consulta de actualización: " . $conn->error;
            }
        }

        $verificarStmt->close();
    } else {
        $error = "Error al preparar la consulta de verificación: " . $conn->error;
    }
}

// Obtener los datos del curso a editar
if ($id_curso > 0) {
    $consulta = "SELECT * FROM curso WHERE id_curso = ?";
    if ($stmt = $conn->prepare($consulta)) {
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $curso = $result->fetch_assoc();
        }
        $stmt->close();
    } else {
        $error = "Error al preparar la consulta para obtener el curso: " . $conn->error;
    }
}

// Obtener los datos para las listas desplegables
try {
    // Profesores (seleccionando solo los que están activos)
    $result = $conn->query("
        SELECT p.id_profesor, CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo 
        FROM profesor p
        INNER JOIN usuario u ON p.id_usuario = u.id_usuario
        WHERE u.estado = 'A'
    ");
    if ($result->num_rows > 0) {
        $profesores = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Materias (Solo activas)
    $result = $conn->query("SELECT id_materia, nombre FROM materia WHERE estado = 'A'");
    if ($result->num_rows > 0) {
        $materias = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Niveles (Solo activos)
    $result = $conn->query("SELECT id_nivel, nombre FROM nivel WHERE estado = 'A'");
    if ($result->num_rows > 0) {
        $niveles = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Paralelos (Solo activos)
    $result = $conn->query("SELECT id_paralelo, nombre FROM paralelo WHERE estado = 'A'");
    if ($result->num_rows > 0) {
        $paralelos = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Subniveles (Solo activos)
    $result = $conn->query("SELECT id_subnivel, nombre FROM subnivel WHERE estado = 'A'");
    if ($result->num_rows > 0) {
        $subniveles = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Especialidades (Solo activas)
    $result = $conn->query("SELECT id_especialidad, nombre FROM especialidad WHERE estado = 'A'");
    if ($result->num_rows > 0) {
        $especialidades = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Jornadas (Solo activas)
    $result = $conn->query("SELECT id_jornada, nombre FROM jornada WHERE estado = 'A'");
    if ($result->num_rows > 0) {
        $jornadas = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Historiales académicos (Solo activos)
    $result = $conn->query("SELECT id_his_academico, año FROM historial_academico WHERE estado = 'A'");
    if ($result->num_rows > 0) {
        $historiales = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $error = "Error al obtener los datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso | Sistema de Gestión UEBF</title>
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

    .row.align-items-center {
        align-items: center;
    }

    .button-group {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        align-items: center;
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

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .form-control {
        height: 38px;
        /* Ajusta la altura del campo de selección */
        border-radius: 6px;
        /* Bordes redondeados */
    }

    .materia-container {
        max-height: 300px;
        /* Ajusta la altura según sea necesario */
        overflow-y: auto;
        /* Añade scrollbar vertical si el contenido es más grande que el contenedor */
        border: 1px solid #ced4da;
        /* Añade borde para mejor visualización */
        padding: 10px;
        /* Añade padding para separar el contenido del borde */
        border-radius: 5px;
        /* Bordes redondeados */
        background-color: #f8f9fa;
        /* Fondo claro */
    }

    .materia-container .form-check {
        margin-bottom: 10px;
        /* Espacio entre los checkboxes */
    }

    .form-check-input {
        margin-right: 10px;
        /* Espacio entre el checkbox y la etiqueta */
    }

    .form-check-label {
        font-size: 14px;
        /* Tamaño de fuente más pequeño para una mejor presentación */
    }
    </style>
</head>

<body>
    <header class="header-banner">
        <h1>Formulario de Edición de Cursos | Sistema de Gestión UEBF</h1>
    </header>

    <div class="container">
        <h2><i class='bx bxs-folder'></i> Editar Curso</h2>

        <?php if ($error): ?>
        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="success-message">
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>?id=<?= htmlspecialchars($id_curso) ?>"
            method="POST">
            <div class="row">
                <!-- Columna 1 -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_profesor">Profesor: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-user'></i></div>
                            </div>
                            <select class="form-control" id="id_profesor" name="id_profesor" required>
                                <option value="" disabled>Selecciona Profesor</option>
                                <?php foreach ($profesores as $profesor): ?>
                                <option value="<?= htmlspecialchars($profesor['id_profesor']) ?>"
                                    <?= ($curso['id_profesor'] == $profesor['id_profesor']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($profesor['nombre_completo']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_materias">Materias: <span class="text-danger">*</span></label>
                        <div class="materia-container">
                            <?php foreach ($materias as $materia): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="id_materias[]"
                                    value="<?= htmlspecialchars($materia['id_materia']) ?>"
                                    <?= in_array($materia['id_materia'], explode(',', $curso['id_materia'])) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= htmlspecialchars($materia['nombre']) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_nivel">Nivel: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-layer'></i></div>
                            </div>
                            <select class="form-control" id="id_nivel" name="id_nivel" required>
                                <option value="" disabled>Selecciona Nivel</option>
                                <?php foreach ($niveles as $nivel): ?>
                                <option value="<?= htmlspecialchars($nivel['id_nivel']) ?>"
                                    <?= ($curso['id_nivel'] == $nivel['id_nivel']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nivel['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_paralelo">Paralelo: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-grid'></i></div>
                            </div>
                            <select class="form-control" id="id_paralelo" name="id_paralelo" required>
                                <option value="" disabled>Selecciona Paralelo</option>
                                <?php foreach ($paralelos as $paralelo): ?>
                                <option value="<?= htmlspecialchars($paralelo['id_paralelo']) ?>"
                                    <?= ($curso['id_paralelo'] == $paralelo['id_paralelo']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($paralelo['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_subnivel">Subnivel: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-layer'></i></div>
                            </div>
                            <select class="form-control" id="id_subnivel" name="id_subnivel" required>
                                <option value="" disabled>Selecciona Subnivel</option>
                                <?php foreach ($subniveles as $subnivel): ?>
                                <option value="<?= htmlspecialchars($subnivel['id_subnivel']) ?>"
                                    <?= ($curso['id_subnivel'] == $subnivel['id_subnivel']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subnivel['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Columna 2 -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_especialidad">Especialidad: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-briefcase'></i></div>
                            </div>
                            <select class="form-control" id="id_especialidad" name="id_especialidad" required>
                                <option value="" disabled>Selecciona Especialidad</option>
                                <?php foreach ($especialidades as $especialidad): ?>
                                <option value="<?= htmlspecialchars($especialidad['id_especialidad']) ?>"
                                    <?= ($curso['id_especialidad'] == $especialidad['id_especialidad']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($especialidad['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_jornada">Jornada: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-time'></i></div>
                            </div>
                            <select class="form-control" id="id_jornada" name="id_jornada" required>
                                <option value="" disabled>Selecciona Jornada</option>
                                <?php foreach ($jornadas as $jornada): ?>
                                <option value="<?= htmlspecialchars($jornada['id_jornada']) ?>"
                                    <?= ($curso['id_jornada'] == $jornada['id_jornada']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($jornada['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_his_academico">Historial Académico: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-bookmark'></i></div>
                            </div>
                            <select class="form-control" id="id_his_academico" name="id_his_academico" required>
                                <option value="" disabled>Selecciona Historial Académico</option>
                                <?php foreach ($historiales as $historial): ?>
                                <option value="<?= htmlspecialchars($historial['id_his_academico']) ?>"
                                    <?= ($curso['id_his_academico'] == $historial['id_his_academico']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($historial['año']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-checkbox-checked'></i></div>
                            </div>
                            <select class="form-control" id="estado" name="estado" required>
                                <option value="" disabled>Selecciona Estado</option>
                                <option value="A" <?= ($curso['estado'] == 'A') ? 'selected' : '' ?>>Activo
                                </option>
                                <option value="I" <?= ($curso['estado'] == 'I') ? 'selected' : '' ?>>Inactivo
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="usuario_ingreso" class="form-label required">Usuario de Ingreso</label>
                        <input type="text" class="form-control" id="usuario_ingreso" name="usuario_ingreso"
                            value="<?php echo htmlspecialchars($curso['usuario_ingreso']); ?>" readonly disabled>
                    </div>

                    <div class="form-group">
                        <label for="fecha_ingreso" class="form-label required">Fecha de Ingreso</label>
                        <input type="text" class="form-control" id="fecha_ingreso" name="fecha_ingreso"
                            value="<?php echo htmlspecialchars($curso['fecha_ingreso']); ?>" readonly disabled>
                    </div>
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