<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil');


define('ROL_ADMIN', 'Administrador');
define('ROL_SUPER', 'Superusuario');

if (!isset($_SESSION['cedula']) || !isset($_SESSION['rol']) || 
    !in_array($_SESSION['rol'], [ROL_ADMIN, ROL_SUPER], true)) {
    session_destroy();
    header("Location: http://localhost/sistema_notas/login.php");
    exit();
}

$error = ''; // Variable para almacenar mensajes de error
$success = ''; // Variable para almacenar mensajes de éxito

// Obtener datos de la base de datos
try {
    $profesores = $conn->query("
        SELECT p.id_profesor, CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo 
        FROM profesor p
        INNER JOIN usuario u ON p.id_usuario = u.id_usuario
        WHERE u.estado = 'A'
    ")->fetch_all(MYSQLI_ASSOC);

    $materias = $conn->query("SELECT id_materia, nombre FROM materia WHERE estado = 'A'")->fetch_all(MYSQLI_ASSOC);
    $niveles = $conn->query("SELECT id_nivel, nombre FROM nivel WHERE estado = 'A'")->fetch_all(MYSQLI_ASSOC);
    $paralelos = $conn->query("SELECT id_paralelo, nombre FROM paralelo WHERE estado = 'A'")->fetch_all(MYSQLI_ASSOC);
    $subniveles = $conn->query("SELECT id_subnivel, nombre FROM subnivel WHERE estado = 'A'")->fetch_all(MYSQLI_ASSOC);
    $especialidades = $conn->query("SELECT id_especialidad, nombre FROM especialidad WHERE estado = 'A'")->fetch_all(MYSQLI_ASSOC);
    $jornadas = $conn->query("SELECT id_jornada, nombre FROM jornada WHERE estado = 'A'")->fetch_all(MYSQLI_ASSOC);
    $historiales = $conn->query("SELECT id_his_academico, año FROM historial_academico WHERE estado = 'A'")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Error al obtener los datos: " . $e->getMessage();
}

// Manejar la inserción de un curso
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_profesor = $_POST['id_profesor'];
    $id_materias = $_POST['id_materias'] ?? [];
    $id_nivel = $_POST['id_nivel'];
    $id_paralelo = $_POST['id_paralelo'];
    $id_subnivel = $_POST['id_subnivel'];
    $id_especialidad = $_POST['id_especialidad'];
    $id_jornada = $_POST['id_jornada'];
    $id_his_academico = $_POST['id_his_academico'];
    $estado = 'A';
    $usuario_ingreso = $_SESSION['cedula'];
    $fecha_ingreso = date('Y-m-d H:i:s');

    if (!empty($id_profesor) && !empty($id_materias) && !empty($id_nivel) && !empty($id_paralelo) &&
        !empty($id_subnivel) && !empty($id_especialidad) && !empty($id_jornada) && !empty($id_his_academico)) {
        
        $conn->begin_transaction(); // Iniciar transacción

        try {
            foreach ($id_materias as $id_materia) {
                $stmt_check = $conn->prepare("
                    SELECT COUNT(*) 
                    FROM curso 
                    WHERE id_profesor = ? AND id_materia = ? AND id_nivel = ? 
                    AND id_paralelo = ? AND id_subnivel = ? AND id_especialidad = ? 
                    AND id_jornada = ? AND id_his_academico = ?
                ");
                $stmt_check->bind_param(
                    "iiiiiiii", $id_profesor, $id_materia, $id_nivel, $id_paralelo,
                    $id_subnivel, $id_especialidad, $id_jornada, $id_his_academico
                );
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count > 0) {
                    throw new Exception("Ya existe un curso con estos datos para la materia: $id_materia.");
                }

                $stmt_insert = $conn->prepare("
                    INSERT INTO curso (
                        id_profesor, id_materia, id_nivel, id_paralelo, 
                        id_subnivel, id_especialidad, id_jornada, id_his_academico, 
                        estado, usuario_ingreso, fecha_ingreso
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt_insert->bind_param(
                    "iiiiiiiisss", 
                    $id_profesor, $id_materia, $id_nivel, $id_paralelo,
                    $id_subnivel, $id_especialidad, $id_jornada, $id_his_academico,
                    $estado, $usuario_ingreso, $fecha_ingreso
                );
                $stmt_insert->execute();
                $stmt_insert->close();
            }

            $conn->commit(); // Confirmar la transacción
            $success = "El curso ha sido creado exitosamente.";
        } catch (Exception $e) {
            $conn->rollback(); // Revertir la transacción
            $error = "Error al guardar el curso: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}

$conn->close();
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

    .materia-container .no-materias {
        color: red;
        font-weight: bold;
        text-align: center;
        margin-top: 20px;
    }
    </style>
</head>

<body>
    <header class="header-banner">
        <h1>Formulario de Registro de Cursos | Sistema de Gestión UEBF</h1>
    </header>

    <div class="container">
        <h2><i class='bx bxs-folder-plus'></i> Registro de Curso</h2>

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

        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
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
                                <option value="<?= htmlspecialchars($profesor['id_profesor']) ?>">
                                    <?= htmlspecialchars($profesor['nombre_completo']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="id_materias">Materias: <span class="text-danger">*</span></label>
                        <div class="materia-container">
                            <?php if (empty($materias)): ?>
                            <p style="color: red; font-weight: bold;">No hay materias registradas todavía.</p>
                            <?php else: ?>
                            <?php foreach ($materias as $materia): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    id="materia_<?= htmlspecialchars($materia['id_materia']) ?>" name="id_materias[]"
                                    value="<?= htmlspecialchars($materia['id_materia']) ?>">
                                <label class="form-check-label"
                                    for="materia_<?= htmlspecialchars($materia['id_materia']) ?>">
                                    <?= htmlspecialchars($materia['nombre']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

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
                                <option value="<?= htmlspecialchars($nivel['id_nivel']) ?>">
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
                                <option value="" disabled selected>Selecciona Paralelo</option>
                                <?php foreach ($paralelos as $paralelo): ?>
                                <option value="<?= htmlspecialchars($paralelo['id_paralelo']) ?>">
                                    <?= htmlspecialchars($paralelo['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_subnivel">Subnivel: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-layer'></i></div>
                            </div>
                            <select class="form-control" id="id_subnivel" name="id_subnivel" required>
                                <option value="" disabled selected>Selecciona Subnivel</option>
                                <?php foreach ($subniveles as $subnivel): ?>
                                <option value="<?= htmlspecialchars($subnivel['id_subnivel']) ?>">
                                    <?= htmlspecialchars($subnivel['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_especialidad">Especialidad: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-briefcase'></i></div>
                            </div>
                            <select class="form-control" id="id_especialidad" name="id_especialidad">
                                <option value="" disabled selected>Selecciona Especialidad</option>
                                <?php foreach ($especialidades as $especialidad): ?>
                                <option value="<?= htmlspecialchars($especialidad['id_especialidad']) ?>">
                                    <?= htmlspecialchars($especialidad['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_jornada">Jornada: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-time'></i></div>
                            </div>
                            <select class="form-control" id="id_jornada" name="id_jornada">
                                <option value="" disabled selected>Selecciona Jornada</option>
                                <?php foreach ($jornadas as $jornada): ?>
                                <option value="<?= htmlspecialchars($jornada['id_jornada']) ?>">
                                    <?= htmlspecialchars($jornada['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="id_his_academico">Histórico Académico: <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class='bx bxs-bookmark'></i></div>
                            </div>
                            <select class="form-control" id="id_his_academico" name="id_his_academico">
                                <option value="" disabled selected>Selecciona Histórico Académico</option>
                                <?php foreach ($historiales as $historial): ?>
                                <option value="<?= htmlspecialchars($historial['id_his_academico']) ?>">
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
                                <option value="" disabled selected>Selecciona Estado</option>
                                <option value="A">Activo</option>
                            </select>
                        </div>
                    </div>

                    <div class="button-group mt-4">
                        <button type="button" class="btn btn-regresar"
                            onclick="location.href='http://localhost/sistema_notas/views/admin/curso_admin.php';">
                            <i class='bx bx-arrow-back'></i> Regresar
                        </button>
                        <button type="submit" class="btn btn-crear-usuario">
                            <i class='bx bx-save'></i> Crear Curso
                        </button>
                    </div>
                </div>
        </form>
    </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano. Todos los derechos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>