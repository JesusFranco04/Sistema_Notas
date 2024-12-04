<?php
session_start();
include '../../Crud/config.php';

// Función para mostrar alertas
function mostrar_alerta($mensaje, $tipo) {
    echo "<div class='alert alert-$tipo'>$mensaje</div>";
}

// Función para subir de nivel a un estudiante
function subir_nivel($id_estudiante, $id_nivel, $conn) {
    $nuevo_nivel = $id_nivel + 1;
    // Verificar si el nuevo nivel está dentro del rango permitido
    if ($nuevo_nivel > 6) {
        mostrar_alerta('No se puede subir de nivel. El estudiante ya está en el nivel máximo.', 'danger');
        return;
    }

    // Actualiza el nivel directamente en la tabla 'estudiante'
    $sql_subir_nivel = "UPDATE estudiante 
                        SET id_nivel = ? 
                        WHERE id_estudiante = ? 
                        AND id_nivel = ?";

    if ($stmt_subir_nivel = $conn->prepare($sql_subir_nivel)) {
        $stmt_subir_nivel->bind_param("iii", $nuevo_nivel, $id_estudiante, $id_nivel);
        $stmt_subir_nivel->execute();
        $stmt_subir_nivel->close();
    } else {
        mostrar_alerta('Error al intentar subir de nivel: ' . $conn->error, 'danger');
        $conn->close();
        exit();
    }
}

// Obtener datos para los filtros
$sql_niveles = "SELECT id_nivel, nombre FROM nivel WHERE estado = 'A'";
$sql_paralelos = "SELECT id_paralelo, nombre FROM paralelo WHERE estado = 'A'";
$sql_jornadas = "SELECT id_jornada, nombre FROM jornada WHERE estado = 'A'";
$sql_historiales = "SELECT id_his_academico, año FROM historial_academico WHERE estado = 'A'";

$nivelesResult = $conn->query($sql_niveles);
$paralelosResult = $conn->query($sql_paralelos);
$jornadasResult = $conn->query($sql_jornadas);
$historialesResult = $conn->query($sql_historiales);

// Procesamiento del formulario
$estudiantes = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Inicializar variables de filtro con valores predeterminados si no están presentes
    $id_his_academico = $_POST['id_his_academico'] ?? '';
    $id_nivel = $_POST['id_nivel'] ?? '';
    $id_paralelo = $_POST['id_paralelo'] ?? '';
    $id_jornada = $_POST['id_jornada'] ?? '';

    // Verificar que se han seleccionado todos los filtros
    if (
        (empty($id_his_academico) && !empty($id_nivel)) ||
        (empty($id_nivel) && !empty($id_his_academico)) ||
        (empty($id_paralelo) && !empty($id_his_academico)) ||
        (empty($id_jornada) && !empty($id_his_academico))
    ) {
        mostrar_alerta('Por favor, seleccione todos los filtros para una búsqueda precisa.', 'danger');
    } elseif (empty($id_his_academico) || empty($id_nivel) || empty($id_paralelo) || empty($id_jornada)) {
        mostrar_alerta('Por favor, seleccione todos los filtros.', 'danger');
    } else {
        // Consulta para obtener los estudiantes con los filtros proporcionados
        $sql_estudiantes = "
            SELECT e.id_estudiante, e.nombres, e.apellidos, m.nombre AS materia, c.estado_calificacion 
            FROM estudiante e
            JOIN calificacion c ON e.id_estudiante = c.id_estudiante
            JOIN materia m ON c.id_materia = m.id_materia
            WHERE e.id_his_academico = ? 
            AND e.id_nivel = ? 
            AND e.id_paralelo = ? 
            AND e.id_jornada = ?
            ORDER BY e.id_estudiante, m.nombre ASC";

        if ($stmt_estudiantes = $conn->prepare($sql_estudiantes)) {
            $stmt_estudiantes->bind_param("iiii", $id_his_academico, $id_nivel, $id_paralelo, $id_jornada);
            $stmt_estudiantes->execute();
            $result_estudiantes = $stmt_estudiantes->get_result();

            // Agrupar materias y calificaciones por estudiante
            while ($row = $result_estudiantes->fetch_assoc()) {
                $id_estudiante = $row['id_estudiante'];

                if (!isset($estudiantes[$id_estudiante])) {
                    $estudiantes[$id_estudiante] = [
                        'nombres' => $row['nombres'],
                        'apellidos' => $row['apellidos'],
                        'materias' => [],
                        'calificaciones' => [],
                    ];
                }

                $estudiantes[$id_estudiante]['materias'][] = $row['materia'];
                $estudiantes[$id_estudiante]['calificaciones'][] = $row['estado_calificacion'];
            }

            if (empty($estudiantes)) {
                mostrar_alerta('No existe ningún grupo de estudiantes con los filtros seleccionados.', 'danger');
            }

            $stmt_estudiantes->close();
        } else {
            mostrar_alerta('Error en la preparación de la consulta de estudiantes: ' . $conn->error, 'danger');
            $conn->close();
            exit();
        }
    }

    // Procesar la subida de nivel si se ha enviado el formulario con selección de estudiantes
    if (isset($_POST['submit_selected'])) {
        $ids_estudiantes = $_POST['estudiantes'] ?? [];

        if (empty($ids_estudiantes)) {
            mostrar_alerta('Debe seleccionar al menos un estudiante para subir de nivel.', 'danger');
        } else {
            foreach ($ids_estudiantes as $id_estudiante) {
                // Consultar el nivel actual del estudiante
                $sql_nivel_estudiante = "SELECT id_nivel FROM estudiante WHERE id_estudiante = ?";
                if ($stmt_nivel = $conn->prepare($sql_nivel_estudiante)) {
                    $stmt_nivel->bind_param("i", $id_estudiante);
                    $stmt_nivel->execute();
                    $result_nivel = $stmt_nivel->get_result();
                    $nivel_actual = $result_nivel->fetch_assoc()['id_nivel'];
                    $stmt_nivel->close();
                }

                // Verificar si el estudiante tiene materias con calificación 'R'
                $sql_calificaciones = "
                    SELECT c.estado_calificacion
                    FROM calificacion c
                    WHERE c.id_estudiante = ? 
                    AND c.id_his_academico = ?
                    AND c.estado_calificacion = 'A'";

                if ($stmt_calificaciones = $conn->prepare($sql_calificaciones)) {
                    $stmt_calificaciones->bind_param("ii", $id_estudiante, $id_his_academico);
                    $stmt_calificaciones->execute();
                    $result_calificaciones = $stmt_calificaciones->get_result();

                    if ($result_calificaciones->num_rows > 0) {
                        // Subir de nivel
                        subir_nivel($id_estudiante, $nivel_actual, $conn);
                        mostrar_alerta("El estudiante con ID $id_estudiante ha subido de nivel con éxito.", 'success');
                    } else {
                        mostrar_alerta("El estudiante con ID $id_estudiante no tiene todas las materias aprobadas.", 'danger');
                    }

                    $stmt_calificaciones->close();
                } else {
                    mostrar_alerta('Error en la preparación de la consulta de calificaciones: ' . $conn->error, 'danger');
                    $conn->close();
                    exit();
                }
            }
            // Deshabilitar el botón después de la acción
            echo "<script>document.getElementById('submit-btn').disabled = true;</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Nivel Académico | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        /* Estilos generales */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Contenedor principal */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        /* Título principal con franja roja */
        .header {
            background-color: #E62433;
            padding: 8px;
            border-radius: 8px;
            text-align: center;
        }

        .header h1 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Estilos de los formularios */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        select, input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            margin: 0;
            box-sizing: border-box;
            border: 2px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        select:focus, input[type="text"]:focus {
            border-color: #E62433;
        }

        /* Estilos de los botones */
        .button-group {
            text-align: right; /* Alineación a la derecha */
            margin-top: 20px;
        }

        button {
            background-color: #E62433;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            background-color: #c91e2b;
            transform: translateY(-2px);
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Estilos para las alertas */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #fff;
            text-align: center;
            font-weight: 600;
        }

        .alert-danger {
            background-color: #e74c3c;
        }

        .alert-success {
            background-color: #2ecc71;
        }

        .alert-warning {
            background-color: #f39c12;
            color: #fff;
        }

        /* Estilos de la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            font-size: 16px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #E62433;
            color: #fff;
            font-weight: 600;
        }

        td {
            background-color: #f9f9f9;
        }

        /* Estilos de los checkboxes */
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            vertical-align: middle;
        }

        /* Ajuste de los títulos secundarios */
        h2 {
            margin-top: 40px;
            margin-bottom: 20px;
            color: #E62433;
            font-weight: 700;
            text-align: left;
            border-bottom: 2px solid #ddd;
            padding-bottom: 8px;
        }
        footer {
        background-color: white; /* Color de fondo blanco */
        color: #737373; /* Color del texto en gris oscuro */
        text-align: center; /* Centrar el texto */
        padding: 20px 0; /* Espaciado interno vertical */
        width: 100%; /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Sombra más pronunciada */
        }

        footer p {
            margin: 0; /* Eliminar el margen de los párrafos */
        }
    </style>
</head>
<body>
    <?php include_once 'navbar_admin.php'; ?>
<div class="container">
    <div class="header">
        <h1>Subida de Nivel de Estudiantes</h1>
    </div>

    <?php if (isset($alerta)) { ?>
        <div class="alert <?php echo $alerta['tipo']; ?>">
            <?php echo $alerta['mensaje']; ?>
        </div>
        <?php unset($alerta); // Limpiar la alerta después de mostrarla ?>
    <?php } ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="id_his_academico">Año Histórico Académico:</label>
            <select name="id_his_academico" id="id_his_academico" required>
                <option value="">Seleccione un año</option>
                <?php while ($row = $historialesResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_his_academico']; ?>"><?php echo $row['año']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="id_nivel">Nivel:</label>
            <select name="id_nivel" id="id_nivel" required>
                <option value="">Seleccione un nivel</option>
                <?php while ($row = $nivelesResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_nivel']; ?>"><?php echo $row['nombre']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="id_paralelo">Paralelo:</label>
            <select name="id_paralelo" id="id_paralelo" required>
                <option value="">Seleccione un paralelo</option>
                <?php while ($row = $paralelosResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_paralelo']; ?>"><?php echo $row['nombre']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="id_jornada">Jornada:</label>
            <select name="id_jornada" id="id_jornada" required>
                <option value="">Seleccione una jornada</option>
                <?php while ($row = $jornadasResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_jornada']; ?>"><?php echo $row['nombre']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="button-group">
            <button type="submit">
                <i class='bx bx-search'></i> Buscar Estudiantes
            </button>
        </div>
    </form>

    <?php if (!empty($estudiantes)) { ?>
        <form method="POST" action="">
            <h2>Estudiantes</h2>
            <table>
                <thead>
                <tr>
                    <th>Seleccionar</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Materias</th>
                    <th>Calificaciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($estudiantes as $id_estudiante => $info) { ?>
                    <tr>
                        <td><input type="checkbox" name="estudiantes[]" value="<?php echo $id_estudiante; ?>"></td>
                        <td><?php echo $info['nombres']; ?></td>
                        <td><?php echo $info['apellidos']; ?></td>
                        <td><?php echo implode(', ', $info['materias']); ?></td>
                        <td><?php echo implode(', ', $info['calificaciones']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <div class="button-group">
                <button type="submit" id="submit-btn" name="submit_selected">
                    <i class='bx bx-up-arrow-alt'></i> Subir Nivel Seleccionados
                </button>
            </div>
        </form>
    <?php } ?>
</div>
</div>
<footer>
     <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.</p>
</footer>
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>
</html>

