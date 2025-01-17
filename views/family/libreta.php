<?php
session_start();

// Verificar si el usuario ha iniciado sesión y si su rol es "Padre"
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
    <title>Libretas Académicas | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <style>
    /* Reset global */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
        color: #163f6b;
        /* Azul */
        background-color: #ffffff;
        /* Blanco */
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        /* Asegura que el cuerpo tenga al menos la altura de la ventana de visualización */
        overflow-x: hidden;
    }

    .header {
        background-color: #a20e14;
        /* Rojo oscuro */
        color: #ffffff;
        /* Blanco */
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Footer */
    footer {
        background-color: #a20e14;
        color: white;
        text-align: center;
        padding: 20px;
        margin-top: auto;
        width: 100%;
        box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.2);
        font-size: 1rem;
    }

    footer p {
        margin: 0;
        line-height: 1.5;
        color: white;
    }

    .container {
        max-width: 1000px;
        margin: auto;
        background-color: white;
        /* Blanco */
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 80px;
        /* Espacio suficiente para el footer */
    }

    h1 {
        margin: 0;
        font-size: 2em;
        /* Tamaño de fuente escalable */
    }

    .title-container {
        text-align: center;
        /* Centrar el título */
        margin-bottom: 20px;
    }

    .title-container h1 {
        color: #163f6b;
        /* Azul */
        margin: 0;
    }

    .header,
    .grades-table-wrapper {
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
        background-color: #f5f5f7;
        /* Gris claro */
        border-bottom: 2px solid #dbdbe2;
        /* Gris oscuro */
    }

    .header td {
        background-color: #ecf0f1;
        /* Gris claro */
        border: 2px solid #163f6b;
        /* Azul */
        color: #000000;
        /* Negro */
    }

    .header .header-info {
        background-color: #163f6b;
        /* Azul */
        color: white;
    }

    .grades-table-wrapper {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 500px;
        border: 1px solid #dfdfdf;
        /* Gris oscuro */
        border-radius: 4px;
        background-color: #f7fafd;
        /* Azul claro */
    }

    .grades-table {
        width: 100%;
        border-collapse: collapse;
    }

    .grades-table th,
    .grades-table td {
        border: 1px solid #dfdfdf;
        /* Gris oscuro */
        padding: 8px;
        text-align: center;
    }

    .grades-table th {
        background-color: #a20e14;
        /* Rojo oscuro */
        color: #ffffff;
        /* Blanco */
    }

    .grades-table tbody tr:nth-child(even) {
        background-color: #ecf0f1;
        /* Gris claro */
    }

    .grades-table tbody tr:hover {
        background-color: #eaecef;
        /* Gris claro */
    }

    .summary-row {
        font-weight: bold;
        background-color: #eaecef;
        /* Gris claro */
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
        transition: background-color 0.3s ease;
    }

    .button-download {
        background-color: #324b26;
        /* Verde */
    }

    .button-download:hover {
        background-color: #002500;
        /* Verde más oscuro */
    }

    .button-print {
        background-color: #163f6b;
        /* Azul */
    }

    .button-print:hover {
        background-color: #0e2643;
        /* Azul más oscuro */
    }

    .actions {
        text-align: right;
        margin-top: 20px;
        /* Espacio para evitar que el contenido quede pegado al footer */
    }

    .search-wrapper {
        margin-bottom: 20px;
        text-align: center;
        /* Centrar el cuadro de búsqueda */
    }

    .search-wrapper input {
        padding: 10px;
        border: 1px solid #b0b0b0;
        /* Gris oscuro */
        border-radius: 4px;
        width: 100%;
        max-width: 400px;
        /* Limitar el ancho del cuadro de búsqueda */
    }

    .note {
        margin-top: 20px;
        padding: 10px;
        background-color: #edffea;
        /* Verde claro */
        border: 1px solid #c0d9b6;
        /* Verde */
        border-radius: 4px;
        color: #002500;
        /* Verde oscuro */
    }

    /* Estilos responsivos */
    @media (max-width: 768px) {

        .header,
        .footer {
            padding: 5px;
            margin: 0;
            width: 100%;
            text-align: center;
            overflow-x: hidden;
        }

        .header img,
        .footer img {
            max-width: 100%;
            height: auto;
        }



        .button {
            padding: 8px 16px;
            font-size: 14px;
        }
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
                        <th colspan="6">Primer Quimestre</th>
                        <th colspan="6">Segundo Quimestre</th>
                        <th colspan="4">Nota Final</th>
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
            <!-- Botón para imprimir -->
            <button class="button button-print" onclick="printFile(this)" data-id-estudiante="<?= $id_estudiante; ?>"
                data-id-his-academico="<?= $id_his_academico; ?>">
                Imprimir
            </button>

            <!-- Mensaje de Estado Académico -->
            <div class="note">
                <p><?php echo htmlspecialchars($mensaje_nivel); ?></p>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

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

    // Función para imprimir el archivo PDF automáticamente
    function printFile(button) {
        // Obtiene los valores dinámicos desde los atributos del botón
        const idEstudiante = button.getAttribute('data-id-estudiante');
        const idHisAcademico = button.getAttribute('data-id-his-academico');

        if (idEstudiante && idHisAcademico) {
            // Construye la URL del archivo a imprimir
            const url = `reporte_libreta.php?id_estudiante=${idEstudiante}&id_his_academico=${idHisAcademico}`;

            // Abre el archivo en una nueva ventana
            const newWindow = window.open(url, '_blank');

            // Ejecuta automáticamente la impresión cuando el archivo termine de cargar
            newWindow.onload = function() {
                newWindow.print();
            };
        } else {
            alert("Datos del estudiante no disponibles.");
        }
    }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.11/jspdf.plugin.autotable.min.js"></script>
</body>

</html>