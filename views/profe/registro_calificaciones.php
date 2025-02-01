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
        /* Alinea los botones a la derecha */
        gap: 15px;
        /* Espacio estándar entre los botones */
    }

    .btn-primary,
    .btn-danger,
    .btn-success,
    .btn-secondary,
    .btn-regresar,
    .btn-enviar {
        /* Aseguramos que todos los botones tengan el mismo estilo visual y espaciado */
        margin: 0;
        /* Elimina márgenes extra */
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

    .btn-danger {
        background-color: #E62433;
        /* Rojo */
        border-color: #E62433;
    }

    .btn-danger:hover {
        background-color: #c72c24;
        border-color: #c72c24;
    }

    .btn-success {
        background-color: #23650f;
        /* Verde */
        border-color: #23650f;
        color: white;
    }

    .btn-success:hover {
        background-color: #1c560c;
        /* Verde */
        border-color: #1c560c;
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

    .btn-regresar,
    .btn-enviar {
        background-color: #0052aa;
        /* Azul */
        border-color: #0052aa;
        color: white;
        /* Color del texto */
    }

    .btn-regresar:hover,
    .btn-enviar:hover {
        background-color: #062f63;
        /* Azul más oscuro para el hover */
        border-color: #062f63;
        color: white;
    }

    /* Contenedor de la tabla */
    .table-container {
        margin: 20px auto;
        padding: 15px;
        background-color: #ffffff;
        /* Fondo blanco */
        border-radius: 12px;
        /* Bordes redondeados */
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        /* Sombra moderna */
        overflow-x: auto;
        /* Permitir desplazamiento horizontal en pantallas pequeñas */
    }

    /* Tabla */
    .table {
        width: 100%;
        border-collapse: collapse;
        /* Sin espacio entre celdas */
        font-family: 'Roboto', sans-serif;
        /* Fuente moderna */
        font-size: 15px;
        color: #333333;
        /* Color del texto */
        text-align: left;
    }

    /* Encabezado */
    .table thead {
        background-color: #b71c1c;
        /* Rojo oscuro elegante */
        color: #ffffff;
        /* Texto blanco */
    }

    .table thead th {
        padding: 15px;
        font-weight: bold;
        font-size: 16px;
        text-transform: uppercase;
        border-bottom: 3px solid #811b1b;
        /* Línea inferior */
        border-right: 1px solid #811b1b;
        /* Separador lateral */
        position: sticky;
        /* Encabezado fijo */
        top: 0;
        z-index: 2;
        white-space: nowrap;
        /* Evitar que el texto se corte */
    }

    /* Filas del cuerpo */
    .table tbody tr {
        border-bottom: 1px solid #ddd;
        /* Línea divisoria sutil */
        transition: background-color 0.3s ease;
    }

    .table tbody tr:nth-of-type(odd) {
        background-color: #fdecea;
        /* Rojo muy claro */
    }

    .table tbody tr:nth-of-type(even) {
        background-color: #fff5f5;
        /* Rojo clarísimo */
    }

    /* Efecto hover */
    .table tbody tr:hover {
        background-color: #ffebee;
        /* Fondo destacado */
        transform: scale(1.02);
        /* Ligeramente más grande */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        /* Sombras ligeras */
    }

    /* Celdas */
    .table td,
    .table th {
        padding: 12px;
        border-right: 1px solid #ddd;
        /* Separador lateral */
        white-space: nowrap;
        /* Evitar cortes de texto */
    }

    .table td:last-child,
    .table th:last-child {
        border-right: none;
        /* Sin borde en la última columna */
    }

    /* Encabezados parciales */
    .partial-header {
        background-color: #b71c1c;
        /* Rojo elegante */
        color: #ffffff;
        text-align: center;
        font-weight: bold;
        font-size: 24px;
        padding: 10px;

    }

    th {
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
        font-size: 0.9em;
        /* Hace el texto más pequeño */
        color: white;
        /* Cambia el color a blanco */
    }

    footer {
        border-top: 3px solid #073b73;
        /* Borde en la parte superior */
        background-color: #ad0f0f;
        color: white;
        text-align: center;
        padding: 20px 0;
        width: 100%;
        /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        /* Sombra más pronunciada */
        margin-top: auto;
        /* Empuja el footer hacia el fondo */
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }

    /* Estilos para scrollbars en la tabla */
    .table-container {
        overflow-x: auto;
        /* Scroll horizontal */
        overflow-y: auto;
        /* Scroll vertical */
    }

    .alert {
        padding: 15px;
        border: 1px solid transparent;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert-error {
        color: #d32f2f;
        /* Rojo más moderno */
        background-color: #fdecea;
        /* Fondo rojo claro */
        border-color: #d32f2f;
    }

    .alert-success {
        color: #388e3c;
        /* Verde más moderno */
        background-color: #e8f5e9;
        /* Fondo verde claro */
        border-color: #388e3c;
    }

    .alert-warning {
        color: #f57c00;
        /* Naranja más vibrante */
        background-color: #fff4e5;
        /* Fondo naranja claro */
        border-color: #f57c00;
    }

    .alert {
        display: none;
        /* Ocultar alerta por defecto */
    }

    .alert.show {
        display: block;
        /* Mostrar alerta cuando sea necesario */
    }
    </style>
</head>

<body>
    <!-- Banner -->
    <div class="banner">
        Sistema de Gestión UEBF
    </div>

    <div class="container mt-5">
        <h2>Registro de Calificaciones - Curso: <?php echo htmlspecialchars($id_curso); ?>, Materia:
            <?php echo htmlspecialchars($nombre_materia); ?></h2>
        <h3 class="partial-header">Período: <?php echo htmlspecialchars($nombre_periodo); ?></h3>

        <div class="alert alert-<?php echo isset($_SESSION['tipo_mensaje']) ? htmlspecialchars($_SESSION['tipo_mensaje']) : ''; ?> alert-dismissible fade show <?php echo isset($_SESSION['mensaje']) ? 'show' : ''; ?>"
            role="alert">
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
                            <th colspan="3" class="partial-header">Primer Parcial</th>
                            <th colspan="3" class="partial-header">Segundo Parcial</th>
                            <?php elseif ($id_periodo == 3): ?>
                            <th colspan="5" class="partial-header">Evaluaciones Finales</th>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <th>N°</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <?php if ($id_periodo == 1 || $id_periodo == 2): ?>
                            <th>Nota 1<br> <span class="porcentaje">(35%)</span></th>
                            <th>Nota 2<br> <span class="porcentaje">(35%)</span></th>
                            <th>1er Examen Final<br> <span class="porcentaje">(30%)</span></th>
                            <th>Nota 1<br> <span class="porcentaje">(35%)</span></th>
                            <th>Nota 2<br> <span class="porcentaje">(35%)</span></th>
                            <th>2do Examen Final<br> <span class="porcentaje">(30%)</span></th>
                            <?php elseif ($id_periodo == 3): ?>
                            <th>1er Quimestre<br> <span class="porcentaje">Promedio</span></th>
                            <th>2do Quimestre<br> <span class="porcentaje">Promedio</span></th>
                            <th>Promedio Final</th>
                            <th>Supletorio</th>
                            <th>Estado<br> Calificación</th>
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
                            <td><input type="number" step="0.1"
                                    name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota1_primer_parcial]"
                                    value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota1_primer_parcial']) : ''; ?>">
                            </td>
                            <td><input type="number" step="0.1"
                                    name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota2_primer_parcial]"
                                    value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota2_primer_parcial']) : ''; ?>">
                            </td>
                            <td><input type="number" step="0.1"
                                    name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][examen_primer_parcial]"
                                    value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['examen_primer_parcial']) : ''; ?>">
                            </td>
                            <td><input type="number" step="0.1"
                                    name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota1_segundo_parcial]"
                                    value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota1_segundo_parcial']) : ''; ?>">
                            </td>
                            <td><input type="number" step="0.1"
                                    name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota2_segundo_parcial]"
                                    value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['nota2_segundo_parcial']) : ''; ?>">
                            </td>
                            <td><input type="number" step="0.1"
                                    name="notas[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][examen_segundo_parcial]"
                                    value="<?php echo isset($notas_estudiantes[$estudiante['id_estudiante']]) ? htmlspecialchars($notas_estudiantes[$estudiante['id_estudiante']]['examen_segundo_parcial']) : ''; ?>">
                            </td>
                            <?php elseif ($id_periodo == 3): ?>
                            <td>
                                <input type="number" step="0.1"
                                    name="calificaciones[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][promedio_primer_quimestre]"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['promedio_primer_quimestre']) : ''; ?>"
                                    readonly>
                            </td>
                            <td>
                                <input type="number" step="0.1"
                                    name="calificaciones[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][promedio_segundo_quimestre]"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['promedio_segundo_quimestre']) : ''; ?>"
                                    readonly>
                            </td>
                            <td>
                                <input type="number" step="0.1"
                                    name="calificaciones[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][nota_final]"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['nota_final']) : ''; ?>"
                                    readonly>
                            </td>
                            <?php
                                    $estado = isset($calificaciones[$estudiante['id_estudiante']]) ? $calificaciones[$estudiante['id_estudiante']]['estado_calificacion'] : 'A';

                                    // Verificar si ya existe un valor para el supletorio
                                    $supletorio_existente = isset($calificaciones[$estudiante['id_estudiante']]['supletorio']) && $calificaciones[$estudiante['id_estudiante']]['supletorio'] !== '';

                                    // Determinar si habilitar o deshabilitar el campo de supletorio
                                    if ($estado === 'A' || $supletorio_existente) {
                                        $supletorio_habilitado = 'disabled'; // Deshabilitar si está aprobado o ya tiene un supletorio
                                    } else {
                                        $supletorio_habilitado = ''; // Habilitar si está reprobado y no tiene supletorio
                                    }
                                ?>
                            <td>
                                <input type="number" step="0.01"
                                    name="calificaciones[<?php echo htmlspecialchars($estudiante['id_estudiante']); ?>][supletorio]"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['supletorio']) : ''; ?>"
                                    <?php echo $supletorio_habilitado; ?>>
                            </td>
                            <td style="text-align: center;">
                                <?php echo $estado === 'A' ? 'Aprobado' : 'Reprobado'; ?>
                            </td>
                            <?php endif; ?>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Botones de acción -->
                <div class="botones-accion">
                    <?php if ($id_periodo == 1 || $id_periodo == 2): ?>
                    <!-- Botones para id_periodo = 1 o 2 -->
                    <button type="button" class="btn btn-secondary"
                        onclick="location.href='ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>';">Regresar</button>
                    <button type="submit" class="btn btn-primary" name="accion" value="eliminar">Eliminar</button>
                    <button type="submit" class="btn btn-success" name="accion" value="guardar">Guardar</button>
                    <button type="button" class="btn btn-primary" onclick="redirigirSiguiente()">Siguiente</button>
                    <?php elseif ($id_periodo == 3): ?>
                    <!-- Botones para id_periodo = 3 -->
                    <button type="button" class="btn btn-regresar" onclick="redirigirRegresar()">Regresar</button>
                    <button type="submit" class="btn btn-success" name="accion" value="guardar_supletorio">Guardar
                        Supletorio</button>
                    <a href="http://localhost/sistema_notas/views/profe/curso_profe.php"
                        class="btn btn-primary">Finalizar</a>
                    <?php endif; ?>
                </div>
        </form>
    </div>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function redirigirSiguiente() {
        var idCurso = <?php echo json_encode($id_curso); ?>;
        window.location.href =
            `http://localhost/sistema_notas/views/profe/registro_calificaciones.php?id_periodo=3&id_curso=${idCurso}`;
    }

    function redirigirRegresar() {
        var idPeriodo = <?php echo json_encode($id_periodo); ?>;
        var idCurso = <?php echo json_encode($id_curso); ?>;
        window.location.href =
            `http://localhost/sistema_notas/views/profe/registro_calificaciones.php?id_curso=${idCurso}`;
    }
    </script>
</body>

</html>