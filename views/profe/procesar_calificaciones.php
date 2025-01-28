<?php
session_start();
include('../../Crud/config.php');

try {
    // Verificar si el usuario ha iniciado sesión y si su rol es "Profesor"
    if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Profesor'])) {
        // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
        header("Location: ../../login.php");
        exit(); // Asegurarse de que no se ejecute más código después de la redirección
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

function eliminarRegistroNota($conn, $id_curso, $id_materia, $id_periodo, $id_his_academico) {
    // Verificar si existen registros en la tabla registro_nota antes de intentar eliminar
    $check_query = "SELECT COUNT(*) FROM registro_nota WHERE id_curso = ? AND id_materia = ? AND id_periodo = ? AND id_his_academico = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("iiii", $id_curso, $id_materia, $id_periodo, $id_his_academico);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count == 0) {
        throw new Exception("No existen registros para eliminar en la tabla registro_nota.");
    }

    // Eliminar los registros de la tabla registro_nota
    $query = "DELETE FROM registro_nota WHERE id_curso = ? AND id_materia = ? AND id_periodo = ? AND id_his_academico = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $id_curso, $id_materia, $id_periodo, $id_his_academico);
    if (!$stmt->execute()) {
        throw new Exception("Error al eliminar registro_nota: " . $stmt->error);
    }
    $stmt->close();
}

function actualizarCalificacion($conn, $id_curso, $id_materia, $id_his_academico, $id_periodo) {
    // Consultar los promedios y la nota_final
    $query = "
        SELECT promedio_primer_quimestre, promedio_segundo_quimestre, nota_final
        FROM calificacion
        WHERE id_curso = ? AND id_materia = ? AND id_his_academico = ?
    ";
    $stmt_check = $conn->prepare($query);
    $stmt_check->bind_param("iii", $id_curso, $id_materia, $id_his_academico);
    $stmt_check->execute();
    $stmt_check->bind_result($promedio_primer_quimestre, $promedio_segundo_quimestre, $nota_final);
    $stmt_check->fetch();
    $stmt_check->close();

    // Si se eliminan las notas de id_periodo = 1
    if ($id_periodo == 1) {
        $query = "
            UPDATE calificacion
            SET
                promedio_primer_quimestre = NULL,
                nota_final = CASE
                    WHEN promedio_segundo_quimestre IS NOT NULL THEN promedio_segundo_quimestre
                    ELSE NULL
                END,
                supletorio = NULL,
                estado_calificacion = CASE
                    WHEN nota_final < 7 THEN 'R'
                    WHEN nota_final >= 7 THEN 'A'
                    ELSE NULL
                END
            WHERE id_curso = ? AND id_materia = ? AND id_his_academico = ?
        ";
    } 
    // Si se eliminan las notas de id_periodo = 2
    else if ($id_periodo == 2) {
        $query = "
            UPDATE calificacion
            SET
                promedio_segundo_quimestre = NULL,
                nota_final = CASE
                    WHEN promedio_primer_quimestre IS NOT NULL THEN promedio_primer_quimestre
                    ELSE NULL
                END,
                supletorio = NULL,
                estado_calificacion = CASE
                    WHEN nota_final < 7 THEN 'R'
                    WHEN nota_final >= 7 THEN 'A'
                    ELSE NULL
                END
            WHERE id_curso = ? AND id_materia = ? AND id_his_academico = ?
        ";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $id_curso, $id_materia, $id_his_academico);
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar la calificación en la tabla calificacion: " . $stmt->error);
    }
    $stmt->close();
}

function eliminarRegistros($conn, $id_curso, $id_materia, $id_periodo, $id_his_academico) {
    try {
        $conn->begin_transaction();
        
        // Eliminar registros de registro_nota
        eliminarRegistroNota($conn, $id_curso, $id_materia, $id_periodo, $id_his_academico);
        
        // Actualizar registros de calificacion
        actualizarCalificacion($conn, $id_curso, $id_materia, $id_his_academico, $id_periodo);
        
        // Confirmar transacción
        $conn->commit();
    } catch (Exception $e) {
        // Si hay un error, revertir la transacción
        $conn->rollback();
        throw new Exception("Error en la eliminación de registros: " . $e->getMessage());
    }
}

function guardarNotas($conn, $id_curso, $id_materia, $id_periodo, $id_his_academico) {
    if (isset($_POST['notas']) && is_array($_POST['notas'])) {
        $notas = $_POST['notas'];

        foreach ($notas as $id_estudiante => $notas_estudiante) {
            // Validación y asignación de valores por defecto
            $notas_estudiante = array_map(function($valor) {
                $valor = $valor === "" ? NULL : floatval($valor);
                // Validar que el valor esté entre 0 y 10
                if (!is_null($valor) && ($valor < 0 || $valor > 10)) {
                    throw new Exception("La nota debe estar entre 0 y 10. Valor recibido: $valor");
                }
                return $valor;
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

function validarNotas($notas) {
    foreach ($notas as $nota) {
        if (is_null($nota)) {
            return false; // Si alguna nota es NULL, la validación falla
        }
    }
    return true; // Todas las notas están completas
}

function calcularNotasFinales($conn, $id_curso, $id_materia, $id_his_academico) {
    // Obtener datos de la tabla registro_nota
    $query = "SELECT id_estudiante, id_periodo, 
                     nota1_primer_parcial, nota2_primer_parcial, examen_primer_parcial, 
                     nota1_segundo_parcial, nota2_segundo_parcial, examen_segundo_parcial
              FROM registro_nota
              WHERE id_curso = ? AND id_materia = ? AND id_his_academico = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $id_curso, $id_materia, $id_his_academico);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_estudiante, $id_periodo, 
                       $nota1_primer_parcial, $nota2_primer_parcial, $examen_primer_parcial, 
                       $nota1_segundo_parcial, $nota2_segundo_parcial, $examen_segundo_parcial);

    // Arreglo para guardar los datos de cada estudiante
    $calificaciones = [];

    // Recorrer las filas de la consulta
    while ($stmt->fetch()) {
        // Inicializar datos del estudiante si no existen en el arreglo
        if (!isset($calificaciones[$id_estudiante])) {
            $calificaciones[$id_estudiante] = [
                'promedio_primer_quimestre' => null,
                'promedio_segundo_quimestre' => null,
            ];
        }

        // Calcular el promedio del Primer Quimestre (id_periodo = 1)
        if ($id_periodo == 1) {
            $notas_primer_quimestre = [
                $nota1_primer_parcial, $nota2_primer_parcial, $examen_primer_parcial
            ];
            if (validarNotas($notas_primer_quimestre)) {
                $promedio_parcial1 = (($nota1_primer_parcial + $nota2_primer_parcial) / 2) * 0.7 + $examen_primer_parcial * 0.3;
                $calificaciones[$id_estudiante]['promedio_primer_quimestre'] = round($promedio_parcial1, 2);
            }
        }

        // Calcular el promedio del Segundo Quimestre (id_periodo = 2)
        if ($id_periodo == 2) {
            $notas_segundo_quimestre = [
                $nota1_segundo_parcial, $nota2_segundo_parcial, $examen_segundo_parcial
            ];
            if (validarNotas($notas_segundo_quimestre)) {
                $promedio_parcial2 = (($nota1_segundo_parcial + $nota2_segundo_parcial) / 2) * 0.7 + $examen_segundo_parcial * 0.3;
                $calificaciones[$id_estudiante]['promedio_segundo_quimestre'] = round($promedio_parcial2, 2);
            }
        }
    }

    // Guardar las calificaciones en la tabla 'calificacion'
    foreach ($calificaciones as $id_estudiante => $datos) {
        $nota_final = null;
        if (!is_null($datos['promedio_primer_quimestre']) && !is_null($datos['promedio_segundo_quimestre'])) {
            $nota_final = round(($datos['promedio_primer_quimestre'] + $datos['promedio_segundo_quimestre']) / 2, 2);
        }

        $estado_calificacion = is_null($nota_final) ? null : ($nota_final >= 7.0 ? "A" : "R");

        // Insertar o actualizar en la tabla 'calificacion'
        $stmt_update = $conn->prepare("
            INSERT INTO calificacion (id_estudiante, id_curso, id_materia, id_his_academico, 
                                      promedio_primer_quimestre, promedio_segundo_quimestre, nota_final, estado_calificacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                promedio_primer_quimestre = VALUES(promedio_primer_quimestre), 
                promedio_segundo_quimestre = VALUES(promedio_segundo_quimestre), 
                nota_final = VALUES(nota_final), 
                estado_calificacion = VALUES(estado_calificacion)
        ");
        $stmt_update->bind_param("iiiiddds", $id_estudiante, $id_curso, $id_materia, $id_his_academico, 
                                  $datos['promedio_primer_quimestre'], $datos['promedio_segundo_quimestre'], $nota_final, $estado_calificacion);

        if (!$stmt_update->execute()) {
            throw new Exception("Error al guardar la calificación del estudiante con ID $id_estudiante: " . $stmt_update->error);
        }
        $stmt_update->close();
    }

    $stmt->close();
}

function guardarSupletorio($conn, $id_curso, $id_materia, $id_his_academico) {
    $calificaciones = $_POST['calificaciones'];

    foreach ($calificaciones as $id_estudiante => $datos) {
        try {
            // Verificar si ya se ha registrado un supletorio
            $query_verificar = "
                SELECT supletorio 
                FROM calificacion 
                WHERE id_estudiante = ? AND id_curso = ? AND id_materia = ? AND id_his_academico = ?
            ";
            $stmt_verificar = $conn->prepare($query_verificar);
            $stmt_verificar->bind_param("iiii", $id_estudiante, $id_curso, $id_materia, $id_his_academico);
            $stmt_verificar->execute();
            $stmt_verificar->bind_result($supletorio_existente);
            $stmt_verificar->fetch();
            $stmt_verificar->close();

            // Si ya existe un supletorio, evitar modificarlo
            if (!is_null($supletorio_existente)) {
                throw new Exception("El supletorio ya fue ingresado para el estudiante con ID $id_estudiante. No se puede modificar.");
            }

            // Verificar que el supletorio sea un número válido
            if (!isset($datos['supletorio']) || !is_numeric($datos['supletorio'])) {
                throw new Exception("El valor del supletorio para el estudiante con ID $id_estudiante debe ser un número válido.");
            }

            // Convertir el supletorio a número flotante
            $supletorio = (float) $datos['supletorio'];

            // Validar que el valor del supletorio esté entre 0 y 10
            if ($supletorio < 0 || $supletorio > 10) {
                throw new Exception("El valor del supletorio debe estar entre 0 y 10 para el estudiante con ID $id_estudiante.");
            }

            // Consultar calificaciones actuales
            $query = "SELECT promedio_primer_quimestre, promedio_segundo_quimestre FROM calificacion 
                      WHERE id_estudiante = ? AND id_curso = ? AND id_materia = ? AND id_his_academico = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiii", $id_estudiante, $id_curso, $id_materia, $id_his_academico);
            $stmt->execute();
            $stmt->store_result();

            // Verificar si existen registros
            if ($stmt->num_rows === 0) {
                throw new Exception("No se encontraron calificaciones para el estudiante con ID $id_estudiante.");
            }

            $stmt->bind_result($promedio_primer_quimestre, $promedio_segundo_quimestre);
            $stmt->fetch();
            $stmt->close();

            // Calcular la nueva nota final con supletorio
            $nota_final = round(($promedio_primer_quimestre + $promedio_segundo_quimestre + $supletorio) / 3, 2);

            // Determinar el estado de la calificación
            $estado_calificacion = $nota_final >= 7 ? 'A' : 'R';

            // Actualizar calificaciones en la base de datos
            $stmt = $conn->prepare("
                UPDATE calificacion 
                SET supletorio = ?, nota_final = ?, estado_calificacion = ?
                WHERE id_estudiante = ? AND id_curso = ? AND id_materia = ? AND id_his_academico = ?
            ");
            $stmt->bind_param("ddsiiii", $supletorio, $nota_final, $estado_calificacion, $id_estudiante, $id_curso, $id_materia, $id_his_academico);
            if (!$stmt->execute()) {
                throw new Exception("Error al guardar el supletorio para el estudiante con ID $id_estudiante: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            // Registrar el error y continuar con el siguiente estudiante
            error_log("Error procesando al estudiante con ID $id_estudiante: " . $e->getMessage());
        }
    }
}

function determinarEstado($nota_final) {
    if ($nota_final >= 7) {
        return 'A'; // Aprobado
    } elseif ($nota_final < 7) {
        return 'R'; // Reprobado
    }
}
?>