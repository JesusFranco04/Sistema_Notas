<?php 
session_start();
include '../../Crud/config.php'; // Asegúrate de que este archivo contenga la conexión a la base de datos

// Verificar si el usuario tiene permiso para realizar esta acción
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    header("Location: ../../login.php");
    exit();
}

// Inicializar variables para mensajes
$mensaje = [];
$mensaje_tipo = 'success'; // Default

// Consultas para los filtros
$sql_niveles = "SELECT id_nivel, nombre FROM nivel WHERE estado = 'A'";
$sql_paralelos = "SELECT id_paralelo, nombre FROM paralelo WHERE estado = 'A'";
$sql_especialidades = "SELECT id_especialidad, nombre FROM especialidad WHERE estado = 'A'";
$sql_jornadas = "SELECT id_jornada, nombre FROM jornada WHERE estado = 'A'";

// Consulta para obtener los 2 años académicos más recientes (cerrados)
$sql_historiales = "
    SELECT id_his_academico, año, fecha_cierre_programada 
    FROM historial_academico 
    WHERE estado = 'I' 
    ORDER BY fecha_cierre_programada DESC 
    LIMIT 2";

// Consulta para obtener el año académico más reciente activo
$sql_historiales_activas = "
    SELECT id_his_academico, año, fecha_ingreso 
    FROM historial_academico 
    WHERE estado = 'A' 
    ORDER BY fecha_ingreso DESC 
    LIMIT 1";

// Consultas para los filtros
$nivelesResult = $conn->query($sql_niveles);
$paralelosResult = $conn->query($sql_paralelos);
$especialidadesResult = $conn->query($sql_especialidades);
$jornadasResult = $conn->query($sql_jornadas);
$historialesResult = $conn->query($sql_historiales);
$historialActivoResult = $conn->query($sql_historiales_activas);

// Obtener los dos años académicos inactivos más recientes
$historialesInactivos = [];
while ($row = $historialesResult->fetch_assoc()) {
    $historialesInactivos[] = $row;
}

// Obtener el año académico activo más reciente
$historialActivo = $historialActivoResult->fetch_assoc();
$id_his_academico_activo = $historialActivo['id_his_academico'] ?? null;

// Consulta para obtener las materias de un estudiante
$sql_materias = "
    SELECT m.nombre 
    FROM materia m
    JOIN calificacion c ON m.id_materia = c.id_materia
    WHERE c.id_estudiante = ? AND c.id_his_academico = ?
";

// Inicializar la variable $estudiantes como un array vacío al principio
$estudiantes = [];

// Procesamiento de formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_his_academico = $_POST['id_his_academico'] ?? '';
    $id_nivel = $_POST['id_nivel'] ?? '';
    $id_paralelo = $_POST['id_paralelo'] ?? '';
    $id_especialidad = $_POST['id_especialidad'] ?? '';  
    $id_jornada = $_POST['id_jornada'] ?? '';

    // Depuración: Verificar qué campos están vacíos
    $campos_vacios = [];
    if (empty($id_his_academico)) $campos_vacios[] = 'Año Lectivo';
    if (empty($id_nivel)) $campos_vacios[] = 'Nivel';
    if (empty($id_paralelo)) $campos_vacios[] = 'Paralelo';
    if (empty($id_especialidad)) $campos_vacios[] = 'Especialidad';
    if (empty($id_jornada)) $campos_vacios[] = 'Jornada';

    if (!empty($campos_vacios)) {
        $mensaje[] = 'Faltan datos necesarios para procesar la solicitud: ' . implode(', ', $campos_vacios);
        $mensaje_tipo = 'error';
    } else {
        // Obtener estudiantes con los filtros seleccionados
        $sql_estudiantes = "
            SELECT e.id_estudiante, e.nombres, e.apellidos, c.estado_calificacion, e.id_nivel
            FROM estudiante e
            JOIN calificacion c ON e.id_estudiante = c.id_estudiante
            WHERE e.id_his_academico = ? 
            AND e.id_nivel = ? 
            AND e.id_paralelo = ? 
            AND e.id_especialidad = ? 
            AND e.id_jornada = ? 
            ORDER BY e.id_estudiante ASC";

        if ($stmt_estudiantes = $conn->prepare($sql_estudiantes)) {
            $stmt_estudiantes->bind_param("iiiii", $id_his_academico, $id_nivel, $id_paralelo, $id_especialidad, $id_jornada);
            $stmt_estudiantes->execute();
            $result_estudiantes = $stmt_estudiantes->get_result();

            // Llenar el array $estudiantes con los datos obtenidos
            while ($row = $result_estudiantes->fetch_assoc()) {
                $id_estudiante = $row['id_estudiante'];

                // Verificar si el estudiante ya existe en el array
                if (!isset($estudiantes[$id_estudiante])) {
                    $estudiantes[$id_estudiante] = [
                        'nombres' => $row['nombres'],
                        'apellidos' => $row['apellidos'],
                        'estado_calificacion' => $row['estado_calificacion'],
                        'id_nivel' => $row['id_nivel'],
                        'materias' => [], // Inicializamos el array de materias
                        'calificaciones' => [] // Inicializamos el array de calificaciones
                    ];
                }

                // Obtener materias y calificaciones del estudiante
                if ($stmt_materias = $conn->prepare($sql_materias)) {
                    $stmt_materias->bind_param("ii", $id_estudiante, $id_his_academico);
                    $stmt_materias->execute();
                    $result_materias = $stmt_materias->get_result();

                    // Guardar las materias y calificaciones en el array
                    while ($materia = $result_materias->fetch_assoc()) {
                        $estudiantes[$id_estudiante]['materias'][] = $materia['nombre'];
                        $estudiantes[$id_estudiante]['calificaciones'][] = $row['estado_calificacion'];
                    }
                    $stmt_materias->close();
                }
            }

            // Si no se encuentran estudiantes, mostramos un mensaje
            if (empty($estudiantes)) {
                $mensaje[] = 'No se encontró ningún estudiante con los filtros seleccionados. Por favor, verifique los datos ingresados.';
                $mensaje_tipo = 'error';
            }

            $stmt_estudiantes->close();
        } else {
            $mensaje[] = 'Error en la consulta de estudiantes: ' . $conn->error;
            $mensaje_tipo = 'error';
        }
    }
}

// Función para verificar materias reprobadas
function verificar_materias_reprobadas($id_estudiante, $id_his_academico, $conn) {
    $sql_reprobadas = "
        SELECT COUNT(*) as total_reprobadas
        FROM calificacion c
        WHERE c.id_estudiante = ? 
        AND c.id_his_academico = ? 
        AND c.estado_calificacion = 'R'"; // Estado 'R' para reprobadas

    if ($stmt = $conn->prepare($sql_reprobadas)) {
        $stmt->bind_param("ii", $id_estudiante, $id_his_academico);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_reprobadas = $row['total_reprobadas'];
        $stmt->close();

        return $total_reprobadas > 0;
    }
    return false;
}

// Función para verificar si todas las materias están aprobadas
function verificar_materias_aprobadas($id_estudiante, $id_his_academico, $conn) {
    $sql_aprobadas = "
        SELECT COUNT(*) as total_aprobadas
        FROM calificacion c
        WHERE c.id_estudiante = ? 
        AND c.id_his_academico = ? 
        AND c.estado_calificacion = 'A'"; // Estado 'A' para aprobadas

    if ($stmt = $conn->prepare($sql_aprobadas)) {
        $stmt->bind_param("ii", $id_estudiante, $id_his_academico);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_aprobadas = $row['total_aprobadas'];
        $stmt->close();

        return $total_aprobadas > 0;
    }
    return false;
}

// Procesar los estudiantes seleccionados para promoción
if (isset($_POST['submit_selected']) && isset($_POST['estudiantes'])) {
    foreach ($_POST['estudiantes'] as $id_estudiante) {
        // Verificar si el estudiante existe
        if (isset($estudiantes[$id_estudiante])) {
            $nombre_estudiante = $estudiantes[$id_estudiante]['nombres'] . ' ' . $estudiantes[$id_estudiante]['apellidos'];
            $nivel_actual = $estudiantes[$id_estudiante]['id_nivel'];

            // Verificamos si el estudiante tiene materias reprobadas
            if (verificar_materias_reprobadas($id_estudiante, $id_his_academico, $conn)) {
                // Si el estudiante tiene materias reprobadas y está en el nivel 6
                if ($nivel_actual == 6) {
                    $sql_actualizar_nivel = "
                        UPDATE estudiante 
                        SET id_his_academico = ? 
                        WHERE id_estudiante = ?";
                    if ($stmt = $conn->prepare($sql_actualizar_nivel)) {
                        $stmt->bind_param("ii", $id_his_academico_activo, $id_estudiante);
                        $stmt->execute();
                        $stmt->close();
                        $mensaje[] = "El estudiante $nombre_estudiante tiene materias reprobadas y se mantendrá en el nivel $nivel_actual, pero se actualizó su año académico.";
                    } else {
                        $mensaje[] = "Error al intentar actualizar el año académico para $nombre_estudiante: " . $conn->error;
                        $mensaje_tipo = 'error';
                    }
                } else {
                    // Si el estudiante tiene materias reprobadas y no está en el nivel 6
                    $sql_actualizar_nivel = "
                        UPDATE estudiante 
                        SET id_his_academico = ? 
                        WHERE id_estudiante = ?";
                    if ($stmt = $conn->prepare($sql_actualizar_nivel)) {
    $stmt->bind_param("ii", $id_his_academico_activo, $id_estudiante);
    $stmt->execute();
    $stmt->close();
    $mensaje[] = "El estudiante $nombre_estudiante tiene materias reprobadas y se mantendrá en el nivel $nivel_actual, pero se actualizó su año académico.";
} else {
    $mensaje[] = "Error al intentar actualizar el año académico para $nombre_estudiante: " . $conn->error;
    $mensaje_tipo = 'error';
}
}
} else {
    // Verificamos si todas las materias están aprobadas
    if (verificar_materias_aprobadas($id_estudiante, $id_his_academico, $conn)) {
        // Verificamos si el estudiante está en el nivel 6 (grado de graduación)
        if ($nivel_actual == 6) {
            // Si el estudiante aprueba todas las materias, está listo para graduarse
            $mensaje[] = "El estudiante $nombre_estudiante ha completado el nivel 6 y está listo para la graduación. No es necesario realizar cambios.";
        } else {
            // Incrementar nivel
            $nuevo_nivel = $nivel_actual + 1;

            // Actualizamos el nivel y el año lectivo
            $sql_actualizar_nivel = "
                UPDATE estudiante 
                SET id_nivel = ?, id_his_academico = ? 
                WHERE id_estudiante = ?";
            if ($stmt = $conn->prepare($sql_actualizar_nivel)) {
                $stmt->bind_param("iii", $nuevo_nivel, $id_his_academico_activo, $id_estudiante);
                $stmt->execute();
                $stmt->close();

                $mensaje[] = "El estudiante $nombre_estudiante ha sido promovido al nivel $nuevo_nivel.";
            } else {
                $mensaje[] = "Error al intentar actualizar el nivel para $nombre_estudiante: " . $conn->error;
                $mensaje_tipo = 'error';
            }
        }
    } else {
        // Si el estudiante no aprueba todas las materias y no está en el nivel 6
        $sql_actualizar_nivel = "
            UPDATE estudiante 
            SET id_his_academico = ? 
            WHERE id_estudiante = ?";
        if ($stmt = $conn->prepare($sql_actualizar_nivel)) {
            $stmt->bind_param("ii", $id_his_academico_activo, $id_estudiante);
            $stmt->execute();
            $stmt->close();

            $mensaje[] = "El estudiante $nombre_estudiante tiene materias reprobadas y se mantendrá en el nivel $nivel_actual, pero se actualizó su año académico.";
        } else {
            $mensaje[] = "Error al intentar actualizar el año académico para $nombre_estudiante: " . $conn->error;
            $mensaje_tipo = 'error';
        }
    }
}
}
}
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Nivel Académico | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/boxicons/css/boxicons.min.css" rel="stylesheet">

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
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    /* Título principal con franja roja más sutil */
    .header {
        background-color: #E62433;
        /* Color suave y menos agresivo */
        padding: 8px 15px;
        /* Menos padding para un efecto más sutil */
        border-radius: 6px;
        text-align: center;
        margin-bottom: 2rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        /* Sombra más suave */
    }

    .header h1 {
        color: #fff;
        margin: 0;
        font-size: 1.8rem;
        /* Tamaño de fuente más pequeño y sutil */
        font-weight: 600;
        /* Peso de fuente más ligero */
        letter-spacing: 0.5px;
        /* Espaciado de letras reducido */
    }

    /* Estilos de los formularios */
    .form-group {
        margin-bottom: 25px;
        /* Aumento de margen para un mejor espaciado */
    }

    label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #333;
        font-size: 1.1rem;
        /* Tamaño de fuente ajustado */
    }

    select,
    input[type="text"] {
        width: 100%;
        padding: 14px 18px;
        margin: 0;
        box-sizing: border-box;
        border: 2px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        /* Ajuste de tamaño de texto */
        transition: border-color 0.3s ease;
    }

    select:focus,
    input[type="text"]:focus {
        border-color: #E62433;
    }

    /* Estilos de los botones */
    .button-group {
        display: flex;
        justify-content: flex-end;
        /* Alineación de los botones a la derecha */
        gap: 20px;
        /* Espacio considerable entre botones */
        margin-top: 20px;
    }


    button {
        padding: 12px 20px;
        /* Tamaño de botón más moderado */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        font-size: 1rem;
        /* Ajuste de tamaño de texto */
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    button:hover {
        transform: translateY(-2px);
    }

    button:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    /* Colores personalizados para los botones */
    button:first-child {
        background-color: #0e2643;
        color: white;
        /* Manual de uso */
    }

    button:nth-child(2) {
        background-color: #0d5316;
        color: white;
        /* Descargar reporte */
    }

    button:nth-child(3) {
        background-color: #DE112D;
        color: white;
        /* Buscar estudiantes */
    }

    /* Estilos generales para las alertas */
    .alert {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
        border-radius: 8px;
        font-size: 1rem;
        font-family: 'Arial', sans-serif;
        color: #fff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-left: 6px solid;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .alert:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    /* Estilo para alertas de peligro */
    .alert-danger {
        background-color: #e74c3c;
        border-left-color: #c0392b;
    }

    /* Estilo para alertas de éxito */
    .alert-success {
        background-color: #2ecc71;
        border-left-color: #27ae60;
    }

    /* Estilo para alertas de advertencia */
    .alert-warning {
        background-color: #f39c12;
        border-left-color: #e67e22;
    }

    /* Estilos de la tabla */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        font-size: 16px;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
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

    .aprobado {
        color: #045d05;
        font-weight: bold;
    }

    .reprobado {
        color: #cf1d29;
        font-weight: bold;
    }

    /* Estilos generales para los modales */
    .modal-content {
        border-radius: 8px;
        border: 1px solid #ddd;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        font-family: Arial, sans-serif;
    }

    .modal-header {
        background-color: #DE112D;
        /* Rojo */
        padding: 15px;
        color: white;
        /* Texto en blanco */
        border-bottom: 2px solid #B50D22;
        /* Bordes más definidos */
    }

    .modal-title {
        font-weight: bold;
        font-size: 1.25rem;
        /* Tamaño ligeramente más grande */
    }

    /* Estilo para el botón de cerrar */
    .modal-header .close {
        font-size: 1.5rem;
        color: white;
        background: none;
        border: none;
        opacity: 0.8;
        outline: none;
        transition: opacity 0.2s;
    }

    .modal-header .close:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    /* Botones del modal */
    .modal-footer .btn-secondary {
        background-color: #0e2643;
        /* Azul oscuro */
        color: white;
        /* Texto en blanco */
        border: none;
        /* Sin borde */
        transition: background-color 0.3s ease;
        /* Animación suave */
    }

    .modal-footer .btn-secondary:hover {
        background-color: #0b1e36;
        /* Azul más claro al pasar el cursor */
    }

    /* Botón Siguiente (verde) */
    .modal-footer .btn-success {
        background-color: #0d5316;
        /* Verde */
        color: white;
        /* Texto en blanco */
        border: none;
        /* Sin borde */
        transition: background-color 0.3s ease;
        /* Animación suave */
    }

    .modal-footer .btn-success:hover {
        background-color: #0a4312;
        /* Verde más oscuro al pasar el cursor */
    }

    /* Botón Cerrar (gris oscuro) */
    .modal-footer .btn-dark {
        background-color: #3d454d;
        /* Gris oscuro */
        color: white;
        /* Texto en blanco */
        border: none;
        /* Sin borde */
        transition: background-color 0.3s ease;
        /* Animación suave */
    }

    .modal-footer .btn-dark:hover {
        background-color: #31373e;
        /* Gris más oscuro al pasar el cursor */
    }

    /* Ajustes generales del modal */
    .modal-content {
        border-radius: 8px;
        /* Bordes redondeados */
        overflow: hidden;
        /* Evitar desbordes */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        /* Sombra para profundidad */
    }

    footer {
        background-color: white;
        /* Color de fondo blanco */
        color: #737373;
        /* Color del texto en gris oscuro */
        text-align: center;
        /* Centrar el texto */
        padding: 20px 0;
        /* Espaciado interno vertical */
        width: 100%;
        /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        /* Sombra más pronunciada */
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }
    </style>
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Subida de Nivel de Estudiantes</h1>
        </div>

        <form method="POST" action="">
            <!-- Mostrar mensajes de error o éxito -->
            <?php if (!empty($mensaje) && is_array($mensaje)): ?>
            <div class="alert alert-<?php echo htmlspecialchars(
                $mensaje_tipo === 'exito' ? 'success' : ($mensaje_tipo === 'error' ? 'danger' : 'warning'),
                ENT_QUOTES,
                'UTF-8'
            ); ?>">
                <?php foreach ($mensaje as $linea): ?>
                <p><?php echo htmlspecialchars($linea, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Filtro: Nivel -->
            <div class="form-group">
                <label for="id_nivel"><i class="bx bx-layer"></i> Nivel:</label>
                <select name="id_nivel" id="id_nivel" required>
                    <option value="">Seleccione un nivel</option>
                    <?php while ($row = $nivelesResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_nivel']; ?>"
                        <?php echo isset($_POST['id_nivel']) && $_POST['id_nivel'] == $row['id_nivel'] ? 'selected' : ''; ?>>
                        <?php echo $row['nombre']; ?>
                    </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Filtro: Paralelo -->
            <div class="form-group">
                <label for="id_paralelo"><i class="bx bx-columns"></i> Paralelo:</label>
                <select name="id_paralelo" id="id_paralelo" required>
                    <option value="">Seleccione un paralelo</option>
                    <?php while ($row = $paralelosResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_paralelo']; ?>"
                        <?php echo isset($_POST['id_paralelo']) && $_POST['id_paralelo'] == $row['id_paralelo'] ? 'selected' : ''; ?>>
                        <?php echo $row['nombre']; ?>
                    </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Filtro: Especialidad -->
            <div class="form-group">
                <label for="id_especialidad"><i class='bx bx-book-content'></i> Especialidad:</label>
                <select name="id_especialidad" id="id_especialidad" required>
                    <option value="">Seleccione una especialidad</option>
                    <?php while ($row = $especialidadesResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_especialidad']; ?>"
                        <?php echo isset($_POST['id_especialidad']) && $_POST['id_especialidad'] == $row['id_especialidad'] ? 'selected' : ''; ?>>
                        <?php echo $row['nombre']; ?>
                    </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Filtro: Jornada -->
            <div class="form-group">
                <label for="id_jornada"><i class="bx bx-time"></i> Jornada:</label>
                <select name="id_jornada" id="id_jornada" required>
                    <option value="">Seleccione una jornada</option>
                    <?php while ($row = $jornadasResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_jornada']; ?>"
                        <?php echo isset($_POST['id_jornada']) && $_POST['id_jornada'] == $row['id_jornada'] ? 'selected' : ''; ?>>
                        <?php echo $row['nombre']; ?>
                    </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Filtro: Año Lectivo -->
            <div class="form-group">
                <label for="id_his_academico"><i class="bx bx-calendar"></i> Año Lectivo:</label>
                <select name="id_his_academico" id="id_his_academico" required>
                    <option value="">Seleccione un año lectivo</option>
                    <?php foreach ($historialesInactivos as $row) { ?>
                    <option value="<?php echo htmlspecialchars($row['id_his_academico']); ?>"
                        <?php echo isset($_POST['id_his_academico']) && $_POST['id_his_academico'] == $row['id_his_academico'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['año']); ?>
                    </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Botones de Acción -->
            <div class="button-group">
                <button type="button" data-toggle="modal" data-target="#modalInstrucciones1">
                    <i class='bx bx-book'></i> Manual de Uso
                </button>
                <!-- Botón para descargar reporte en PDF -->
                <button type="button"
                    onclick="window.open('http://localhost/sistema_notas/views/admin/reporte_subir_nivel.php', '_blank')">
                    <i class='bx bx-download'></i> Descargar Reporte
                </button>
                <button type="submit">
                    <i class='bx bx-search'></i> Buscar Estudiantes
                </button>
            </div>
        </form>

        <!-- Mostrar Estudiantes si se han encontrado -->
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
                        <td>
                            <?php 
                        foreach ($info['calificaciones'] as $calificacion) {
                            $clase = ($calificacion == 'A') ? 'aprobado' : 'reprobado';
                            echo "<span class='$clase'>$calificacion</span> ";
                        }
                        ?>
                        </td>
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


        <!-- Modal 1 - Subida de Nivel de Estudiantes (1/3) -->
        <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
            aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInstruccionesLabel1">Manual de Uso - Subida de Nivel de
                            Estudiantes (1/3)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>¿Cómo buscar a los estudiantes para subir de nivel?</strong></p>
                        <p>Para encontrar a los estudiantes que deseas subir de nivel, primero necesitas utilizar los
                            filtros ubicados en la parte superior de la página. Los filtros disponibles son:</p>
                        <ul>
                            <li><strong>Nivel:</strong> Selecciona el nivel actual de los estudiantes que deseas buscar.
                            </li>
                            <li><strong>Paralelo:</strong> Elige el paralelo de los estudiantes.</li>
                            <li><strong>Jornada:</strong> Selecciona la jornada (por ejemplo: matutina o vespertina).
                            </li>
                            <li><strong>Año Lectivo:</strong> Elige el año académico en el que están los estudiantes.
                            </li>
                        </ul>
                        <p>Una vez seleccionados los filtros, presiona el botón <strong>"Buscar Estudiantes"</strong>
                            para ver la lista de estudiantes disponibles para la subida de nivel.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success"
                            onclick="openModal('#modalInstrucciones2')">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 2 - Subida de Nivel de Estudiantes (2/3) -->
        <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog"
            aria-labelledby="modalInstruccionesLabel2" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInstruccionesLabel2">Manual de Uso - Subida de Nivel de
                            Estudiantes (2/3)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>¿Cómo selecciono a los estudiantes que quiero subir de nivel?</strong></p>
                        <p>Después de realizar la búsqueda, verás una tabla con los estudiantes que cumplen con los
                            filtros seleccionados. En esta tabla podrás ver:</p>
                        <ul>
                            <li><strong>Nombres y Apellidos</strong> de cada estudiante.</li>
                            <li><strong>Materias</strong> que están cursando.</li>
                            <li><strong>Calificaciones</strong> obtenidas en las materias.</li>
                        </ul>
                        <p>Para seleccionar a un estudiante, marca la casilla de verificación (checkbox) al lado de su
                            nombre. Puedes seleccionar varios estudiantes al mismo tiempo.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="openModal('#modalInstrucciones1')">Atrás</button>
                        <button type="button" class="btn btn-success"
                            onclick="openModal('#modalInstrucciones3')">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 3 - Subida de Nivel de Estudiantes (3/3) -->
        <div class="modal fade" id="modalInstrucciones3" tabindex="-1" role="dialog"
            aria-labelledby="modalInstruccionesLabel3" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInstruccionesLabel3">Manual de Uso - Subida de Nivel de
                            Estudiantes (3/3)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>¿Cómo subo de nivel a los estudiantes seleccionados?</strong></p>
                        <p>Una vez que hayas seleccionado a los estudiantes, el siguiente paso es subirlos de nivel.
                            Para hacerlo, simplemente haz clic en el botón <strong>"Subir Nivel Seleccionados"</strong>,
                            que se encuentra al final de la tabla.</p>
                        <p>Recuerda que solo los estudiantes con calificaciones aprobadas serán elegibles para la subida
                            de nivel.</p>
                        <p>Al hacer clic en este botón, los estudiantes seleccionados serán movidos al siguiente nivel,
                            y aparecerá una confirmación del proceso completado.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="openModal('#modalInstrucciones2')">Atrás</button>
                        <button type="button" class="btn btn-dark" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
    </footer>
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cargar jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Luego carga sb-admin-2.min.js -->
    <script src="path_to_your/js/sb-admin-2.min.js"></script>



    <script>
    function openModal(modalId) {
        // Ocultar todos los modales abiertos
        $('.modal').modal('hide');

        // Mostrar el modal correspondiente
        if ($(modalId).length) {
            $(modalId).modal('show');
        } else {
            console.error('Modal no encontrado: ' + modalId);
        }
    }
    </script>
</body>

</html>