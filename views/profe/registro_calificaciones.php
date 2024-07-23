<?php
session_start();
include('../../Crud/config.php');

// Verifica si el usuario es un profesor
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
    header("Location: ../../login.php");
    exit();
}

// Asegúrate de que id_curso esté definido en la URL
if (!isset($_GET['id_curso'])) {
    echo "ID de curso no definido.";
    exit();
}

$id_curso = intval($_GET['id_curso']);

// Obtener los detalles del curso
$sql_curso = "SELECT c.id_curso, h.id_his_academico, h.año AS año_academico, c.id_materia
              FROM curso c
              JOIN historial_academico h ON c.id_his_academico = h.id_his_academico
              WHERE c.id_curso = ?";
$stmt_curso = $conn->prepare($sql_curso);
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
$curso = $result_curso->fetch_assoc();
$stmt_curso->close();

if (!$curso) {
    echo "Curso no encontrado.";
    exit();
}

// Obtener el nombre de la materia
$id_materia = $curso['id_materia'];
$sql_materia = "SELECT nombre FROM materia WHERE id_materia = ?";
$stmt_materia = $conn->prepare($sql_materia);
$stmt_materia->bind_param("i", $id_materia);
$stmt_materia->execute();
$result_materia = $stmt_materia->get_result();
$materia = $result_materia->fetch_assoc();
$stmt_materia->close();

$nombre_materia = $materia ? htmlspecialchars($materia['nombre']) : 'Materia no encontrada';

// Obtener la lista de estudiantes
$id_his_academico = $curso['id_his_academico'];
$sql_estudiantes = "SELECT id_estudiante, nombres, apellidos
                    FROM estudiante
                    WHERE id_his_academico = ?";
$stmt_estudiantes = $conn->prepare($sql_estudiantes);
$stmt_estudiantes->bind_param("i", $id_his_academico);
$stmt_estudiantes->execute();
$result_estudiantes = $stmt_estudiantes->get_result();
$estudiantes = $result_estudiantes->fetch_all(MYSQLI_ASSOC);
$stmt_estudiantes->close();

// Obtener los periodos académicos
function obtenerPeriodosAcademicos($conn) {
    $sql = "SELECT id_periodo, nombre, estado FROM periodo_academico";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$periodos = obtenerPeriodosAcademicos($conn);


$sql_calificaciones = "SELECT c.id_estudiante, c.promedio_primer_quimestre, c.promedio_segundo_quimestre, c.nota_final, c.supletorio, c.estado_calificacion
    FROM calificacion c
    JOIN estudiante e ON c.id_estudiante = e.id_estudiante
    WHERE e.id_his_academico = ?";

$stmt = $conn->prepare($sql_calificaciones);
$stmt->bind_param("i", $id_his_academico);
$stmt->execute();
$result = $stmt->get_result();

$calificaciones = [];
while ($row = $result->fetch_assoc()) {
    $calificaciones[$row['id_estudiante']] = $row;
}


// Cerrar la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro de Calificaciones</title>
    <link rel="stylesheet" href="path/to/your/bootstrap.css">
    <style>
    .tabcontent {
        display: none;
    }

    .tabcontent.active {
        display: block;
    }

    .centered-input input {
        text-align: center;
    }

    .small-text {
        font-size: 0.75em;
        color: #666;
    }

    .partial-header {
        text-align: center;
    }

    .tabs-container {
        text-align: right;
        margin-bottom: 20px;
    }

    .tabs {
        display: inline-block;
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .tabs li {
        display: inline;
    }

    .tabs button {
        padding: 10px 15px;
        margin-left: 5px;
        border: none;
        cursor: pointer;
    }

    .tabs button.active {
        background-color: #ccc;
        font-weight: bold;
    }

    .tabs button.disabled {
        background-color: #e0e0e0;
        cursor: not-allowed;
    }

    .tabcontent {
        display: none;
    }

    .tabcontent.active {
        display: block;
    }

    .small-text {
        font-size: 0.8em;
        color: #666;
    }

    .first-partial .small-text {
        color: #007bff;
        /* Color para el primer parcial */
    }

    .second-partial .small-text {
        color: #28a745;
        /* Color para el segundo parcial */
    }

    .partial-header {
        text-align: center;
    }

    .centered-input {
        text-align: center;
    }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Calificaciones de estudiantes de <?php echo $nombre_materia; ?></h1>

        <div class="tabs-container">
            <ul class="tabs">
                <?php foreach ($periodos as $periodo): ?>
                <li>
                    <button class="tablink <?php echo $periodo['estado'] == '0' ? 'disabled' : ''; ?>"
                        onclick="openTab(event, 'tab<?php echo $periodo['id_periodo']; ?>')">
                        <?php echo htmlspecialchars($periodo['nombre']); ?>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php foreach ($periodos as $periodo): ?>
        <div id="tab<?php echo $periodo['id_periodo']; ?>"
            class="tabcontent <?php echo $periodo['estado'] == '1' ? 'active' : ''; ?>">
            <h2><?php echo htmlspecialchars($periodo['nombre']); ?></h2>

            <?php if ($periodo['id_periodo'] == 1 || $periodo['id_periodo'] == 2): ?>
            <form action="procesar_calificaciones.php" method="POST">
                <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($id_curso); ?>">
                <input type="hidden" name="id_his_academico"
                    value="<?php echo htmlspecialchars($curso['id_his_academico']); ?>">
                <input type="hidden" name="id_periodo" value="<?php echo htmlspecialchars($periodo['id_periodo']); ?>">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre del Estudiante</th>
                            <th colspan="3" class="partial-header">Primer Parcial</th>
                            <th colspan="3" class="partial-header">Segundo Parcial</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th>Nota 1 <span class="small-text">(35%)</span></th>
                            <th>Nota 2 <span class="small-text">(35%)</span></th>
                            <th>Examen <span class="small-text">(30%)</span></th>
                            <th>Nota 1 <span class="small-text">(35%)</span></th>
                            <th>Nota 2 <span class="small-text">(35%)</span></th>
                            <th>Examen <span class="small-text">(30%)</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $index => $estudiante): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?>
                            </td>
                            <td class="centered-input"><input type="number"
                                    name="nota1_primer_parcial[<?php echo $estudiante['id_estudiante']; ?>]" min="0"
                                    max="100" step="0.1"></td>
                            <td class="centered-input"><input type="number"
                                    name="nota2_primer_parcial[<?php echo $estudiante['id_estudiante']; ?>]" min="0"
                                    max="100" step="0.1"></td>
                            <td class="centered-input"><input type="number"
                                    name="examen_primer_parcial[<?php echo $estudiante['id_estudiante']; ?>]" min="0"
                                    max="100" step="0.1"></td>
                            <td class="centered-input"><input type="number"
                                    name="nota1_segundo_parcial[<?php echo $estudiante['id_estudiante']; ?>]" min="0"
                                    max="100" step="0.1"></td>
                            <td class="centered-input"><input type="number"
                                    name="nota2_segundo_parcial[<?php echo $estudiante['id_estudiante']; ?>]" min="0"
                                    max="100" step="0.1"></td>
                            <td class="centered-input"><input type="number"
                                    name="examen_segundo_parcial[<?php echo $estudiante['id_estudiante']; ?>]" min="0"
                                    max="100" step="0.1"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary">Guardar Calificaciones</button>
            </form>
            <?php elseif ($periodo['id_periodo'] == 3): ?>
            <form action="procesar_calificaciones.php" method="POST">
                <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($id_curso); ?>">
                <input type="hidden" name="id_his_academico"
                    value="<?php echo htmlspecialchars($curso['id_his_academico']); ?>">
                <input type="hidden" name="id_periodo" value="<?php echo htmlspecialchars($periodo['id_periodo']); ?>">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre del Estudiante</th>
                            <th class="text-center">Promedio Primer Quimestre</th>
                            <th class="text-center">Promedio Segundo Quimestre</th>
                            <th class="text-center">Nota Final</th>
                            <th class="text-center">Supletorio</th>
                            <th class="text-center">Estado de Calificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $index => $estudiante): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?>
                            </td>
                            <td class="text-center">
                                <input type="number"
                                    name="promedio_primer_quimestre[<?php echo $estudiante['id_estudiante']; ?>]"
                                    min="0" max="100" step="0.1"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]['promedio_primer_quimestre']) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['promedio_primer_quimestre']) : ''; ?>">
                            </td>
                            <td class="text-center">
                                <input type="number"
                                    name="promedio_segundo_quimestre[<?php echo $estudiante['id_estudiante']; ?>]"
                                    min="0" max="100" step="0.1"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]['promedio_segundo_quimestre']) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['promedio_segundo_quimestre']) : ''; ?>">
                            </td>
                            <td class="text-center">
                                <input type="number" name="nota_final[<?php echo $estudiante['id_estudiante']; ?>]"
                                    min="0" max="100" step="0.1"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]['nota_final']) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['nota_final']) : ''; ?>">
                            </td>
                            <td class="text-center">
                                <input type="number" name="supletorio[<?php echo $estudiante['id_estudiante']; ?>]"
                                    min="0" max="100" step="0.1" <?php
                        // Obtener el estado de calificación
                        $estado_calificacion = isset($calificaciones[$estudiante['id_estudiante']]['estado_calificacion']) ? $calificaciones[$estudiante['id_estudiante']]['estado_calificacion'] : '';

                        // Habilitar el campo supletorio solo si el estado es 'R'
                        if ($estado_calificacion === 'R') {
                            echo 'value="' . (isset($calificaciones[$estudiante['id_estudiante']]['supletorio']) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['supletorio']) : '') . '"';
                        } else {
                            echo 'disabled';
                        }
                        ?>>
                            </td>
                            <td class="text-center">
                                <!-- Mostrar el estado de calificación como texto -->
                                <?php echo isset($calificaciones[$estudiante['id_estudiante']]['estado_calificacion']) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['estado_calificacion']) : 'No asignado'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary">Guardar Calificaciones</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

    </div>

    <script>
    function openTab(evt, tabId) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablink");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabId).style.display = "block";
        evt.currentTarget.className += " active";
    }
    </script>
</body>

</html>