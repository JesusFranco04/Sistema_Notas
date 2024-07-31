<?php
session_start();
include('../../Crud/config.php');

// Verifica si el usuario es un profesor
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
    header("Location: ../../login.php");
    exit();
}

// Asegúrate de que id_curso esté definido en la URL
if (!isset($_GET['id_curso']) || !filter_var($_GET['id_curso'], FILTER_VALIDATE_INT)) {
    echo "ID de curso no válido.";
    exit();
}
$id_curso = intval($_GET['id_curso']);

// Obtener los detalles del curso
$sql_curso = "SELECT c.id_curso, h.id_his_academico, h.año AS año_academico, c.id_materia
              FROM curso c
              JOIN historial_academico h ON c.id_his_academico = h.id_his_academico
              WHERE c.id_curso = ?";
$stmt_curso = $conn->prepare($sql_curso);
if (!$stmt_curso) {
    error_log("Error en prepare: " . $conn->error);
    die("Error en la consulta de curso.");
}
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
if (!$result_curso) {
    error_log("Error en get_result: " . $stmt_curso->error);
    die("Error en la obtención de resultados del curso.");
}
$curso = $result_curso->fetch_assoc();
$stmt_curso->close();

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
                    WHERE id_his_academico = ?
                    ORDER BY apellidos ASC";
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

// Cerrar la conexión
$conn->close();
?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Calificaciones</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
    .tabcontent {
        display: none;
    }

    .tabcontent.active {
        display: block;
    }

    .tabs-container {
        margin-bottom: 20px;
    }

    .tabs {
        list-style-type: none;
        padding: 0;
    }

    .tabs li {
        display: inline;
        margin-right: 10px;
    }

    .tablink {
        background-color: #f1f1f1;
        border: none;
        cursor: pointer;
        padding: 10px 20px;
        text-align: center;
    }

    .tablink.active {
        background-color: #ddd;
    }

    .small-text {
        font-size: 0.75rem;
        color: #888;
    }

    .centered-input input {
        text-align: center;
    }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Calificaciones de estudiantes de <?php echo htmlspecialchars($nombre_materia); ?></h1>
        <?php if (isset($_GET['mensaje'])): ?>
        <?php if ($_GET['mensaje'] == 'exito'): ?>
        <div class="alert alert-success">Se guardaron las notas exitosamente</div>
        <?php elseif ($_GET['mensaje'] == 'error'): ?>
        <div class="alert alert-danger">No se guardaron las calificaciones</div>
        <?php elseif ($_GET['mensaje'] == 'faltan_datos'): ?>
        <div class="alert alert-warning">Debe llenar todos los campos</div>
        <?php endif; ?>
        <?php endif; ?>

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

            <?php if ($periodo['id_periodo'] == 1): ?>
            <form method="POST" action="procesar_calificaciones.php">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($id_curso); ?>">
                <input type="hidden" name="id_materia" value="<?php echo htmlspecialchars($id_materia); ?>">
                <input type="hidden" name="id_his_academico" value="<?php echo htmlspecialchars($curso['id_his_academico']); ?>">
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
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back();">Regresar</button>
                    <button type="submit" class="btn btn-primary" name="accion" value="guardar">Guardar</button>
                    <button type="submit" class="btn btn-danger" name="accion" value="eliminar">Eliminar</button>
                </div>
            </form>
            <?php elseif ($periodo['id_periodo'] == 2): ?>
            <form action="procesar_calificaciones.php" method="POST">
                <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($id_curso); ?>">
                <input type="hidden" name="id_materia" value="<?php echo htmlspecialchars($id_materia); ?>">
                <input type="hidden" name="id_his_academico" value="<?php echo htmlspecialchars($curso['id_his_academico']); ?>">
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
                <div class="d-flex justify-content-between mt-3">
                    <button type="submit" class="btn btn-primary" name="accion" value="guardar">Guardar</button>
                    <button type="submit" class="btn btn-danger" name="accion" value="eliminar">Eliminar</button>
                </div>
            </form>
            <?php elseif ($periodo['id_periodo'] == 3): ?>
            <form action="procesar_calificaciones.php" method="POST">
                <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($id_curso); ?>">
                <input type="hidden" name="id_materia" value="<?php echo htmlspecialchars($id_materia); ?>">
                <input type="hidden" name="id_his_academico" value="<?php echo htmlspecialchars($curso['id_his_academico']); ?>">
                <input type="hidden" name="id_periodo" value="<?php echo htmlspecialchars($periodo['id_periodo']); ?>">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre del Estudiante</th>
                            <th>Promedio Primer Quimestre</th>
                            <th>Promedio Segundo Quimestre</th>
                            <th>Nota Final</th>
                            <th>Supletorio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $index => $estudiante): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?>
                            </td>
                            <td class="centered-input"><input type="number"
                                    name="promedio_primer_quimestre[<?php echo $estudiante['id_estudiante']; ?>]"
                                    min="0" max="100" step="0.1"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['promedio_primer_quimestre']) : ''; ?>">
                            </td>
                            <td class="centered-input"><input type="number"
                                    name="promedio_segundo_quimestre[<?php echo $estudiante['id_estudiante']; ?>]"
                                    min="0" max="100" step="0.1"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['promedio_segundo_quimestre']) : ''; ?>">
                            </td>
                            <td class="centered-input"><input type="number"
                                    name="nota_final[<?php echo $estudiante['id_estudiante']; ?>]" min="0" max="100"
                                    step="0.1"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['nota_final']) : ''; ?>">
                            </td>
                            <td class="centered-input"><input type="number"
                                    name="supletorio[<?php echo $estudiante['id_estudiante']; ?>]" min="0" max="100"
                                    step="0.1"
                                    value="<?php echo isset($calificaciones[$estudiante['id_estudiante']]) ? htmlspecialchars($calificaciones[$estudiante['id_estudiante']]['supletorio']) : ''; ?>">
                            </td>
                            <td class="centered-input">
                                <?php
                                if (isset($calificaciones[$estudiante['id_estudiante']])) {
                                    $estado = $calificaciones[$estudiante['id_estudiante']]['estado_calificacion'];
                                    // Verifica si el estado está vacío o no está definido
                                    echo empty($estado) ? 'Pendiente' : ($estado == 'A' ? 'Aprobado' : ($estado == 'R' ? 'Reprobado' : 'Pendiente'));
                                } else {
                                    echo 'Pendiente';
                                }
                                ?>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between mt-3">
                    <button type="submit" class="btn btn-primary" name="accion" value="guardar">Guardar</button>
                    <button type="submit" class="btn btn-danger" name="accion" value="eliminar">Eliminar</button>
                </div>
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