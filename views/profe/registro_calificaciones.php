<?php
session_start();

// Incluir el archivo de conexión
include('../../Crud/config.php'); // Ruta absoluta

// Verificar si el usuario ha iniciado sesión y si su rol es "Profesor"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Profesor'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Variables para mensajes
$mensaje = '';
$tipo_alerta = '';

// Función para mostrar alertas
function mostrar_alerta($mensaje, $tipo_alerta) {
    echo '<div class="alert alert-' . htmlspecialchars($tipo_alerta) . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($mensaje);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}

// Obtener el id_periodo desde la sesión o URL
$id_periodo = isset($_GET['id_periodo']) ? filter_var($_GET['id_periodo'], FILTER_VALIDATE_INT) : null;

// Obtener el id_curso desde la URL, si está disponible
$id_curso = isset($_GET['id_curso']) ? filter_var($_GET['id_curso'], FILTER_VALIDATE_INT) : null;

// Si no se proporciona el id_periodo, obtener el período académico activo
if ($id_periodo === null) {
    $sql_periodo_activo = "SELECT id_periodo FROM periodo_academico WHERE estado IN ('1', 'A') LIMIT 1";
    if ($stmt_periodo_activo = $conn->prepare($sql_periodo_activo)) {
        $stmt_periodo_activo->execute();
        $result_periodo_activo = $stmt_periodo_activo->get_result();
        if ($result_periodo_activo->num_rows > 0) {
            $periodo_activo = $result_periodo_activo->fetch_assoc();
            $id_periodo = $periodo_activo['id_periodo'];
        } else {
            mostrar_alerta("No se ha encontrado un período académico activo.", 'danger');
            $conn->close();
            exit();
        }
        $stmt_periodo_activo->close();
    } else {
        mostrar_alerta('Error en la preparación de la consulta de período activo: ' . $conn->error, 'danger');
        $conn->close();
        exit();
    }
}

// Inicializa $id_his_academico y $id_materia
$id_his_academico = null;
$id_materia = null;

// Obtener el año académico para el id_curso y luego el id_his_academico
if ($id_curso) {
    $sql_curso = "SELECT c.id_curso, c.id_materia, h.año AS año_academico, h.id_his_academico
                  FROM curso c
                  JOIN historial_academico h ON c.id_his_academico = h.id_his_academico
                  WHERE c.id_curso = ?";
    if ($stmt_curso = $conn->prepare($sql_curso)) {
        $stmt_curso->bind_param("i", $id_curso);
        $stmt_curso->execute();
        $result_curso = $stmt_curso->get_result();
        $curso = $result_curso->fetch_assoc();
        if ($curso) {
            $año_academico = $curso['año_academico'];
            $id_his_academico = $curso['id_his_academico'];
            $id_materia = isset($curso['id_materia']) ? $curso['id_materia'] : null;
        } else {
            mostrar_alerta("Curso no encontrado.", 'danger');
            $conn->close();
            exit();
        }
        $stmt_curso->close();
    } else {
        mostrar_alerta('Error en la preparación de la consulta de curso: ' . $conn->error, 'danger');
        $conn->close();
        exit();
    }
} else {
    // Si no se proporciona id_curso, obtener un historial académico basado en el año académico
    if (isset($año_academico)) {
        $sql_his_academico = "SELECT id_his_academico FROM historial_academico WHERE año = ? AND estado = 'A' LIMIT 1";
        if ($stmt_his_academico = $conn->prepare($sql_his_academico)) {
            $stmt_his_academico->bind_param("s", $año_academico);
            $stmt_his_academico->execute();
            $result_his_academico = $stmt_his_academico->get_result();
            $his_academico = $result_his_academico->fetch_assoc();
            if ($his_academico) {
                $id_his_academico = $his_academico['id_his_academico'];
            } else {
                mostrar_alerta("Historial académico no encontrado para el año académico: " . htmlspecialchars($año_academico), 'danger');
                $conn->close();
                exit();
            }
            $stmt_his_academico->close();
        } else {
            mostrar_alerta('Error en la preparación de la consulta de historial académico: ' . $conn->error, 'danger');
            $conn->close();
            exit();
        }
    } else {
        mostrar_alerta("No se ha proporcionado el año académico.", 'danger');
        $conn->close();
        exit();
    }
}

// Verificar que id_curso sea válido si no se ha definido antes
if (!$id_curso) {
    $sql_curso_predeterminado = "SELECT id_curso, id_materia, id_his_academico FROM curso WHERE id_his_academico = ?";
    if ($stmt_curso_predeterminado = $conn->prepare($sql_curso_predeterminado)) {
        $stmt_curso_predeterminado->bind_param("i", $id_his_academico);
        $stmt_curso_predeterminado->execute();
        $result_curso_predeterminado = $stmt_curso_predeterminado->get_result();
        if ($result_curso_predeterminado->num_rows > 0) {
            $curso_predeterminado = $result_curso_predeterminado->fetch_assoc();
            $id_curso = $curso_predeterminado['id_curso'];
            $id_his_academico = $curso_predeterminado['id_his_academico'];
            $id_materia = isset($curso_predeterminado['id_materia']) ? $curso_predeterminado['id_materia'] : null;
        } else {
            mostrar_alerta("Lo sentimos, no es posible ingresar calificaciones para este curso porque el Año Lectivo ya ha sido cerrado. Por favor, intente nuevamente utilizando el Año Lectivo actual.", 'danger');
            $conn->close();
            exit();
        }
        $stmt_curso_predeterminado->close();
    } else {
        mostrar_alerta('Error en la preparación de la consulta de curso predeterminado: ' . $conn->error, 'danger');
        $conn->close();
        exit();
    }
}

// Consulta para obtener el nombre del período activo
$estado_periodo = ($id_periodo == 3) ? 'A' : '1';
$sql_nombre_periodo_activo = "SELECT nombre FROM periodo_academico WHERE id_periodo = ? AND estado = ? LIMIT 1";
if ($stmt_nombre_periodo_activo = $conn->prepare($sql_nombre_periodo_activo)) {
    $stmt_nombre_periodo_activo->bind_param("is", $id_periodo, $estado_periodo);
    $stmt_nombre_periodo_activo->execute();
    $result_nombre_periodo_activo = $stmt_nombre_periodo_activo->get_result();
    $periodo_activo = $result_nombre_periodo_activo->fetch_assoc();
    $nombre_periodo = ($periodo_activo) ? htmlspecialchars($periodo_activo['nombre']) : 'Período no activo o no encontrado';
    $stmt_nombre_periodo_activo->close();
} else {
    mostrar_alerta('Error en la preparación de la consulta de nombre del período: ' . $conn->error, 'danger');
    $conn->close();
    exit();
}

// Consulta para obtener el nombre de la materia
if ($id_materia !== null) {
    $sql_materia = "SELECT nombre FROM materia WHERE id_materia = ?";
    if ($stmt_materia = $conn->prepare($sql_materia)) {
        $stmt_materia->bind_param("i", $id_materia);
        $stmt_materia->execute();
        $result_materia = $stmt_materia->get_result();
        $materia = $result_materia->fetch_assoc();
        $nombre_materia = ($materia) ? htmlspecialchars($materia['nombre']) : 'Materia no encontrada';
        $stmt_materia->close();
    } else {
        $mensaje = 'Error en la preparación de la consulta de materia: ' . $conn->error;
        $tipo_alerta = 'danger';
        mostrar_alerta($mensaje, $tipo_alerta);
        $conn->close();
        exit();
    }
} else {
    $nombre_materia = 'Materia no disponible'; // Valor por defecto si id_materia no está definido
}

// Obtener los estudiantes
$sql_estudiantes = "SELECT id_estudiante, nombres, apellidos FROM estudiante WHERE id_his_academico = ? ORDER BY apellidos ASC";
if ($stmt_estudiantes = $conn->prepare($sql_estudiantes)) {
    $stmt_estudiantes->bind_param("i", $id_his_academico);
    $stmt_estudiantes->execute();
    $result_estudiantes = $stmt_estudiantes->get_result();
    $estudiantes = $result_estudiantes->fetch_all(MYSQLI_ASSOC);
    $stmt_estudiantes->close();
} else {
    mostrar_alerta('Error en la preparación de la consulta de estudiantes: ' . $conn->error, 'danger');
    $conn->close();
    exit();
}

// Consulta para obtener las notas
$sql_notas = "SELECT e.id_estudiante, e.nombres, e.apellidos, r.id_periodo, r.nota1_primer_parcial, r.nota2_primer_parcial, r.examen_primer_parcial, r.nota1_segundo_parcial, r.nota2_segundo_parcial, r.examen_segundo_parcial, c.promedio_primer_quimestre, c.promedio_segundo_quimestre, c.nota_final, c.supletorio, c.estado_calificacion 
              FROM estudiante e 
              JOIN registro_nota r ON e.id_estudiante = r.id_estudiante 
              JOIN calificacion c ON e.id_estudiante = c.id_estudiante AND r.id_curso = c.id_curso AND r.id_materia = c.id_materia AND r.id_his_academico = c.id_his_academico 
              WHERE r.id_curso = ? AND r.id_periodo = ?";
if ($stmt_notas = $conn->prepare($sql_notas)) {
    $stmt_notas->bind_param("ii", $id_curso, $id_periodo);
    $stmt_notas->execute();
    $result_notas = $stmt_notas->get_result();
    $notas_estudiantes = [];
    while ($row = $result_notas->fetch_assoc()) {
        $notas_estudiantes[$row['id_estudiante']] = $row;
    }
    $stmt_notas->close();
} else {
    mostrar_alerta('Error en la preparación de la consulta de notas: ' . $conn->error, 'danger');
    $conn->close();
    exit();
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
    mostrar_alerta('Error en la preparación de la consulta de calificaciones: ' . $conn->error, 'danger');
    $conn->close();
    exit();
}

// Cerrar la conexión
$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Calificaciones | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            margin-bottom: 80px; /* Asegura que el contenido no quede oculto detrás del footer fijo */
        }
        .banner {
            background-color: #E62433;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #003366;
        }
        .container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            text-align: center; /* Centra el texto del contenedor */
        }
        h2 {
            color: #E62433;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #e9faff; /* Color azul claro para filas impares */
        }
        .table-striped tbody tr:nth-of-type(even) {
            background-color: #f4f9fc; /* Color azul muy claro para filas pares */
        }
        .btn-primary {
            background-color: #E62433;
            border-color: #E62433;
        }
        .btn-primary:hover {
            background-color: #c72c24;
            border-color: #c72c24;
        }
        .btn-danger {
            background-color: #E62433; /* Rojo */
            border-color: #E62433;
        }
        .btn-danger:hover {
            background-color: #c72c24;
            border-color: #c72c24;
        }
        .btn-success {
            background-color: #4CAF50; /* Verde */
            border-color: #4CAF50;
        }
        .btn-success:hover {
            background-color: #45a049;
            border-color: #45a049;
        }
        .btn-secondary {
            background-color: #003366; /* Gris */
            border-color: #003366;
        }
        .btn-secondary:hover {
            background-color: #434b52;
            border-color: #434b52;
        }
	.btn-regresar, .btn-enviar {
            background-color: #003366; /* Azul */
            border-color: #003366;
            color: white; /* Color del texto */
        }
        .btn-regresar:hover, .btn-enviar:hover {
            background-color: #002244; /* Azul más oscuro para el hover */
            border-color: #001122;
        }
        .partial-header {
            text-align: center;
            color: #E62433;
            font-weight: bold;
        }
        .small-text {
            font-size: 0.8rem;
        }
        .botones-accion {
            margin-top: 1rem;
            text-align: right;
        }
        .table th, .table td {
            padding: 0.75rem; /* Ajuste de espaciado para mayor consistencia */
        }
        .input-number {
            width: 100%;
            box-sizing: border-box;
        }
        footer {
            border-top: 3px solid #003366; /* Borde en la parte superior */
            background-color: #E62433;
            color: white; 
            text-align: center; /* Centrar el texto */
            padding: 20px 0; /* Espaciado interno vertical */
            width: 100%; /* Ancho completo */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Sombra más pronunciada */
            position: fixed;
            bottom: 0;
        }
        footer p {
            margin: 0; /* Eliminar el margen de los párrafos */
        }
        /* Estilos para scrollbars en la tabla */
        .table-container {
            overflow-x: auto; /* Scroll horizontal */
            overflow-y: auto; /* Scroll vertical */
        }
        .alert {
            display: none; /* Ocultar alerta por defecto */
        }
        .alert.show {
            display: block; /* Mostrar alerta cuando sea necesario */
        }
    </style>
</head>
<body>
    <!-- Banner -->
    <div class="banner">
        Sistema de Gestión UEBF
    </div>

    <div class="container mt-5">
        <h2>Registro de Calificaciones - Curso: <?php echo htmlspecialchars($id_curso); ?>, Materia: <?php echo htmlspecialchars($nombre_materia); ?></h2>
        <h3 class="partial-header">Período: <?php echo htmlspecialchars($nombre_periodo); ?></h3>

        <div class="alert alert-<?php echo isset($_SESSION['tipo_mensaje']) ? htmlspecialchars($_SESSION['tipo_mensaje']) : ''; ?> alert-dismissible fade show <?php echo isset($_SESSION['mensaje']) ? 'show' : ''; ?>" role="alert">
            <?php if (isset($_SESSION['mensaje']) && isset($_SESSION['tipo_mensaje'])): ?>
                <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['mensaje']); ?>
                <?php unset($_SESSION['tipo_mensaje']); ?>
            <?php endif; ?>
        </div>

        <form action="procesar_calificaciones.php" method="post">
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($id_curso); ?>">
            <input type="hidden" name="id_materia" value="<?php echo htmlspecialchars($id_materia); ?>">
            <input type="hidden" name="id_his_academico" value="<?php echo htmlspecialchars($id_his_academico); ?>">
            <input type="hidden" name="id_periodo" value="<?php echo htmlspecialchars($id_periodo); ?>">
            <div class="table-container">
	            <table class="table table-striped">
	                <thead>
	                    <tr>
	                        <th></th>
	                        <th></th>
	                        <th></th>
	                        <?php if ($id_periodo == 1 || $id_periodo == 2): ?>
	                            <th colspan="3" class="partial-header">1er Parcial</th>
	                            <th colspan="3" class="partial-header">2do Parcial</th>
	                        <?php elseif ($id_periodo == 3): ?>
	                            <th colspan="5" class="partial-header">Evaluaciones Finales</th>
	                        <?php endif; ?>
	                    </tr>
	                    <tr>
	                        <th>N°</th>
	                        <th>Nombre</th>
	                        <th>Apellido</th>
	                        <?php if ($id_periodo == 1 || $id_periodo == 2): ?>
	                            <th>Nota 1<span class="small-text"> (35%)</span></th>
	                            <th>Nota 2<span class="small-text"> (35%)</span></th>
	                            <th>1er Examen<span class="small-text"> (30%)</span></th>
	                            <th>Nota 1<span class="small-text"> (35%)</span></th>
	                            <th>Nota 2<span class="small-text"> (35%)</span></th>
	                            <th>2do Examen<span class="small-text"> (30%)</span></th>
	                        <?php elseif ($id_periodo == 3): ?>
	                            <th>Promedio 1er Q.</th>
	                            <th>Promedio 2do Q.</th>
	                            <th>Nota Final</th>
	                            <th>Supletorio</th>
	                            <th>Estado</th>
	                        <?php endif; ?>
	                    </tr>
	                </thead>
	                <tbody>
	                <?php $i = 1; foreach ($estudiantes as $estudiante): ?>
	                    <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($estudiante['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($estudiante['apellidos']); ?></td>
                        <?php if ($id_periodo == 1 || $id_periodo == 2): ?>
                            <td><input type="number" step="0.1" name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota1_primer_parcial]" value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota1_primer_parcial']) : ''; ?>"></td>
                            <td><input type="number" step="0.1" name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota2_primer_parcial]" value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota2_primer_parcial']) : ''; ?>"></td>
                            <td><input type="number" step="0.1" name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][examen_primer_parcial]" value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['examen_primer_parcial']) : ''; ?>"></td>
                            <td><input type="number" step="0.1" name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota1_segundo_parcial]" value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota1_segundo_parcial']) : ''; ?>"></td>
                            <td><input type="number" step="0.1" name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota2_segundo_parcial]" value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota2_segundo_parcial']) : ''; ?>"></td>
                            <td><input type="number" step="0.1" name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][examen_segundo_parcial]" value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['examen_segundo_parcial']) : ''; ?>"></td>
                        <?php elseif ($id_periodo == 3): ?>
                            <td><input type="number" step="0.1" name="calificaciones[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][promedio_primer_quimestre]" value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['promedio_primer_quimestre']) : ''; ?>" readonly></td>
                            <td><input type="number" step="0.1" name="calificaciones[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][promedio_segundo_quimestre]" value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['promedio_segundo_quimestre']) : ''; ?>" readonly></td>
                            <td><input type="number" step="0.1" name="calificaciones[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota_final]" value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['nota_final']) : ''; ?>" readonly></td>
                            <?php
                                $estado = isset($calificaciones[$estudiante['id_estudiante']]) ? $calificaciones[$estudiante['id_estudiante']]['estado_calificacion'] : 'A';
                                $supletorio_habilitado = ($estado === 'R') ? '' : 'disabled';
                            ?>
                            <td><input type="number" step="0.01" name="calificaciones[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][supletorio]" value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['supletorio']) : ''; ?>" <?php echo $supletorio_habilitado; ?>></td>
                            <td><?php echo $estado === 'A' ? 'Aprobado' : 'Reprobado'; ?></td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Botones de acción -->
            <div class="botones-accion">
                    <?php if ($id_periodo == 1 || $id_periodo == 2): ?>
                        <!-- Botones para id_periodo = 1 o 2 -->
                        <button type="button" class="btn btn-secondary" onclick="location.href='ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>';">Regresar</button>
                        <button type="submit" class="btn btn-primary" name="accion" value="eliminar">Eliminar</button>
                        <button type="submit" class="btn btn-success" name="accion" value="guardar">Guardar</button>
                        <button type="button" class="btn btn-primary" onclick="redirigirSiguiente()">Siguiente</button>
                    <?php elseif ($id_periodo == 3): ?>
                        <!-- Botones para id_periodo = 3 -->
                        <button type="button" class="btn btn-regresar" onclick="redirigirRegresar()">Regresar</button>
                        <button type="submit" class="btn btn-success" name="accion" value="guardar_supletorio">Guardar Supletorio</button>
                        <a href="http://localhost/sistema_notas/views/profe/curso_profe.php" class="btn btn-primary">Finalizar</a>
                    <?php endif; ?>
            </div>
        </form>
    </div>
</div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
function redirigirSiguiente() {
    var idCurso = <?php echo json_encode($id_curso); ?>;
    window.location.href = `http://localhost/sistema_notas/views/profe/registro_calificaciones.php?id_periodo=3&id_curso=${idCurso}`;
}

function redirigirRegresar() {
    var idPeriodo = <?php echo json_encode($id_periodo); ?>;
    var idCurso = <?php echo json_encode($id_curso); ?>;
    window.location.href = `http://localhost/sistema_notas/views/profe/registro_calificaciones.php?id_curso=${idCurso}`;
}
    </script>
</body>
</html>
