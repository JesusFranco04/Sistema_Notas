<?php
session_start();

// Incluir archivo de configuración
include('../../Crud/config.php');

// Definir constantes para valores constantes
define('ROL_PROFESOR', 'Profesor');
define('ALERTA_ERROR', 'danger');
define('ALERTA_EXITO', 'success');

// Verificar autenticación y rol
if (!isset($_SESSION['cedula']) || $_SESSION['rol'] !== ROL_PROFESOR) {
    $_SESSION['mensaje'] = 'No estás autenticado o no tienes permisos para acceder.';
    $_SESSION['tipo_mensaje'] = ALERTA_ERROR;
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Función para mostrar alertas
function mostrar_alerta($mensaje, $tipo_alerta) {
    echo '<div class="alert alert-' . htmlspecialchars($tipo_alerta) . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($mensaje);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}

// Obtener id_periodo y id_curso
$id_periodo = filter_input(INPUT_GET, 'id_periodo', FILTER_VALIDATE_INT);
$id_curso = filter_input(INPUT_GET, 'id_curso', FILTER_VALIDATE_INT);

// Obtener los primeros tres períodos académicos
$periodos = [];
$sql_periodos = "SELECT id_periodo, nombre FROM periodo_academico ORDER BY id_periodo ASC LIMIT 3";
if ($stmt = $conn->prepare($sql_periodos)) {
    $stmt->execute();
    $periodos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    mostrar_alerta('Error en la consulta de períodos: ' . $conn->error, ALERTA_ERROR);
    exit();
}

// Asignar el primer período si no se seleccionó uno
if (empty($id_periodo) && !empty($periodos)) {
    $id_periodo = $periodos[0]['id_periodo'];
}

// Variables iniciales
$id_his_academico = $id_materia = null;
$nombre_materia = 'Materia no disponible';

// Obtener datos del curso
if ($id_curso) {
    $sql_curso = "SELECT c.id_curso, c.id_materia, h.año AS año_academico, h.id_his_academico
                  FROM curso c
                  JOIN historial_academico h ON c.id_his_academico = h.id_his_academico
                  WHERE c.id_curso = ?";
    if ($stmt_curso = $conn->prepare($sql_curso)) {
        $stmt_curso->bind_param("i", $id_curso);
        $stmt_curso->execute();
        $curso = $stmt_curso->get_result()->fetch_assoc();
        if ($curso) {
            $id_his_academico = $curso['id_his_academico'];
            $id_materia = $curso['id_materia'];
            $nombre_materia = $curso['año_academico']; // Asumí que la variable 'año_academico' es la que se quería mostrar.
        } else {
            mostrar_alerta("Curso no encontrado.", ALERTA_ERROR);
            exit();
        }
        $stmt_curso->close();
    } else {
        mostrar_alerta('Error en la consulta de curso: ' . $conn->error, ALERTA_ERROR);
        exit();
    }
} else {
    mostrar_alerta("No se ha proporcionado el id_curso.", ALERTA_ERROR);
    exit();
}

// Obtener nombre de la materia
if ($id_materia) {
    $sql_materia = "SELECT nombre FROM materia WHERE id_materia = ?";
    if ($stmt_materia = $conn->prepare($sql_materia)) {
        $stmt_materia->bind_param("i", $id_materia);
        $stmt_materia->execute();
        $materia = $stmt_materia->get_result()->fetch_assoc();
        if ($materia) {
            $nombre_materia = htmlspecialchars($materia['nombre']);
        } else {
            mostrar_alerta("Materia no encontrada.", ALERTA_ERROR);
            exit();
        }
        $stmt_materia->close();
    }
}

// Obtener estudiantes del curso
$sql_estudiantes = "SELECT id_estudiante, nombres, apellidos FROM estudiante WHERE id_his_academico = ? ORDER BY apellidos ASC";
$estudiantes = [];
if ($stmt_estudiantes = $conn->prepare($sql_estudiantes)) {
    $stmt_estudiantes->bind_param("i", $id_his_academico);
    $stmt_estudiantes->execute();
    $estudiantes = $stmt_estudiantes->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_estudiantes->close();
} else {
    mostrar_alerta('Error en la consulta de estudiantes: ' . $conn->error, ALERTA_ERROR);
    exit();
}

// Obtener las calificaciones
$sql_notas = "SELECT e.id_estudiante, r.nota1_primer_parcial, r.nota2_primer_parcial, r.examen_primer_parcial, 
              r.nota1_segundo_parcial, r.nota2_segundo_parcial, r.examen_segundo_parcial, 
              c.promedio_primer_quimestre, c.promedio_segundo_quimestre, c.nota_final, c.supletorio, c.estado_calificacion 
              FROM estudiante e
              JOIN registro_nota r ON e.id_estudiante = r.id_estudiante
              JOIN calificacion c ON e.id_estudiante = c.id_estudiante
              WHERE r.id_curso = ? AND r.id_periodo = ?";
$notas_estudiantes = [];
if ($stmt_notas = $conn->prepare($sql_notas)) {
    $stmt_notas->bind_param("ii", $id_curso, $id_periodo);
    $stmt_notas->execute();
    $result_notas = $stmt_notas->get_result();
    while ($row = $result_notas->fetch_assoc()) {
        $notas_estudiantes[$row['id_estudiante']] = $row;
    }
    $stmt_notas->close();
}

// Consulta para obtener las calificaciones finales
$sql_calificaciones = "SELECT e.id_estudiante, e.nombres, e.apellidos, c.promedio_primer_quimestre, c.promedio_segundo_quimestre, c.nota_final, c.supletorio, c.estado_calificacion 
                       FROM estudiante e 
                       JOIN calificacion c ON e.id_estudiante = c.id_estudiante 
                       WHERE c.id_curso = ? AND c.id_materia = ? AND c.id_his_academico = ?";
if ($stmt_calificaciones = $conn->prepare($sql_calificaciones)) {
    $stmt_calificaciones->bind_param("iii", $id_curso, $id_materia, $id_his_academico);
    $stmt_calificaciones->execute();
    $result_calificaciones = $stmt_calificaciones->get_result();
    $calificaciones = [];
    if ($result_calificaciones->num_rows > 0) {
        while ($row = $result_calificaciones->fetch_assoc()) {
            $calificaciones[$row['id_estudiante']] = $row;
        }
    }
    $stmt_calificaciones->close();
} else {
    mostrar_alerta('Error en la preparación de la consulta de calificaciones: ' . $conn->error, ALERTA_ERROR);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Visualización de Calificaciones | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
    /* Estilos CSS */
    body {
        background-color: #f8f9fa;
        margin: 0;
        min-height: 100vh;
        /* Asegura que el body ocupe al menos el 100% de la altura de la pantalla */
        display: flex;
        flex-direction: column;
        justify-content: center;
        /* Centra el contenido verticalmente */
    }

    /* Banner */
    .banner {
        background-color: #c61e1e;
        color: white;
        padding: 2rem;
        text-align: center;
        font-size: 1.8rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 2px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid #0052aa;
    }

    .container {
        background-color: #ffffff;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
        margin-top: 20px;
        margin-bottom: 45px;
        /* Espacio entre el contenedor y el footer */
        text-align: center;
        /* Centra el texto del contenedor */
    }

    h2 {
        color: #E62433;
    }

    .botones-accion {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }

    .btn-primary {
        background-color: #a21616;
        border-color: #a21616;
        color: white;
    }

    .btn-primary:hover {
        background-color: #8a1313;
        border-color: #8a1313;
        color: white;
    }

    .btn-secondary {
        background-color: #0052aa;
        border-color: #0052aa;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #062f63;
        border-color: #062f63;
        color: white;
    }

    .table-container {
        margin: 20px auto;
        padding: 15px;
        background-color: #ffffff;

        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Roboto', sans-serif;
        font-size: 15px;
        color: #333333;
        text-align: left;
    }

    .table thead {
        background-color: #b71c1c;
        color: #ffffff;
    }

    .table thead th {
        padding: 15px;
        font-weight: bold;
        font-size: 16px;
        text-transform: uppercase;
        border-bottom: 3px solid #811b1b;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .table tbody tr {
        border-bottom: 1px solid #ddd;
        transition: background-color 0.3s ease;
    }

    .table tbody tr:nth-of-type(odd) {
        background-color: #fdecea;
    }

    .table tbody tr:nth-of-type(even) {
        background-color: #fff5f5;
    }

    .table tbody tr:hover {
        background-color: #ffebee;
        transform: scale(1.02);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .table td,
    .table th {
        padding: 12px;
        border-right: 1px solid #ddd;
    }

    .table td:last-child,
    .table th:last-child {
        border-right: none;
    }

    .partial-header {
        background-color: #f1f1f1;
        color: #a21616;
        text-align: center;
        font-weight: bold;
        font-size: 15px;
        padding: 10px;
    }

    th,
    td {
        text-align: center;
        /* Centra el texto horizontalmente */
        vertical-align: middle;
        /* Centra el contenido verticalmente */
        white-space: pre-line;
        /* Respeta los saltos de línea */
        padding: 5px;
        /* Espaciado adicional para mejorar la legibilidad */
    }

    /* Estilo para los porcentajes */
    .porcentaje {
        font-size: 0.8em;
        /* Hace el texto más pequeño */
        color: white;
        /* Cambia el color a blanco */
    }

    /* Evita la selección de las celdas de N° y Nombres */
    .no-select {
        pointer-events: none;
        /* Desactiva la interacción de la celda */
    }

    /* Evitar que las celdas del encabezado sean seleccionables */
    .table thead th {
        pointer-events: none;
        /* Desactiva la selección de las celdas del encabezado */
    }

    /* Los efectos siguen activos en las celdas de las notas */
    .table td:hover,
    .table th:hover {
        background-color: #a21616;
        color: #f1f1f1;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Animación para las filas seleccionables */
    .table tbody tr.selected {
        background-color: #f8d7da;
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease-in-out;
    }

    footer {
        border-top: 3px solid #073b73;
        background-color: #ad0f0f;
        color: white;
        text-align: center;
        padding: 20px 0;
        width: 100%;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        margin-top: auto;
    }

    footer p {
        margin: 0;
    }

    .alert {
        padding: 15px;
        border: 1px solid transparent;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 14px;
        display: none;
    }

    .alert.show {
        display: block;
    }

    .alert-error {
        color: #d32f2f;
        background-color: #fdecea;
        border-color: #d32f2f;
    }

    .alert-success {
        color: #388e3c;
        background-color: #e8f5e9;
        border-color: #388e3c;
    }
    </style>
</head>

<body>
    <!-- Banner -->
    <div class="banner">
        Sistema de Gestión UEBF
    </div>

    <div class="container mt-5">
        <h2>Visualización de Calificaciones - Curso: <?php echo htmlspecialchars($id_curso); ?>, Materia:
            <?php echo htmlspecialchars($nombre_materia); ?></h2>

        <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?> show">
            <?php echo $_SESSION['mensaje']; ?>
        </div>
        <?php unset($_SESSION['mensaje']); unset($_SESSION['tipo_mensaje']); endif; ?>

        <!-- Tabla de Calificaciones -->
        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="partial-header"></th>
                        <th class="partial-header"></th>
                        <th class="partial-header"></th>
                        <!-- Sección Primer Quimestre -->
                        <th colspan="6" class="partial-header">Período: Primer Quimestre</th>
                        <th colspan="6" class="partial-header">Período: Segundo Quimestre</th>
                        <th colspan="5" class="partial-header">Nota Final</th>
                    </tr>
                    <tr>
                        <th class="no-select">N°</th>
                        <th class="no-select">Nombres</th>
                        <th class="no-select">Apellidos</th>
                        <!-- Primer Quimestre -->
                        <th>
                            1er Parcial<br> -<br> Nota 1<br> <span class="porcentaje">(35%)</span>
                        </th>
                        <th>
                            1er Parcial<br> -<br> Nota 2<br> <span class="porcentaje">(35%)</span>
                        </th>
                        <th>
                            Examen 1er Parcial<br> -<br> Examen Final<br> <span class="porcentaje">(30%)</span>
                        </th>
                        <th>
                            2do Parcial<br> -<br> Nota 1<br> <span class="porcentaje">(35%)</span>
                        </th>
                        <th>
                            2do Parcial<br> -<br> Nota 2<br> <span class="porcentaje">(35%)</span>
                        </th>
                        <th>
                            Examen 2do Parcial<br> -<br> Examen Final<br> <span class="porcentaje">(30%)</span>
                        </th>

                        <!-- Segundo Quimestre -->
                        <th>
                            1er Parcial<br> -<br> Nota 1<br> <span class="porcentaje">(35%)</span>
                        </th>
                        <th>
                            1er Parcial<br> -<br> Nota 2<br> <span class="porcentaje">(35%)</span>
                        </th>
                        <th>
                            Examen 1er Parcial<br> -<br> Examen Final<br> <span class="porcentaje">(30%)</span>
                        </th>
                        <th>
                            2do Parcial<br> -<br> Nota 1<br> <span class="porcentaje">(35%)</span>
                        </th>
                        <th>
                            2do Parcial<br> -<br> Nota 2<br> <span class="porcentaje">(35%)</span>
                        </th>
                        <th>
                            Examen 2do Parcial<br> -<br> Examen Final<br> <span class="porcentaje">(30%)</span>
                        </th>
                        <th>
                            1er Quimestre<br> -<br> Promedio
                        </th>
                        <th>
                            2do Quimestre<br> -<br> Promedio
                        </th>
                        <th>Promedio Final</th>
                        <th>Supletorio</th>
                        <th>Estado Calificación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $index = 1; ?>
                    <?php foreach ($estudiantes as $estudiante): ?>
                    <tr>
                        <td class="no-select"><?php echo $index++; ?></td>
                        <td class="no-select"><?php echo htmlspecialchars($estudiante['nombres']); ?></td>
                        <td class="no-select"><?php echo htmlspecialchars($estudiante['apellidos']); ?></td>

                        <!-- Primer Quimestre -->
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota1_primer_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota2_primer_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['examen_primer_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota1_segundo_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota2_segundo_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['examen_segundo_parcial'] ?? '-'); ?>
                        </td>

                        <!-- Segundo Quimestre -->
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota1_primer_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota2_primer_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['examen_primer_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota1_segundo_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota2_segundo_parcial'] ?? '-'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['examen_segundo_parcial'] ?? '-'); ?>
                        </td>

                        <!-- Promedio y Nota Final -->
                        <?php 
                        // Accedemos a los datos de calificación para el estudiante actual
                        $calificacion = $calificaciones[$estudiante['id_estudiante']] ?? [];
                        ?>
                        <td><?php echo isset($calificacion['promedio_primer_quimestre']) ? htmlspecialchars($calificacion['promedio_primer_quimestre']) : '-'; ?>
                        </td>
                        <td><?php echo isset($calificacion['promedio_segundo_quimestre']) ? htmlspecialchars($calificacion['promedio_segundo_quimestre']) : '-'; ?>
                        </td>
                        <td><?php echo isset($calificacion['nota_final']) ? htmlspecialchars($calificacion['nota_final']) : '-'; ?>
                        </td>
                        <td><?php echo isset($calificacion['supletorio']) ? htmlspecialchars($calificacion['supletorio']) : '-'; ?>
                        </td>
                        <td><?php echo isset($calificacion['estado_calificacion']) ? ($calificacion['estado_calificacion'] === 'A' ? 'Aprobado' : 'Reprobado') : '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Botones de acción -->
        <div class="botones-accion">
            <button type="button" class="btn btn-secondary"
                onclick="location.href='ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>';">Regresar</button>
            <a href="http://localhost/sistema_notas/views/profe/curso_profe.php" class="btn btn-primary">Cerrar</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

    <!-- JavaScript Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
    // JavaScript para alternar la selección de filas y celdas de notas
    const rows = document.querySelectorAll('.table tbody tr');

    rows.forEach(row => {
        row.addEventListener('click', (event) => {
            // Verificar si la celda clickeada no es parte del encabezado o de las celdas no seleccionables
            if (!event.target.closest('th.no-select') && event.target.tagName !== 'TH') {
                // Remueve la clase "selected" de todas las filas
                rows.forEach(r => r.classList.remove('selected'));
                // Añade la clase "selected" a la fila clickeada
                row.classList.add('selected');
            }
        });
    });
    </script>

</body>

</html>