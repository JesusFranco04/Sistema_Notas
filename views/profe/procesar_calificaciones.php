<?php
session_start();
include('../../Crud/config.php');

try {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
        header("Location: ../../login.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'];
        $id_curso = intval($_POST['id_curso']);
        $id_materia = intval($_POST['id_materia']);
        $id_periodo = intval($_POST['id_periodo']);
        $id_his_academico = intval($_POST['id_his_academico']);

        if (!filter_var($id_his_academico, FILTER_VALIDATE_INT)) {
            $_SESSION['mensaje'] = "ID de historial académico no válido.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: registro_calificaciones.php?id_curso=$id_curso");
            exit();
        }

        $sql_historial = "SELECT * FROM historial_academico WHERE id_his_academico = ?";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("i", $id_his_academico);
        $stmt_historial->execute();
        $result_historial = $stmt_historial->get_result();
        $historial = $result_historial->fetch_assoc();
        $stmt_historial->close();

        if (!$historial) {
            $_SESSION['mensaje'] = "Historial académico no encontrado.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: registro_calificaciones.php?id_curso=$id_curso");
            exit();
        }

        if (isset($_POST['notas']) && is_array($_POST['notas'])) {
            $notas = $_POST['notas'];

            foreach ($notas as $key => $value) {
                foreach ($value as $estudiante_id => $nota) {
                    $notas[$key][$estudiante_id] = ($nota === "" ? 0 : floatval($nota));

                    if (!is_numeric($notas[$key][$estudiante_id]) || $notas[$key][$estudiante_id] < 0 || $notas[$key][$estudiante_id] > 100) {
                        $_SESSION['mensaje'] = "Las notas deben estar entre 0 y 100.";
                        $_SESSION['tipo_mensaje'] = "error";
                        header("Location: registro_calificaciones.php?id_curso=$id_curso");
                        exit();
                    }
                }
            }

            if ($accion === 'guardar' || $accion === 'modificar') {
                foreach ($notas as $id_estudiante => $notas_estudiante) {
                    $nota1_primer_parcial = $notas_estudiante['nota1_primer_parcial'] ?? 0;
                    $nota2_primer_parcial = $notas_estudiante['nota2_primer_parcial'] ?? 0;
                    $examen_primer_parcial = $notas_estudiante['examen_primer_parcial'] ?? 0;
                    $nota1_segundo_parcial = $notas_estudiante['nota1_segundo_parcial'] ?? 0;
                    $nota2_segundo_parcial = $notas_estudiante['nota2_segundo_parcial'] ?? 0;
                    $examen_segundo_parcial = $notas_estudiante['examen_segundo_parcial'] ?? 0;

                    $query_registro_nota = "
                        INSERT INTO registro_nota (id_estudiante, id_curso, id_materia, id_periodo, id_his_academico, nota1_primer_parcial, nota2_primer_parcial, examen_primer_parcial, nota1_segundo_parcial, nota2_segundo_parcial, examen_segundo_parcial) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                            nota1_primer_parcial = VALUES(nota1_primer_parcial), 
                            nota2_primer_parcial = VALUES(nota2_primer_parcial), 
                            examen_primer_parcial = VALUES(examen_primer_parcial), 
                            nota1_segundo_parcial = VALUES(nota1_segundo_parcial), 
                            nota2_segundo_parcial = VALUES(nota2_segundo_parcial), 
                            examen_segundo_parcial = VALUES(examen_segundo_parcial)
                    ";
                    $stmt_registro_nota = $conn->prepare($query_registro_nota);
                    $stmt_registro_nota->bind_param(
                        "iiiiidddddd",
                        $id_estudiante, $id_curso, $id_materia, $id_periodo, $id_his_academico,
                        $nota1_primer_parcial, $nota2_primer_parcial, $examen_primer_parcial,
                        $nota1_segundo_parcial, $nota2_segundo_parcial, $examen_segundo_parcial
                    );

                    if (!$stmt_registro_nota->execute()) {
                        error_log("Error en la ejecución de la consulta de registro_nota: " . $stmt_registro_nota->error);
                        $_SESSION['mensaje'] = "Error al guardar las calificaciones.";
                        $_SESSION['tipo_mensaje'] = "error";
                        header("Location: registro_calificaciones.php?id_curso=$id_curso");
                        exit();
                    }
                    $stmt_registro_nota->close();

                    $promedio_primer_quimestre = ($nota1_primer_parcial * 0.35) + ($nota2_primer_parcial * 0.35) + ($examen_primer_parcial * 0.30);
                    $promedio_segundo_quimestre = ($nota1_segundo_parcial * 0.35) + ($nota2_segundo_parcial * 0.35) + ($examen_segundo_parcial * 0.30);
                    $nota_final = ($promedio_primer_quimestre + $promedio_segundo_quimestre) / 2;

                    $estado_calificacion = 'R';

                    if ($estado_calificacion == 'R') {
                        $supletorio = isset($_POST['supletorio'][$id_estudiante]) ? floatval($_POST['supletorio'][$id_estudiante]) : 0;

                        if ($supletorio < 0) {
                            $supletorio = 0;
                        }

                        $nota_ajustada = min(($nota_final + $supletorio) / 2, 10);

                        if ($nota_ajustada >= 7) {
                            $estado_calificacion = 'A';
                        }
                    }

                    $query_calificacion = "
                        INSERT INTO calificacion (id_estudiante, id_curso, id_materia, id_periodo, id_his_academico, promedio_primer_quimestre, promedio_segundo_quimestre, nota_final, supletorio, estado_calificacion)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                            promedio_primer_quimestre = VALUES(promedio_primer_quimestre),
                            promedio_segundo_quimestre = VALUES(promedio_segundo_quimestre),
                            nota_final = VALUES(nota_final),
                            supletorio = VALUES(supletorio),
                            estado_calificacion = VALUES(estado_calificacion)
                    ";
                    $stmt_calificacion = $conn->prepare($query_calificacion);
                    $stmt_calificacion->bind_param(
                        "iiiiidddds",
                        $id_estudiante, $id_curso, $id_materia, $id_periodo, $id_his_academico,
                        $promedio_primer_quimestre, $promedio_segundo_quimestre,
                        $nota_final, $supletorio, $estado_calificacion
                    );

                    if (!$stmt_calificacion->execute()) {
                        error_log("Error en la ejecución de la consulta de calificacion: " . $stmt_calificacion->error);
                        $_SESSION['mensaje'] = "Error al guardar las calificaciones.";
                        $_SESSION['tipo_mensaje'] = "error";
                        header("Location: registro_calificaciones.php?id_curso=$id_curso");
                        exit();
                    }
                    $stmt_calificacion->close();
                }
                $_SESSION['mensaje'] = "Calificaciones guardadas exitosamente.";
                $_SESSION['tipo_mensaje'] = "success";

                if ($accion === 'eliminar') {
                    $sql_eliminar = "DELETE FROM registro_nota WHERE id_curso = ? AND id_materia = ? AND id_periodo = ? AND id_his_academico = ?";
                    $stmt_eliminar = $conn->prepare($sql_eliminar);
                    $stmt_eliminar->bind_param("iiii", $id_curso, $id_materia, $id_periodo, $id_his_academico);

                    if (!$stmt_eliminar->execute()) {
                        error_log("Error en la ejecución de la consulta de eliminación: " . $stmt_eliminar->error);
                        $_SESSION['mensaje'] = "Error al eliminar las calificaciones.";
                        $_SESSION['tipo_mensaje'] = "error";
                        header("Location: registro_calificaciones.php?id_curso=$id_curso");
                        exit();
                    }
                    $stmt_eliminar->close();
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Error en el procesamiento de calificaciones: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error en el procesamiento de calificaciones.";
    $_SESSION['tipo_mensaje'] = "error";
} finally {
    $conn->close();
    header("Location: registro_calificaciones.php?id_curso=$id_curso");
}
?>
