<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta a tu archivo de configuración

// Verificar si el usuario ha iniciado sesión y si su rol es "Administrador" o "Superusuario"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Obtener valores de filtros
$materia = isset($_GET['materia']) ? $conn->real_escape_string($_GET['materia']) : '';
$nivel = isset($_GET['nivel']) ? $conn->real_escape_string($_GET['nivel']) : '';
$anioLectivo = isset($_GET['anioLectivo']) ? $conn->real_escape_string($_GET['anioLectivo']) : '';
$cedula = isset($_GET['cedula']) ? $conn->real_escape_string($_GET['cedula']) : '';
$curso = isset($_GET['curso']) ? $conn->real_escape_string($_GET['curso']) : '';

// Consultar las materias
$materiasQuery = "SELECT nombre FROM materia WHERE estado = 'A'";
$materiasResult = $conn->query($materiasQuery);

// Consultar los niveles
$nivelesQuery = "SELECT nombre FROM nivel WHERE estado = 'A'";
$nivelesResult = $conn->query($nivelesQuery);

// Obtener años lectivos para el filtro
$anioLectivoQuery = "SELECT DISTINCT año FROM historial_academico WHERE estado = 'A'";
$anioLectivoResult = $conn->query($anioLectivoQuery);

// Consultar los cursos
$cursosQuery = "SELECT id_curso FROM curso WHERE estado = 'A'";
$cursosResult = $conn->query($cursosQuery);

// Inicializar variables para la consulta de resultados
$result = null;

// Construir la consulta SQL para los resultados solo si se han aplicado filtros
if ($materia || $nivel || $anioLectivo || $curso || $cedula) {
    $sql = "
    SELECT 
        c.id_curso, 
        r.id_estudiante, 
        e.nombres AS estudiante_nombre, 
        e.apellidos AS estudiante_apellido,
        -- Notas Primer Quimestre
        MAX(CASE WHEN r.id_periodo = 1 THEN r.nota1_primer_parcial END) AS nota1_primer_parcial,
        MAX(CASE WHEN r.id_periodo = 1 THEN r.nota2_primer_parcial END) AS nota2_primer_parcial,
        MAX(CASE WHEN r.id_periodo = 1 THEN r.examen_primer_parcial END) AS examen_primer_parcial,
        MAX(CASE WHEN r.id_periodo = 1 THEN r.nota1_segundo_parcial END) AS nota1_segundo_parcial,
        MAX(CASE WHEN r.id_periodo = 1 THEN r.nota2_segundo_parcial END) AS nota2_segundo_parcial,
        MAX(CASE WHEN r.id_periodo = 1 THEN r.examen_segundo_parcial END) AS examen_segundo_parcial,
        
        -- Notas Segundo Quimestre
        MAX(CASE WHEN r.id_periodo = 2 THEN r.nota1_primer_parcial END) AS nota1_primer_parcial_2Q,
        MAX(CASE WHEN r.id_periodo = 2 THEN r.nota2_primer_parcial END) AS nota2_primer_parcial_2Q,
        MAX(CASE WHEN r.id_periodo = 2 THEN r.examen_primer_parcial END) AS examen_primer_parcial_2Q,
        MAX(CASE WHEN r.id_periodo = 2 THEN r.nota1_segundo_parcial END) AS nota1_segundo_parcial_2Q,
        MAX(CASE WHEN r.id_periodo = 2 THEN r.nota2_segundo_parcial END) AS nota2_segundo_parcial_2Q,
        MAX(CASE WHEN r.id_periodo = 2 THEN r.examen_segundo_parcial END) AS examen_segundo_parcial_2Q,
    
        -- Calificaciones finales
        MAX(cal.promedio_primer_quimestre) AS promedio_primer_quimestre,
        MAX(cal.promedio_segundo_quimestre) AS promedio_segundo_quimestre,
        MAX(cal.nota_final) AS nota_final, 
        MAX(cal.estado_calificacion) AS estado_calificacion
    FROM 
        registro_nota r
    INNER JOIN 
        calificacion cal ON r.id_estudiante = cal.id_estudiante 
        AND r.id_curso = cal.id_curso 
        AND r.id_materia = cal.id_materia 
        AND r.id_his_academico = cal.id_his_academico
    INNER JOIN 
        curso c ON r.id_curso = c.id_curso
    INNER JOIN 
        estudiante e ON r.id_estudiante = e.id_estudiante
    WHERE 
        c.estado = 'A' AND e.estado = 'A'
        -- Excluir estudiantes sin notas
        AND (
            r.nota1_primer_parcial IS NOT NULL OR
            r.nota2_primer_parcial IS NOT NULL OR
            r.examen_primer_parcial IS NOT NULL OR
            r.nota1_segundo_parcial IS NOT NULL OR
            r.nota2_segundo_parcial IS NOT NULL OR
            r.examen_segundo_parcial IS NOT NULL
        )
    ";

    // Aplicar los filtros
    if ($materia) {
        $sql .= " AND r.id_materia = (SELECT id_materia FROM materia WHERE nombre = '$materia' AND estado = 'A')";
    }
    if ($nivel) {
        $sql .= " AND c.id_nivel = (SELECT id_nivel FROM nivel WHERE nombre = '$nivel' AND estado = 'A')";
    }
    if ($anioLectivo) {
        $sql .= " AND cal.id_his_academico = (SELECT id_his_academico FROM historial_academico WHERE año = '$anioLectivo' AND estado = 'A')";
    }
    if ($curso) {
        $sql .= " AND c.id_curso = '$curso'";
    }
    if ($cedula) {
        $sql .= " AND e.cedula = '$cedula'";
    }

    $sql .= " GROUP BY c.id_curso, r.id_estudiante, e.nombres, e.apellidos";

    // Ejecutar consulta
    $result = $conn->query($sql);

    // Verificar si la consulta tuvo éxito
    if (!$result) {
        die("Error en la consulta: " . $conn->error);
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Calificaciones | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
    /* Estilos generales */
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        color: #4b5563;
    }

    .container-fluid {
        max-width: 1000px;
        margin: 40px auto;
        background-color: #fff;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #E62433;
        padding: 10px;
        border-radius: 6px;
        text-align: center;
        margin-bottom: 2rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .card-header h2 {
        color: #fff;
        margin: 0;
        font-size: 1.8rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .filters-container {
        display: flex;
        flex-wrap: nowrap;
        gap: 15px;
        margin-bottom: 20px;
    }

    .filters-container .form-group {
        flex: 1;
        min-width: 150px;
        margin-bottom: 0;
    }

    .filters-container .form-control {
        width: 100%;
    }

    .input-group .form-control {
        border-radius: 6px 0 0 6px;
    }

    .input-group-append .btn {
        border-radius: 0 6px 6px 0;
    }

    .search-bar-container {
        margin-bottom: 20px;
    }

    /* Contenedor de la tabla */
    .table-container {
        margin: 30px auto;
        max-width: 90%;
        overflow-x: auto;
        background-color: #f9f9f9;
        /* Fondo suave y claro */
        border-radius: 8px;
        /* Bordes redondeados para suavizar la apariencia */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        /* Sombra sutil para dar profundidad */
        padding: 20px;
    }

    /* Estilos básicos para la tabla */
    table.table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Arial', sans-serif;
        color: #333;
        /* Color de texto oscuro para un buen contraste */
    }

    /* Estilo para las celdas de la tabla */
    th,
    td {
        padding: 12px 16px;
        text-align: center;
        border: 1px solid #f1f1f1;
        font-size: 13px;
        /* Tamaño de fuente legible */
    }

    /* Estilo para los encabezados de la tabla */
    th {
        background-color: #a20000;
        /* Rojo como color principal */
        color: white;
        font-weight: bold;
        text-transform: uppercase;
        /* Textos en mayúsculas para claridad */
    }

    /* Estilo para las filas de la tabla */
    tr:nth-child(even) {
        background-color: #f2f2f2;
        /* Gris claro para las filas pares */
    }

    tr:nth-child(odd) {
        background-color: #ffffff;
        /* Blanco para las filas impares */
    }

    /* Resaltar fila cuando se pasa el mouse (sin efecto de movimiento) */
    tr:hover {
        background-color: #ee7e86;
        /* Rojo suave */
    }

    /* Bordes de la tabla */
    .table-bordered {
        border: 2px solid #a20000;
        /* Borde rojo para la tabla */
    }

    table.table th,
    table.table td {
        border: 1px solid #ddd;
        /* Bordes sutiles */
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
        padding: 15px;
        color: white;
        border-bottom: 2px solid #B50D22;
    }

    .modal-title {
        font-weight: bold;
        font-size: 1.25rem;
    }

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

    .modal-footer .btn {
        border: none;
        transition: background-color 0.3s ease;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
    }

    .modal-footer .btn-secondary {
        background-color: #0e2643;
    }

    .modal-footer .btn-secondary:hover {
        background-color: #0b1e36;
    }

    .modal-footer .btn-success {
        background-color: #0d5316;
    }

    .modal-footer .btn-success:hover {
        background-color: #0a4312;
    }

    .modal-footer .btn-dark {
        background-color: #3d454d;
    }

    .modal-footer .btn-dark:hover {
        background-color: #31373e;
    }

    footer {
        background-color: white;
        color: #737373;
        text-align: center;
        padding: 20px 0;
        width: 100%;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    footer p {
        margin: 0;
    }

    /* MEDIA QUERIES PARA HACE EL DISEÑO RESPONSIVO */
    @media (max-width: 1200px) {
        .container-fluid {
            padding: 30px;
        }

        .filters-container {
            flex-direction: column;
        }

        .filters-container .form-group {
            margin-bottom: 10px;
        }

        table {
            min-width: 800px;
        }
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding: 20px;
            margin: 20px;
        }

        .card-header h2 {
            font-size: 1.5rem;
        }

        .filters-container {
            flex-direction: column;
            gap: 10px;
        }

        .filters-container .form-group {
            min-width: 100%;
        }

        .table-container {
            padding: 10px;
        }

        table.table {
            font-size: 14px;
        }

        th,
        td {
            padding: 10px;
        }
    }

    @media (max-width: 480px) {
        .card-header h2 {
            font-size: 1.2rem;
        }

        .modal-title {
            font-size: 1rem;
        }

        table {
            min-width: 400px;
        }

        th,
        td {
            font-size: 0.8rem;
            padding: 8px;
        }

        .filters-container {
            gap: 5px;
        }

        .filters-container .form-group {
            min-width: 100%;
        }

        .search-bar-container {
            margin-bottom: 10px;
        }
    }
    </style>
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>
    <div class="container-fluid">
        <div class="card-header">
            <h2>Gestión de Calificaciones</h2>
        </div>
        <form method="GET" action="">
            <div class="filters-container">
                <!-- Campo Materia -->
                <div class="form-group">
                    <i class="bx bx-book"></i>
                    <label for="materia">Materia:</label>
                    <select id="materia" name="materia" class="form-control">
                        <option value="">Selecciona Materia</option>
                        <?php
                while ($row = $materiasResult->fetch_assoc()) {
                    echo "<option value=\"" . htmlspecialchars($row['nombre']) . "\" " . ($materia == $row['nombre'] ? 'selected' : '') . ">" . htmlspecialchars($row['nombre']) . "</option>";
                }
                ?>
                    </select>
                </div>
                <!-- Campo Nivel -->
                <div class="form-group">
                    <i class="bx bx-bar-chart-alt-2"></i>
                    <label for="nivel">Nivel:</label>
                    <select id="nivel" name="nivel" class="form-control">
                        <option value="">Selecciona Nivel</option>
                        <?php
                while ($row = $nivelesResult->fetch_assoc()) {
                    echo "<option value=\"" . htmlspecialchars($row['nombre']) . "\" " . ($nivel == $row['nombre'] ? 'selected' : '') . ">" . htmlspecialchars($row['nombre']) . "</option>";
                }
                ?>
                    </select>
                </div>
                <!-- Campo Curso -->
                <div class="form-group">
                    <i class="bx bx-chalkboard"></i>
                    <label for="curso">Curso:</label>
                    <select id="curso" name="curso" class="form-control">
                        <option value="">Selecciona Curso</option>
                        <?php
                while ($row = $cursosResult->fetch_assoc()) {
                    echo "<option value=\"" . htmlspecialchars($row['id_curso']) . "\" " . ($curso == $row['id_curso'] ? 'selected' : '') . ">" . htmlspecialchars($row['id_curso']) . "</option>";
                }
                ?>
                    </select>
                </div>
                <!-- Campo Año Lectivo -->
                <div class="form-group">
                    <i class="bx bx-calendar"></i>
                    <label for="anioLectivo">Año Lectivo:</label>
                    <select id="anioLectivo" name="anioLectivo" class="form-control">
                        <option value="">Selecciona Año Lectivo</option>
                        <?php
                while ($row = $anioLectivoResult->fetch_assoc()) {
                    echo "<option value=\"" . htmlspecialchars($row['año']) . "\" " . ($anioLectivo == $row['año'] ? 'selected' : '') . ">" . htmlspecialchars($row['año']) . "</option>";
                }
                ?>
                    </select>
                </div>
            </div>
            <!-- Barra de búsqueda -->
            <div class="search-bar-container">
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" id="cedula" name="cedula" class="form-control"
                            value="<?php echo htmlspecialchars($cedula); ?>" placeholder="Cédula Estudiante">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Botón para abrir el manual de uso -->
            <div class="manual-button-container" style="margin-top: 10px; margin-bottom: 20px;">
                <button type="button" data-toggle="modal" data-target="#modalInstrucciones1" class="btn btn-secondary">
                    <i class='bx bx-book'></i> Manual de Uso
                </button>
            </div>
        </form>

        <!-- Tabla de Resultados -->
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2">Curso</th>
                        <th rowspan="2">Estudiante</th>
                        <th colspan="6">Primer Quimestre</th>
                        <th colspan="6">Segundo Quimestre</th>
                        <th colspan="4">Evaluaciones Finales</th>
                    </tr>
                    <tr>
                        <th>Parcial 1 - Nota 1</th>
                        <th>Parcial 1 - Nota 2</th>
                        <th>Parcial 1 - Examen</th>
                        <th>Parcial 2 - Nota 1</th>
                        <th>Parcial 2 - Nota 2</th>
                        <th>Parcial 2 - Examen</th>
                        <th>Parcial 1 - Nota 1</th>
                        <th>Parcial 1 - Nota 2</th>
                        <th>Parcial 1 - Examen</th>
                        <th>Parcial 2 - Nota 1</th>
                        <th>Parcial 2 - Nota 2</th>
                        <th>Parcial 2 - Examen</th>
                        <th>Promedio 1Q</th>
                        <th>Promedio 2Q</th>
                        <th>Nota Final</th>
                        <th>Estado Calificación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id_curso']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['estudiante_nombre']) . " " . htmlspecialchars($row['estudiante_apellido']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota1_primer_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota2_primer_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['examen_primer_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota1_segundo_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota2_segundo_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['examen_segundo_parcial']) . "</td>";
                        // Para el segundo quimestre
                        echo "<td>" . htmlspecialchars($row['nota1_primer_parcial_2Q']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota2_primer_parcial_2Q']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['examen_primer_parcial_2Q']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota1_segundo_parcial_2Q']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota2_segundo_parcial_2Q']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['examen_segundo_parcial_2Q']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['promedio_primer_quimestre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['promedio_segundo_quimestre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota_final']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['estado_calificacion']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php elseif ($result): ?>
        <p>No se encontraron registros con los filtros aplicados.</p>
        <?php endif; ?>


        <!-- Modal 1 - Manual de Uso de la Gestión de Calificaciones (1/2) -->
        <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
            aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInstruccionesLabel1">Manual de Uso - Gestión de Calificaciones
                            (1/2)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>¿Cómo buscar estudiantes?</strong></p>
                        <ol>
                            <li>En la parte superior de la página, completa los filtros de búsqueda:</li>
                            <ul>
                                <li><strong>Materia:</strong> Selecciona la asignatura.</li>
                                <li><strong>Nivel:</strong> Elige el nivel educativo.</li>
                                <li><strong>Curso:</strong> Escoge el curso.</li>
                                <li><strong>Año Lectivo:</strong> Selecciona el año académico.</li>
                                <li><strong>Cédula:</strong> Si buscas por estudiante, ingresa su cédula.</li>
                            </ul>
                            <li>Haz clic en el botón verde <strong>"Buscar"</strong> debajo de los filtros.</li>
                        </ol>
                        <p><strong>Nota:</strong> Si no aparece ningún estudiante, verifica que los datos ingresados
                            sean correctos.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success"
                            onclick="openModal('#modalInstrucciones2')">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 2 - Manual de Uso de la Gestión de Calificaciones (2/2) -->
        <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog"
            aria-labelledby="modalInstruccionesLabel2" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInstruccionesLabel2">Manual de Uso - Gestión de Calificaciones
                            (2/2)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>¿Cómo ver los resultados?</strong></p>
                        <ol>
                            <li>Al hacer clic en <strong>"Buscar"</strong>, verás una tabla con los estudiantes que
                                coinciden con tus filtros.</li>
                            <li>La tabla muestra:
                                <ul>
                                    <li><strong>Nombre:</strong> El nombre completo del estudiante.</li>
                                    <li><strong>Curso:</strong> El curso en el que está matriculado.</li>
                                    <li><strong>Calificación:</strong> Las notas obtenidas en los parciales y exámenes,
                                        así como el promedio final.</li>
                                    <li><strong>Estado de la calificación:</strong> Indica si el estudiante está
                                        <strong>Aprobado</strong> o <strong>Reprobado</strong>.
                                    </li>
                                </ul>
                            </li>
                            <li>Si ves varios estudiantes, puedes buscar por nombre, curso o estado de calificación para
                                encontrar a uno en particular.</li>
                        </ol>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="openModal('#modalInstrucciones1')">Atrás</button>
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

    <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- SB Admin 2 JS -->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
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