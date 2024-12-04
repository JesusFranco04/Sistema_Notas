<?php
session_start();
include('../../Crud/config.php');

try {
    // Verificar el rol del usuario
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
        header("Location: ../../login.php");
        exit();
    }

    // Verificar el método de solicitud
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['mensaje'] = "Método de solicitud no válido.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: registro_calificaciones.php");
        exit();
    }

    // Obtener y validar datos del formulario
    $accion = $_POST['accion'];
    $id_curso = filter_input(INPUT_POST, 'id_curso', FILTER_VALIDATE_INT);
    $id_materia = filter_input(INPUT_POST, 'id_materia', FILTER_VALIDATE_INT);
    $id_periodo = filter_input(INPUT_POST, 'id_periodo', FILTER_VALIDATE_INT);
    $id_his_academico = filter_input(INPUT_POST, 'id_his_academico', FILTER_VALIDATE_INT);

    if ($id_his_academico === false) {
        $_SESSION['mensaje'] = "ID de historial académico no válido.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: registro_calificaciones.php?id_curso=$id_curso");
        exit();
    }

    // Verificar existencia del historial académico
    $sql_historial = "SELECT 1 FROM historial_academico WHERE id_his_academico = ?";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("i", $id_his_academico);
    $stmt_historial->execute();
    if (!$stmt_historial->get_result()->num_rows) {
        $_SESSION['mensaje'] = "Historial académico no encontrado.";
        $_SESSION['tipo_mensaje'] = "error";
        $stmt_historial->close();
        header("Location: registro_calificaciones.php?id_curso=$id_curso");
        exit();
    }
    $stmt_historial->close();

// Iniciar transacción
$conn->begin_transaction();
try {
    if ($accion === 'eliminar') {
        eliminarRegistros($conn, $id_curso, $id_materia, $id_periodo, $id_his_academico);
        $_SESSION['mensaje'] = "La calificación ha sido eliminada correctamente.";
    } else if ($accion === 'guardar') {
        guardarNotas($conn, $id_curso, $id_materia, $id_periodo, $id_his_academico);
        $_SESSION['mensaje'] = "La calificación se ha guardado correctamente.";
    } else if ($accion === 'guardar_supletorio') {
        guardarSupletorio($conn, $id_curso, $id_materia, $id_his_academico);
        $_SESSION['mensaje'] = "La nota de supletorio se ha guardado con éxito.";
    }

    // Confirmar transacción
    $conn->commit();
    $_SESSION['tipo_mensaje'] = "success";
} catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }

    header("Location: registro_calificaciones.php?id_curso=$id_curso");
    exit();
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error en la transacción: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: registro_calificaciones.php?id_curso=$id_curso");
    exit();
}

function eliminarRegistros($conn, $id_curso, $id_materia, $id_periodo, $id_his_academico) {
    $query_delete_registro_nota = "
        DELETE FROM registro_nota
        WHERE id_curso = ? AND id_materia = ? AND id_periodo = ? AND id_his_academico = ?
    ";
    $stmt = $conn->prepare($query_delete_registro_nota);
    $stmt->bind_param("iiii", $id_curso, $id_materia, $id_periodo, $id_his_academico);
    if (!$stmt->execute()) {
        throw new Exception("Error al eliminar las calificaciones: " . $stmt->error);
    }
    $stmt->close();
}

function guardarNotas($conn, $id_curso, $id_materia, $id_periodo, $id_his_academico) {
    if (isset($_POST['notas']) && is_array($_POST['notas'])) {
        $notas = $_POST['notas'];

        foreach ($notas as $id_estudiante => $notas_estudiante) {
            // Validación y asignación de valores por defecto
            $notas_estudiante = array_map(function($valor) {
                return $valor === "" ? NULL : floatval($valor);
            }, $notas_estudiante);

            validarNotas($notas_estudiante);

            $stmt = $conn->prepare("
                INSERT INTO registro_nota (
                    id_estudiante, id_curso, id_materia, id_periodo, id_his_academico,
                    nota1_primer_parcial, nota2_primer_parcial, examen_primer_parcial,
                    nota1_segundo_parcial, nota2_segundo_parcial, examen_segundo_parcial
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    nota1_primer_parcial = COALESCE(VALUES(nota1_primer_parcial), nota1_primer_parcial), 
                    nota2_primer_parcial = COALESCE(VALUES(nota2_primer_parcial), nota2_primer_parcial), 
                    examen_primer_parcial = COALESCE(VALUES(examen_primer_parcial), examen_primer_parcial), 
                    nota1_segundo_parcial = COALESCE(VALUES(nota1_segundo_parcial), nota1_segundo_parcial), 
                    nota2_segundo_parcial = COALESCE(VALUES(nota2_segundo_parcial), nota2_segundo_parcial), 
                    examen_segundo_parcial = COALESCE(VALUES(examen_segundo_parcial), examen_segundo_parcial)
            ");
            $stmt->bind_param(
                "iiiiidddddd",
                $id_estudiante, $id_curso, $id_materia, $id_periodo, $id_his_academico,
                $notas_estudiante['nota1_primer_parcial'], $notas_estudiante['nota2_primer_parcial'], $notas_estudiante['examen_primer_parcial'],
                $notas_estudiante['nota1_segundo_parcial'], $notas_estudiante['nota2_segundo_parcial'], $notas_estudiante['examen_segundo_parcial']
            );
            if (!$stmt->execute()) {
                throw new Exception("Error al guardar el registro de notas del estudiante: " . $stmt->error);
            }
            $stmt->close();
        }

        calcularNotasFinales($conn, $id_curso, $id_materia, $id_his_academico);
    } else {
        $_SESSION['mensaje'] = "No se han enviado notas para procesar.";
        $_SESSION['tipo_mensaje'] = "error";
    }
}

function guardarSupletorio($conn, $id_curso, $id_materia, $id_his_academico) {
    $calificaciones = $_POST['calificaciones'];

    foreach ($calificaciones as $id_estudiante => $datos) {
        $supletorio = isset($datos['supletorio']) ? (float) $datos['supletorio'] : 0;

        $query = "SELECT promedio_primer_quimestre, promedio_segundo_quimestre FROM calificacion 
                  WHERE id_estudiante = ? AND id_curso = ? AND id_materia = ? AND id_his_academico = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $id_estudiante, $id_curso, $id_materia, $id_his_academico);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($promedio_primer_quimestre, $promedio_segundo_quimestre);
        $stmt->fetch();

        $nota_final = calcularNotaFinal($promedio_primer_quimestre, $promedio_segundo_quimestre, $supletorio);
        $estado_calificacion = determinarEstado($nota_final);

        $stmt = $conn->prepare("
            UPDATE calificacion 
            SET supletorio = ?, nota_final = ?, estado_calificacion = ?
            WHERE id_estudiante = ? AND id_curso = ? AND id_materia = ? AND id_his_academico = ?
        ");
        $stmt->bind_param("ddsiiii", $supletorio, $nota_final, $estado_calificacion, $id_estudiante, $id_curso, $id_materia, $id_his_academico);
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar la nota de supletorio: " . $stmt->error);
        }
        $stmt->close();
    }
}

function calcularNotasFinales($conn, $id_curso, $id_materia, $id_his_academico) {
    // Obtener datos de la tabla registro_nota
    $query = "SELECT id_estudiante, nota1_primer_parcial, nota2_primer_parcial, examen_primer_parcial, 
                     nota1_segundo_parcial, nota2_segundo_parcial, examen_segundo_parcial
              FROM registro_nota
              WHERE id_curso = ? AND id_materia = ? AND id_his_academico = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $id_curso, $id_materia, $id_his_academico);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_estudiante, $nota1_primer_parcial, $nota2_primer_parcial, $examen_primer_parcial, 
                        $nota1_segundo_parcial, $nota2_segundo_parcial, $examen_segundo_parcial);

    while ($stmt->fetch()) {
        // Cálculos
        $promedio_primer_quimestre = round((($nota1_primer_parcial + $nota2_primer_parcial) / 2) * 0.8 + $examen_primer_parcial * 0.2, 2);
        $promedio_segundo_quimestre = round((($nota1_segundo_parcial + $nota2_segundo_parcial) / 2) * 0.8 + $examen_segundo_parcial * 0.2, 2);
        $nota_final = round(($promedio_primer_quimestre + $promedio_segundo_quimestre) / 2, 2);
        $estado_calificacion = determinarEstado($nota_final);

        $stmt_update = $conn->prepare("
            INSERT INTO calificacion (id_estudiante, id_curso, id_materia, id_his_academico, promedio_primer_quimestre, 
                                      promedio_segundo_quimestre, nota_final, estado_calificacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                promedio_primer_quimestre = VALUES(promedio_primer_quimestre), 
                promedio_segundo_quimestre = VALUES(promedio_segundo_quimestre), 
                nota_final = VALUES(nota_final), 
                estado_calificacion = VALUES(estado_calificacion)
        ");
        $stmt_update->bind_param("iiiiddds", $id_estudiante, $id_curso, $id_materia, $id_his_academico, 
                                  $promedio_primer_quimestre, $promedio_segundo_quimestre, $nota_final, $estado_calificacion);
        if (!$stmt_update->execute()) {
            throw new Exception("Error al calcular las notas finales: " . $stmt_update->error);
        }
        $stmt_update->close();
    }
    $stmt->close();
}

function calcularNotaFinal($promedio_primer_quimestre, $promedio_segundo_quimestre, $supletorio) {
    if ($promedio_primer_quimestre < 7 || $promedio_segundo_quimestre < 7) {
        return round(($promedio_primer_quimestre + $promedio_segundo_quimestre + $supletorio) / 3, 2);
    }
    return round(($promedio_primer_quimestre + $promedio_segundo_quimestre) / 2, 2);
}

function determinarEstado($nota_final) {
    if ($nota_final >= 7) {
        return 'A'; // Aprobado
    } elseif ($nota_final < 7) {
        return 'R'; // Reprobado
    }
}

function validarNotas($notas) {
    foreach ($notas as $nota) {
        if ($nota !== NULL && ($nota < 0 || $nota > 10)) {
            throw new Exception("Las notas deben estar entre 0 y 10.");
        }
    }
}
?>