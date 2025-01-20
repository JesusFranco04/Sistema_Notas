<?php
session_start();
include '../../Crud/config.php';
// Inicializar las variables de mensaje
$mensaje = '';
$mensaje_tipo = '';

// Verificar si el usuario ha iniciado sesión y si su rol es "Administrador" o "Superusuario"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    header("Location: ../../login.php");
    exit();

}

// Función para subir de nivel a un estudiante
function subir_nivel($id_estudiante, $id_nivel, $conn) {
    global $mensaje, $mensaje_tipo;
    $nuevo_nivel = $id_nivel + 1;
    // Verificar si el nuevo nivel está dentro del rango permitido
    if ($nuevo_nivel > 6) {
        $mensaje = 'No se puede subir de nivel. El estudiante ya está en el nivel máximo.';
        $mensaje_tipo = 'danger';
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
        $mensaje = 'Error al intentar subir de nivel: ' . $conn->error;
        $mensaje_tipo = 'danger';
        $conn->close();
        exit();
    }
}

// Obtener datos para los filtros
$sql_niveles = "SELECT id_nivel, nombre FROM nivel WHERE estado = 'A'";
$sql_paralelos = "SELECT id_paralelo, nombre FROM paralelo WHERE estado = 'A'";
$sql_especialidades = "SELECT id_especialidad, nombre FROM especialidad WHERE estado = 'A'";
$sql_jornadas = "SELECT id_jornada, nombre FROM jornada WHERE estado = 'A'";
$sql_historiales = "SELECT id_his_academico, año FROM historial_academico WHERE estado = 'A'";

$nivelesResult = $conn->query($sql_niveles);
$paralelosResult = $conn->query($sql_paralelos);
$especialidadesResult = $conn->query($sql_especialidades);
$jornadasResult = $conn->query($sql_jornadas);
$historialesResult = $conn->query($sql_historiales);

// Procesamiento del formulario
$estudiantes = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Inicializar variables de filtro con valores predeterminados si no están presentes
    $id_his_academico = $_POST['id_his_academico'] ?? '';
    $id_nivel = $_POST['id_nivel'] ?? '';
    $id_paralelo = $_POST['id_paralelo'] ?? '';
    $id_especialidad = $_POST['id_especialidad'] ?? '';  
    $id_jornada = $_POST['id_jornada'] ?? '';

    // Verificar que se han seleccionado todos los filtros
    if (
        (empty($id_his_academico) && !empty($id_nivel)) ||
        (empty($id_nivel) && !empty($id_his_academico)) ||
        (empty($id_paralelo) && !empty($id_his_academico)) ||
        (empty($id_jornada) && !empty($id_his_academico)) ||
        (empty($id_especialidad) && !empty($id_his_academico))
    ) {
        $mensaje = 'Por favor, seleccione todos los filtros para una búsqueda precisa.';
        $mensaje_tipo = 'danger';
    } elseif (empty($id_his_academico) || empty($id_nivel) || empty($id_paralelo) || empty($id_jornada)) {
        $mensaje = 'Por favor, seleccione todos los filtros.';
        $mensaje_tipo = 'danger';
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
            AND e.id_especialidad = ? 
            AND e.id_jornada = ?
            ORDER BY e.id_estudiante, m.nombre ASC";

        if ($stmt_estudiantes = $conn->prepare($sql_estudiantes)) {
            $stmt_estudiantes->bind_param("iiiii", $id_his_academico, $id_nivel, $id_paralelo, $id_especialidad, $id_jornada);
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
                $mensaje = 'No existe ningún grupo de estudiantes con los filtros seleccionados.';
                $mensaje_tipo = 'danger';
            }

            $stmt_estudiantes->close();
        } else {
            $mensaje = 'Error en la preparación de la consulta de estudiantes: ' . $conn->error;
            $mensaje_tipo = 'danger';
            $conn->close();
            exit();
        }
    }

    // Procesar la subida de nivel si se ha enviado el formulario con selección de estudiantes
    if (isset($_POST['submit_selected'])) {
        $ids_estudiantes = $_POST['estudiantes'] ?? [];

        if (empty($ids_estudiantes)) {
            $mensaje = 'Debe seleccionar al menos un estudiante para subir de nivel.';
            $mensaje_tipo = 'danger';
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
                        $mensaje = "El estudiante con ID $id_estudiante ha subido de nivel con éxito.";
                        $mensaje_tipo = 'success';
                    } else {
                        $mensaje = "El estudiante con ID $id_estudiante no tiene todas las materias aprobadas.";
                        $mensaje_tipo = 'danger';
                    }

                    $stmt_calificaciones->close();
                } else {
                    $mensaje = 'Error en la preparación de la consulta de calificaciones: ' . $conn->error;
                    $mensaje_tipo = 'danger';
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
            <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje_tipo === 'exito' ? 'success' : 'danger'; ?>">
                <?php echo $mensaje; ?>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="id_nivel"><i class="bx bx-layer"></i> Nivel:</label>
                <select name="id_nivel" id="id_nivel" required>
                    <option value="">Seleccione un nivel</option>
                    <?php while ($row = $nivelesResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_nivel']; ?>"><?php echo $row['nombre']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_paralelo"><i class="bx bx-columns"></i> Paralelo:</label>
                <select name="id_paralelo" id="id_paralelo" required>
                    <option value="">Seleccione un paralelo</option>
                    <?php while ($row = $paralelosResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_paralelo']; ?>"><?php echo $row['nombre']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_especialidad"><i class='bx bx-book-content'></i> Especialidad:</label>
                <select name="id_especialidad" id="id_especialidad" required>
                    <option value="">Seleccione una especialidad</option>
                    <?php while ($row = $especialidadesResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_especialidad']; ?>"><?php echo $row['nombre']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_jornada"><i class="bx bx-time"></i> Jornada:</label>
                <select name="id_jornada" id="id_jornada" required>
                    <option value="">Seleccione una jornada</option>
                    <?php while ($row = $jornadasResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_jornada']; ?>"><?php echo $row['nombre']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_his_academico"><i class="bx bx-calendar"></i> Año Lectivo:</label>
                <select name="id_his_academico" id="id_his_academico" required>
                    <option value="">Seleccione un año lectivo</option>
                    <?php while ($row = $historialesResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id_his_academico']; ?>"><?php echo $row['año']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="button-group">
                <!-- Botón para abrir el manual de uso -->
                <button type="button" data-toggle="modal" data-target="#modalInstrucciones1">
                    <i class='bx bx-book'></i> Manual de Uso
                </button>
                <!-- Botón para descargar reporte en PDF -->
                <button type="button"
                    onclick="window.open('http://localhost/sistema_notas/views/admin/reporte_subir_nivel.php', '_blank')">
                    <i class='bx bx-download'></i> Descargar Reporte
                </button>
                <!-- Botón para buscar estudiantes -->
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