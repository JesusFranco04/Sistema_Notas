<?php
include('../../Crud/config.php');

function obtenerPeriodosAcademicos($conn) {
    $sql = "SELECT id_periodo, nombre FROM periodo_academico";
    $result = $conn->query($sql);
    return $result && $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function obtenerCalificaciones($conn, $id_periodo) {
    $sql = "SELECT id_estudiante, nota1_primer_parcial, nota2_primer_parcial, examen_primer_parcial, 
                   nota1_segundo_parcial, nota2_segundo_parcial, examen_segundo_parcial 
            FROM registro_nota 
            WHERE id_periodo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_periodo);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result && $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function obtenerCalificacionesEstadisticas($conn, $id_periodo) {
    $sql = "SELECT id_estudiante, promedio_primer_quimestre, promedio_segundo_quimestre, 
                   nota_final, supletorio, estado_calificacion 
            FROM calificacion 
            WHERE id_periodo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_periodo);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result && $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

$periodos = obtenerPeriodosAcademicos($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Calificaciones</title>
    <style>
        .tabs {
            cursor: pointer;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f1f1f1;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .editable-table input {
            width: 100%;
        }
        .editable-table th, .editable-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
    </style>
    <script>
        function openTab(event, tabId) {
            let i, tabcontent, tabs;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";  
            }
            tabs = document.getElementsByClassName("tab");
            for (i = 0; i < tabs.length; i++) {
                tabs[i].className = tabs[i].className.replace(" active", "");
            }
            document.getElementById(tabId).style.display = "block";
            event.currentTarget.className += " active";
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Registro de Calificaciones</h1>
    <div class="tabs">
        <?php foreach ($periodos as $periodo): ?>
            <div class="tab" onclick="openTab(event, 'tab-<?php echo $periodo['id_periodo']; ?>')">
                <?php echo htmlspecialchars($periodo['nombre']); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_SESSION['mensaje']) && isset($_SESSION['tipo_mensaje'])): ?>
        <div class="alert <?php echo $_SESSION['tipo_mensaje'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
        </div>
        <?php
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        ?>
    <?php endif; ?>

    <?php foreach ($periodos as $periodo): ?>
        <div id="tab-<?php echo $periodo['id_periodo']; ?>" class="tab-content">
            <h2><?php echo htmlspecialchars($periodo['nombre']); ?></h2>
            <form action="procesar_calificaciones.php" method="post">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="id_periodo" value="<?php echo $periodo['id_periodo']; ?>">
                
                <table id="notas-<?php echo $periodo['id_periodo']; ?>" class="editable-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th colspan="3">Primer Parcial</th>
                            <th colspan="3">Segundo Parcial</th>
                        </tr>
                        <tr>
                            <th>N°</th>
                            <th>Nombre del Estudiante</th>
                            <th>Nota 1 <span>(35%)</span></th>
                            <th>Nota 2 <span>(35%)</span></th>
                            <th>Examen <span>(30%)</span></th>
                            <th>Nota 1 <span>(35%)</span></th>
                            <th>Nota 2 <span>(35%)</span></th>
                            <th>Examen <span>(30%)</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $calificaciones = obtenerCalificaciones($conn, $periodo['id_periodo']);
                        foreach ($calificaciones as $calificacion):
                            $id_estudiante = $calificacion['id_estudiante'];
                            $sql_estudiante = "SELECT nombres, apellidos FROM estudiante WHERE id_estudiante = ?";
                            $stmt_estudiante = $conn->prepare($sql_estudiante);
                            $stmt_estudiante->bind_param('i', $id_estudiante);
                            $stmt_estudiante->execute();
                            $result_estudiante = $stmt_estudiante->get_result();
                            $estudiante = $result_estudiante->fetch_assoc();
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($id_estudiante); ?></td>
                            <td><?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?></td>
                            <td><input type="number" name="notas[<?php echo htmlspecialchars($id_estudiante); ?>][nota1_primer_parcial]" step="0.01" value="<?php echo htmlspecialchars($calificacion['nota1_primer_parcial']); ?>"></td>
                            <td><input type="number" name="notas[<?php echo htmlspecialchars($id_estudiante); ?>][nota2_primer_parcial]" step="0.01" value="<?php echo htmlspecialchars($calificacion['nota2_primer_parcial']); ?>"></td>
                            <td><input type="number" name="notas[<?php echo htmlspecialchars($id_estudiante); ?>][examen_primer_parcial]" step="0.01" value="<?php echo htmlspecialchars($calificacion['examen_primer_parcial']); ?>"></td>
                            <td><input type="number" name="notas[<?php echo htmlspecialchars($id_estudiante); ?>][nota1_segundo_parcial]" step="0.01" value="<?php echo htmlspecialchars($calificacion['nota1_segundo_parcial']); ?>"></td>
                            <td><input type="number" name="notas[<?php echo htmlspecialchars($id_estudiante); ?>][nota2_segundo_parcial]" step="0.01" value="<?php echo htmlspecialchars($calificacion['nota2_segundo_parcial']); ?>"></td>
                            <td><input type="number" name="notas[<?php echo htmlspecialchars($id_estudiante); ?>][examen_segundo_parcial]" step="0.01" value="<?php echo htmlspecialchars($calificacion['examen_segundo_parcial']); ?>"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="submit" value="Guardar Calificaciones">
            </form>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>

