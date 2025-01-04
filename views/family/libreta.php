<?php
session_start();

// Verificar si el usuario ha iniciado sesión y si su rol es "Administrador" o "Superusuario"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Padre'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Incluir el archivo de conexión
include('../../Crud/config.php'); // Ruta a tu archivo de configuración de conexión

// Obtener el id_estudiante desde la URL
if (isset($_GET['id_estudiante'])) {
    $id_estudiante = intval($_GET['id_estudiante']);
} else {
    die("ID del estudiante no proporcionado.");
}

// Consultar la información del estudiante
$query_estudiante = "
    SELECT e.id_estudiante, e.nombres, e.apellidos, e.id_nivel, e.id_paralelo, e.id_jornada, e.id_his_academico, n.nombre AS nombre_nivel, p.nombre AS nombre_paralelo, s.nombre AS nombre_subnivel, esp.nombre AS nombre_especialidad, j.nombre AS nombre_jornada, h.año
    FROM estudiante e
    JOIN nivel n ON e.id_nivel = n.id_nivel
    JOIN paralelo p ON e.id_paralelo = p.id_paralelo
    JOIN subnivel s ON e.id_subnivel = s.id_subnivel
    JOIN especialidad esp ON e.id_especialidad = esp.id_especialidad
    JOIN jornada j ON e.id_jornada = j.id_jornada
    JOIN historial_academico h ON e.id_his_academico = h.id_his_academico
    WHERE e.id_estudiante = ?
";

$stmt_estudiante = $conn->prepare($query_estudiante);
$stmt_estudiante->bind_param("i", $id_estudiante);
$stmt_estudiante->execute();
$result_estudiante = $stmt_estudiante->get_result();

if ($result_estudiante->num_rows === 0) {
    die('Estudiante no encontrado.');
}

$estudiante = $result_estudiante->fetch_assoc();
$id_nivel_actual = $estudiante['id_nivel'];
$id_paralelo = $estudiante['id_paralelo'];
$id_jornada = $estudiante['id_jornada'];
$id_his_academico = $estudiante['id_his_academico'];

// Consultar las materias del nivel, paralelo y jornada del estudiante
$query_materias = "
    SELECT m.id_materia, m.nombre AS materia
    FROM materia m
    JOIN registro_nota r ON m.id_materia = r.id_materia
    WHERE r.id_estudiante = ? 
      AND r.id_his_academico = ?
    GROUP BY m.id_materia, m.nombre
";

$stmt_materias = $conn->prepare($query_materias);
$stmt_materias->bind_param("ii", $id_estudiante, $id_his_academico);
$stmt_materias->execute();
$result_materias = $stmt_materias->get_result();

// Consultar las calificaciones del estudiante para los periodos 1 y 2
$query_calificaciones = "
    SELECT m.nombre AS materia, 
           r.id_periodo,
           r.nota1_primer_parcial, r.nota2_primer_parcial, r.examen_primer_parcial, 
           r.nota1_segundo_parcial, r.nota2_segundo_parcial, r.examen_segundo_parcial
    FROM registro_nota r
    JOIN materia m ON r.id_materia = m.id_materia
    WHERE r.id_estudiante = ? 
      AND r.id_his_academico = ?
";

$stmt_calificaciones = $conn->prepare($query_calificaciones);
$stmt_calificaciones->bind_param("ii", $id_estudiante, $id_his_academico);
$stmt_calificaciones->execute();
$result_calificaciones = $stmt_calificaciones->get_result();

// Consultar los promedios y nota final del estudiante para el periodo 3
$query_calificacion_final = "
    SELECT m.nombre AS materia, 
           c.promedio_primer_quimestre, c.promedio_segundo_quimestre, c.nota_final, c.estado_calificacion
    FROM calificacion c
    JOIN materia m ON c.id_materia = m.id_materia
    WHERE c.id_estudiante = ? 
      AND c.id_his_academico = ?
";

$stmt_calificacion_final = $conn->prepare($query_calificacion_final);
$stmt_calificacion_final->bind_param("ii", $id_estudiante, $id_his_academico);
$stmt_calificacion_final->execute();
$result_calificacion_final = $stmt_calificacion_final->get_result();

// Obtener el nivel del próximo año
$query_nivel_siguiente = "SELECT id_nivel FROM nivel WHERE id_nivel = ? + 1";
$stmt_nivel_siguiente = $conn->prepare($query_nivel_siguiente);
$stmt_nivel_siguiente->bind_param("i", $id_nivel_actual);
$stmt_nivel_siguiente->execute();
$result_nivel_siguiente = $stmt_nivel_siguiente->get_result();
$nivel_siguiente = $result_nivel_siguiente->fetch_assoc();

$mensaje_nivel = '';

if ($nivel_siguiente) {
    // Estudiante pasó de nivel
    $mensaje_nivel = 'Nota: Este estudiante se quedó de año. Imprima la libreta y espere el proceso de matriculación para que lo pueda inscribir en el nuevo año lectivo.';
} else {
    // Estudiante no pasó de nivel
    $mensaje_nivel = 'Nota: Este estudiante ha pasado de año. Imprima la libreta y espere el proceso de matriculación para que lo pueda inscribir en el nuevo año lectivo.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualización de Libretas Académicas | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Asegura que el cuerpo tenga al menos la altura de la ventana de visualización */
        }
        .header {
            background-color: #E62433; /* Rojo para el fondo del encabezado */
            color: #ffffff; /* Blanco para el texto */
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #003366; /* Azul marino para el borde inferior */
        }
        .footer {
            text-align: center;
            padding: 15px;
            background-color: #E62433; /* Rojo para el fondo del pie de página */
            color: #ffffff; /* Blanco para el texto del pie de página */
            border-top: 4px solid #003366; /* Azul marino para el borde superior del pie de página */
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 80px; /* Espacio suficiente para el footer */
        }
        h1 {
            margin: 0;
        }
        .title-container {
            text-align: center; /* Centrar el título */
            margin-bottom: 20px;
        }
        .title-container h1 {
            color: #003366; /* Color azul oscuro */
            margin: 0;
        }
        .header, .grades-table-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header td {
            padding: 8px;
            text-align: left;
        }
        .header .label {
            font-weight: bold;
            background-color: #e9ecef;
            border-bottom: 2px solid #dee2e6;
        }
        .header td {
            background-color: #eef7ff;
            border: 2px solid #003366;
            color: #000000; /* Blanco para el texto de las celdas en el encabezado */
        }
        .header .header-info {
            background-color: #003366; /* Rojo para el cuadro de curso */
            color: white;
        }
        .grades-table-wrapper {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 500px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background-color: #f9f9f9; /* Color de fondo para la tabla de calificaciones */
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grades-table th, .grades-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }
        .grades-table th {
            background-color: #E62433; /* Rojo oscuro para el encabezado de la tabla */
            color: #fff;
        }
        .grades-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .grades-table tbody tr:hover {
            background-color: #e9ecef;
        }
        .summary-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            color: white;
            cursor: pointer;
            text-align: center;
            border-radius: 4px;
            font-size: 16px;
        }
        .button-download {
            background-color: #dc3545; /* Rojo para el botón de PDF */
        }
        .button-print {
            background-color: #28a745; /* Verde para el botón de imprimir */
        }
        .actions {
            text-align: right;
            margin-top: 20px; /* Espacio para evitar que el contenido quede pegado al footer */
        }
        .search-wrapper {
            margin-bottom: 20px;
            text-align: center; /* Centrar el cuadro de búsqueda */
        }
        .search-wrapper input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            max-width: 400px; /* Limitar el ancho del cuadro de búsqueda */
        }
        .note {
            margin-top: 20px;
            padding: 10px;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema de Gestión UEBF</h1>
    </div>

    <div class="container">
        <div class="title-container">
            <h1>Libreta de Calificaciones</h1>
        </div>
        
        <!-- Encabezado -->
        <table class="header">
            <tr>
                <td class="label header-info">Nombre del Estudiante:</td>
                <td><?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?></td>
            </tr>
            <tr>
                <td class="label header-info">Nivel:</td>
                <td><?php echo htmlspecialchars($estudiante['nombre_nivel']); ?></td>
            </tr>
            <tr>
                <td class="label header-info">Subnivel:</td>
                <td><?php echo htmlspecialchars($estudiante['nombre_subnivel']); ?></td>
            </tr>
            <tr>
                <td class="label header-info">Paralelo:</td>
                <td><?php echo htmlspecialchars($estudiante['nombre_paralelo']); ?></td>
            </tr>
            <tr>
                <td class="label header-info">Especialidad:</td>
                <td><?php echo htmlspecialchars($estudiante['nombre_especialidad']); ?></td>
            </tr>
            <tr>
                <td class="label header-info">Jornada:</td>
                <td><?php echo htmlspecialchars($estudiante['nombre_jornada']); ?></td>
            </tr>
            <tr>
                <td class="label header-info">Año:</td>
                <td><?php echo htmlspecialchars($estudiante['año']); ?></td>
            </tr>
        </table>

        <!-- Filtros y Búsqueda -->
        <div class="search-wrapper">
            <input type="text" id="search-materia" placeholder="Buscar por materia..." oninput="filterTable()">
        </div>

        <!-- Detalle de Calificaciones con Scrollbars -->
        <div class="grades-table-wrapper scrollable-x">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th rowspan="2">Materia</th>
                        <th colspan="6">Periodo 1</th>
                        <th colspan="6">Periodo 2</th>
                        <th colspan="4">Periodo 3</th>
                    </tr>
                    <tr>
                        <th>Nota 1 Primer Parcial</th>
                        <th>Nota 2 Primer Parcial</th>
                        <th>Examen Primer Parcial</th>
                        <th>Nota 1 Segundo Parcial</th>
                        <th>Nota 2 Segundo Parcial</th>
                        <th>Examen Segundo Parcial</th>
                        <th>Nota 1 Primer Parcial</th>
                        <th>Nota 2 Primer Parcial</th>
                        <th>Examen Primer Parcial</th>
                        <th>Nota 1 Segundo Parcial</th>
                        <th>Nota 2 Segundo Parcial</th>
                        <th>Examen Segundo Parcial</th>
                        <th>Promedio Primer Q.</th>
                        <th>Promedio Segundo Q.</th>
                        <th>Nota Final</th>
                        <th>Estado Calificación</th>
                    </tr>
                </thead>
                <tbody id="grades-table-body">
                    <?php
                    // Agrupar materias y calificaciones por estudiante
                    $notas = [];

                    // Procesar las notas de los periodos 1 y 2
                    while ($row = $result_calificaciones->fetch_assoc()) {
                        $materia = htmlspecialchars($row['materia']);
                        $id_periodo = intval($row['id_periodo']);
                        
                        if (!isset($notas[$materia])) {
                            $notas[$materia] = [
                                'nota1_primer_parcial' => null,
                                'nota2_primer_parcial' => null,
                                'examen_primer_parcial' => null,
                                'nota1_segundo_parcial' => null,
                                'nota2_segundo_parcial' => null,
                                'examen_segundo_parcial' => null,
                                'promedio_primer_quimestre' => null,
                                'promedio_segundo_quimestre' => null,
                                'nota_final' => null,
                                'estado_calificacion' => null,
                            ];
                        }

                        if ($id_periodo == 1) {
                            $notas[$materia]['nota1_primer_parcial'] = htmlspecialchars($row['nota1_primer_parcial']);
                            $notas[$materia]['nota2_primer_parcial'] = htmlspecialchars($row['nota2_primer_parcial']);
                            $notas[$materia]['examen_primer_parcial'] = htmlspecialchars($row['examen_primer_parcial']);
                            $notas[$materia]['nota1_segundo_parcial'] = htmlspecialchars($row['nota1_segundo_parcial']);
                            $notas[$materia]['nota2_segundo_parcial'] = htmlspecialchars($row['nota2_segundo_parcial']);
                            $notas[$materia]['examen_segundo_parcial'] = htmlspecialchars($row['examen_segundo_parcial']);
                        } else if ($id_periodo == 2) {
                            $notas[$materia]['nota1_primer_parcial'] = htmlspecialchars($row['nota1_primer_parcial']);
                            $notas[$materia]['nota2_primer_parcial'] = htmlspecialchars($row['nota2_primer_parcial']);
                            $notas[$materia]['examen_primer_parcial'] = htmlspecialchars($row['examen_primer_parcial']);
                            $notas[$materia]['nota1_segundo_parcial'] = htmlspecialchars($row['nota1_segundo_parcial']);
                            $notas[$materia]['nota2_segundo_parcial'] = htmlspecialchars($row['nota2_segundo_parcial']);
                            $notas[$materia]['examen_segundo_parcial'] = htmlspecialchars($row['examen_segundo_parcial']);
                        }
                    }

                    // Procesar las notas del tercer periodo
                    while ($row = $result_calificacion_final->fetch_assoc()) {
                        $materia = htmlspecialchars($row['materia']);
                        
                        if (!isset($notas[$materia])) {
                            $notas[$materia] = [
                                'nota1_primer_parcial' => null,
                                'nota2_primer_parcial' => null,
                                'examen_primer_parcial' => null,
                                'nota1_segundo_parcial' => null,
                                'nota2_segundo_parcial' => null,
                                'examen_segundo_parcial' => null,
                                'promedio_primer_quimestre' => null,
                                'promedio_segundo_quimestre' => null,
                                'nota_final' => null,
                                'estado_calificacion' => null,
                            ];
                        }

                        $notas[$materia]['promedio_primer_quimestre'] = htmlspecialchars($row['promedio_primer_quimestre']);
                        $notas[$materia]['promedio_segundo_quimestre'] = htmlspecialchars($row['promedio_segundo_quimestre']);
                        $notas[$materia]['nota_final'] = htmlspecialchars($row['nota_final']);
                        $notas[$materia]['estado_calificacion'] = htmlspecialchars($row['estado_calificacion']);
                    }

                    // Mostrar las notas
                    foreach ($notas as $materia => $notas_materia) {
                        echo "<tr>";
                        echo "<td>" . $materia . "</td>";
                        echo "<td>" . $notas_materia['nota1_primer_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['nota2_primer_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['examen_primer_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['nota1_segundo_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['nota2_segundo_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['examen_segundo_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['nota1_primer_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['nota2_primer_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['examen_primer_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['nota1_segundo_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['nota2_segundo_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['examen_segundo_parcial'] . "</td>";
                        echo "<td>" . $notas_materia['promedio_primer_quimestre'] . "</td>";
                        echo "<td>" . $notas_materia['promedio_segundo_quimestre'] . "</td>";
                        echo "<td>" . $notas_materia['nota_final'] . "</td>";
                        echo "<td>" . $notas_materia['estado_calificacion'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Botones de Acción -->
        <div class="actions">
            <button class="button button-print" onclick="window.print()">Imprimir</button>
        </div>
        <!-- Mensaje de Estado Académico -->
        <div class="note">
            <p><?php echo htmlspecialchars($mensaje_nivel); ?></p>
        </div>
    </div>
    </div>
    <div class="footer">
        &copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.
    </div>

    <script>
        // Función para filtrar la tabla por materia
        function filterTable() {
            const input = document.getElementById('search-materia');
            const filter = input.value.toLowerCase();
            const rows = document.getElementById('grades-table-body').getElementsByTagName('tr');
            
            for (const row of rows) {
                const materiaCell = row.getElementsByTagName('td')[0];
                if (materiaCell) {
                    const materiaText = materiaCell.textContent || materiaCell.innerText;
                    if (materiaText.toLowerCase().indexOf(filter) > -1) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }
        }

    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.11/jspdf.plugin.autotable.min.js"></script>
</body>
</html>
