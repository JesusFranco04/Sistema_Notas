<?php
session_start();
include('../../Crud/config.php');

// Verifica si el usuario es un profesor
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'];
    $id_curso = intval($_POST['id_curso']);
    $id_materia = intval($_POST['id_materia']);
    $id_his_academico = intval($_POST['id_his_academico']);
    $id_periodo = intval($_POST['id_periodo']);

    // Verifica que se ha enviado el id_his_academico
    if (!isset($_POST['id_his_academico']) || !filter_var($_POST['id_his_academico'], FILTER_VALIDATE_INT)) {
        $_SESSION['mensaje'] = "ID de historial académico no válido.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: registro_calificaciones.php?id_curso=$id_curso");
        exit();
    }

    // Obtener detalles del historial académico
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

    // Obtener las calificaciones desde el formulario
    $notas = [
        'nota1_primer_parcial' => $_POST['nota1_primer_parcial'],
        'nota2_primer_parcial' => $_POST['nota2_primer_parcial'],
        'examen_primer_parcial' => $_POST['examen_primer_parcial'],
        'nota1_segundo_parcial' => $_POST['nota1_segundo_parcial'],
        'nota2_segundo_parcial' => $_POST['nota2_segundo_parcial'],
        'examen_segundo_parcial' => $_POST['examen_segundo_parcial']
    ];

    // Validar notas
    foreach ($notas as $key => $value) {
        foreach ($value as $estudiante_id => $nota) {
            if (empty($nota)) {
                $notas[$key][$estudiante_id] = null; // Permitir valor nulo
                continue;
            }
            $nota = floatval($nota); // Convertir a número decimal
            if (!is_numeric($nota) || $nota < 0 || $nota > 100) {
                $_SESSION['mensaje'] = "Las notas deben estar entre 0 y 100.";
                $_SESSION['tipo_mensaje'] = "error";
                header("Location: registro_calificaciones.php?id_curso=$id_curso");
                exit();
            }
            $notas[$key][$estudiante_id] = $nota; // Actualizar el array con el valor decimal
        }
    }

    // Función para ejecutar consultas de guardado o modificación
    function ejecutarConsulta($conn, $sql, $params, $id_curso) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(...$params);
        if (!$stmt->execute()) {
            error_log("Error en execute: " . $stmt->error);
            $_SESSION['mensaje'] = "Error en la operación.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: registro_calificaciones.php?id_curso=$id_curso");
            exit();
        }
    }

    $conn->begin_transaction(); // Iniciar transacción

    try {
        if ($accion == 'guardar' || $accion == 'modificar') {
            foreach ($notas['nota1_primer_parcial'] as $estudiante_id => $nota1_primer_parcial) {
                // Obtener valores actuales de las notas si existen
                $sql_check = "SELECT * FROM registro_nota WHERE id_estudiante = ? AND id_curso = ? AND id_materia = ? AND id_periodo = ? AND id_his_academico = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("iiiii", $estudiante_id, $id_curso, $id_materia, $id_periodo, $id_his_academico);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                $registro_actual = $result_check->fetch_assoc();
                $stmt_check->close();
            
                // Asignar valores actuales para campos no enviados
                $nota1_primer_parcial = $notas['nota1_primer_parcial'][$estudiante_id] ?? $registro_actual['nota1_primer_parcial'];
                $nota2_primer_parcial = $notas['nota2_primer_parcial'][$estudiante_id] ?? $registro_actual['nota2_primer_parcial'];
                $examen_primer_parcial = $notas['examen_primer_parcial'][$estudiante_id] ?? $registro_actual['examen_primer_parcial'];
                $nota1_segundo_parcial = $notas['nota1_segundo_parcial'][$estudiante_id] ?? $registro_actual['nota1_segundo_parcial'];
                $nota2_segundo_parcial = $notas['nota2_segundo_parcial'][$estudiante_id] ?? $registro_actual['nota2_segundo_parcial'];
                $examen_segundo_parcial = $notas['examen_segundo_parcial'][$estudiante_id] ?? $registro_actual['examen_segundo_parcial'];
            
                if ($result_check->num_rows > 0) {
                    // Registro existe, actualizar
                    $sql = "UPDATE registro_nota SET nota1_primer_parcial = ?, nota2_primer_parcial = ?, examen_primer_parcial = ?, nota1_segundo_parcial = ?, nota2_segundo_parcial = ?, examen_segundo_parcial = ? WHERE id_estudiante = ? AND id_curso = ? AND id_materia = ? AND id_periodo = ? AND id_his_academico = ?";
                    $params = ["ddddddiiiii", $nota1_primer_parcial, $nota2_primer_parcial, $examen_primer_parcial, $nota1_segundo_parcial, $nota2_segundo_parcial, $examen_segundo_parcial, $estudiante_id, $id_curso, $id_materia, $id_periodo, $id_his_academico];
                } else {
                    // Registro no existe, insertar
                    $sql = "INSERT INTO registro_nota (id_estudiante, id_curso, id_materia, id_periodo, id_his_academico, nota1_primer_parcial, nota2_primer_parcial, examen_primer_parcial, nota1_segundo_parcial, nota2_segundo_parcial, examen_segundo_parcial) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = ["iiiiiiddddd", $estudiante_id, $id_curso, $id_materia, $id_periodo, $id_his_academico, $nota1_primer_parcial, $nota2_primer_parcial, $examen_primer_parcial, $nota1_segundo_parcial, $nota2_segundo_parcial, $examen_segundo_parcial];
                }
            
                ejecutarConsulta($conn, $sql, $params, $id_curso);
            }
        } elseif ($accion == 'eliminar') {
            $sql = "DELETE FROM registro_nota WHERE id_curso = ? AND id_materia = ? AND id_periodo = ? AND id_his_academico = ?";
            $params = ["iiii", $id_curso, $id_materia, $id_periodo, $id_his_academico];
            ejecutarConsulta($conn, $sql, $params, $id_curso);
        }

        $conn->commit(); // Confirmar transacción
        $_SESSION['mensaje'] = "Operación realizada con éxito.";
        $_SESSION['tipo_mensaje'] = "exito";
        header("Location: registro_calificaciones.php?id_curso=$id_curso");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // Revertir transacción en caso de error
        error_log("Error en transacción: " . $e->getMessage());
        $_SESSION['mensaje'] = "Error en la operación.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: registro_calificaciones.php?id_curso=$id_curso");
        exit();
    }
}
?>
