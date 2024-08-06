<?php
include('../../Crud/config.php');

try {

    // Verificar que el método de solicitud es POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['mensaje'] = "Método de solicitud no válido.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: registro_calificaciones.php");
        exit();
    }

    // Obtener datos del formulario
    $accion = $_POST['accion'];
    $id_curso = intval($_POST['id_curso']);
    $id_materia = intval($_POST['id_materia']);
    $id_periodo = intval($_POST['id_periodo']);
    $id_his_academico = intval($_POST['id_his_academico']);
    $cedula_profesor = $_SESSION['cedula']; // Cédula del profesor logueado

    // Validar ID de historial académico
    if (!filter_var($id_his_academico, FILTER_VALIDATE_INT)) {
        $_SESSION['mensaje'] = "ID de historial académico no válido.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: registro_calificaciones.php?id_curso=$id_curso");
        exit();
    }

    // Función para validar el historial académico
    function validar_historial_academico($conn, $id_his_academico, $id_curso) {
        $sql_historial = "SELECT 1 FROM historial_academico WHERE id_his_academico = ?";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("i", $id_his_academico);
        $stmt_historial->execute();
        if (!$stmt_historial->get_result()->num_rows) {
            $stmt_historial->close();
            handle_error("Historial académico no encontrado.", "registro_calificaciones.php?id_curso=$id_curso");
        }
        $stmt_historial->close();
    }
    
    validar_historial_academico($conn, $id_his_academico, $id_curso);
    
    // Iniciar transacción
    $conn->begin_transaction();
    try {
        if ($accion === 'eliminar') {
            // Eliminar registros solo en el período actual (de periodo 1 o 2)
            $query_delete_registro_nota = "
                DELETE FROM registro_nota
                WHERE id_curso = ? AND id_materia = ? AND id_periodo = ? AND id_his_academico = ?
            ";
            $stmt_delete_registro_nota = $conn->prepare($query_delete_registro_nota);
            if (!$stmt_delete_registro_nota) {
                throw new Exception("Error al preparar la consulta de eliminación: " . $conn->error);
            }
            $stmt_delete_registro_nota->bind_param("iiii", $id_curso, $id_materia, $id_periodo, $id_his_academico);
            if (!$stmt_delete_registro_nota->execute()) {
                throw new Exception("Error al eliminar las calificaciones: " . $stmt_delete_registro_nota->error);
            }
            $stmt_delete_registro_nota->close();

            // Actualizar calificaciones para reflejar la eliminación
            $query_update_calificacion = "
                UPDATE calificacion
                SET promedio_primer_quimestre = CASE WHEN ? = 1 THEN NULL ELSE promedio_primer_quimestre END,
                    promedio_segundo_quimestre = CASE WHEN ? = 2 THEN NULL ELSE promedio_segundo_quimestre END,
                    nota_final = CASE WHEN ? = 1 OR ? = 2 THEN NULL ELSE nota_final END,
                    estado_calificacion = CASE WHEN ? = 1 OR ? = 2 THEN 'Borrado' ELSE estado_calificacion END
                WHERE id_curso = ? AND id_materia = ? AND id_his_academico = ?
            ";
            $stmt_update_calificacion = $conn->prepare($query_update_calificacion);
            if (!$stmt_update_calificacion) {
                throw new Exception("Error al preparar la consulta de actualización de calificaciones: " . $conn->error);
            }
            $stmt_update_calificacion->bind_param("iiiiiii", $id_periodo, $id_periodo, $id_periodo, $id_periodo, $id_periodo, $id_curso, $id_materia, $id_his_academico);
            if (!$stmt_update_calificacion->execute()) {
                throw new Exception("Error al actualizar las calificaciones: " . $stmt_update_calificacion->error);
            }
            $stmt_update_calificacion->close();

            $_SESSION['mensaje'] = "Registros eliminados exitosamente.";
            $_SESSION['tipo_mensaje'] = "success";
            
        } else if ($accion === 'guardar') {
            // Guardar notas
            if (isset($_POST['notas']) && is_array($_POST['notas'])) {
                $notas = $_POST['notas'];

                foreach ($notas as $id_estudiante => $notas_estudiante) {
                    foreach ($notas_estudiante as $clave => $valor) {
                        // Convertir valores vacíos a NULL y validar notas
                        $notas_estudiante[$clave] = ($valor === "" ? NULL : floatval($valor));
                        if ($notas_estudiante[$clave] !== NULL && ($notas_estudiante[$clave] < 0 || $notas_estudiante[$clave] > 10)) {
                            throw new Exception("Las notas deben estar entre 0 y 10.");
                        }
                    }

                    $nota1_primer_parcial = $notas_estudiante['nota1_primer_parcial'] ?? NULL;
                    $nota2_primer_parcial = $notas_estudiante['nota2_primer_parcial'] ?? NULL;
                    $examen_primer_parcial = $notas_estudiante['examen_primer_parcial'] ?? NULL;
                    $nota1_segundo_parcial = $notas_estudiante['nota1_segundo_parcial'] ?? NULL;
                    $nota2_segundo_parcial = $notas_estudiante['nota2_segundo_parcial'] ?? NULL;
                    $examen_segundo_parcial = $notas_estudiante['examen_segundo_parcial'] ?? NULL;
                    $supletorio = $_POST['supletorio'][$id_estudiante] ?? 0;

                    // Calcular promedios para el período actual
                    $promedio_primer_quimestre = $id_periodo == 1 ? (
                        ($nota1_primer_parcial ?? 0) * 0.35 +
                        ($nota2_primer_parcial ?? 0) * 0.35 +
                        ($examen_primer_parcial ?? 0) * 0.30
                    ) : 0;

                    $promedio_segundo_quimestre = $id_periodo == 2 ? (
                        ($nota1_segundo_parcial ?? 0) * 0.35 +
                        ($nota2_segundo_parcial ?? 0) * 0.35 +
                        ($examen_segundo_parcial ?? 0) * 0.30
                    ) : 0;

                    // Actualizar registro_nota para los períodos 1, 2 y 3
                    $query_registro_nota = "
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
                    ";
                    $stmt_registro_nota = $conn->prepare($query_registro_nota);
                    if (!$stmt_registro_nota) {
                        throw new Exception("Error al preparar la consulta de registro de notas: " . $conn->error);
                    }
                    $stmt_registro_nota->bind_param(
                        "iiiiidddddd",
                        $id_estudiante, $id_curso, $id_materia, $id_periodo, $id_his_academico,
                        $nota1_primer_parcial, $nota2_primer_parcial, $examen_primer_parcial,
                        $nota1_segundo_parcial, $nota2_segundo_parcial, $examen_segundo_parcial
                    );
                    if (!$stmt_registro_nota->execute()) {
                        throw new Exception("Error al guardar las notas en registro_nota: " . $stmt_registro_nota->error);
                    }
                    $stmt_registro_nota->close();

                    // Llamar a la función para calcular notas finales
	                calcularNotasFinales($conn, $id_curso, $id_materia, $id_his_academico);

                    // Actualizar calificaciones
                    $query_update_calificaciones = "
                        INSERT INTO calificacion (
                            id_curso, id_materia, id_his_academico, id_estudiante, 
                            promedio_primer_quimestre, promedio_segundo_quimestre, nota_final, estado_calificacion
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            promedio_primer_quimestre = VALUES(promedio_primer_quimestre),
                            promedio_segundo_quimestre = VALUES(promedio_segundo_quimestre),
                            nota_final = VALUES(nota_final),
                            estado_calificacion = VALUES(estado_calificacion)
                    ";
                    $stmt_update_calificaciones = $conn->prepare($query_update_calificaciones);
                    if (!$stmt_update_calificaciones) {
                        throw new Exception("Error al preparar la consulta de actualización de calificaciones: " . $conn->error);
                    }
                    $stmt_update_calificaciones->bind_param(
                        "iiiiidss",
                        $id_curso, $id_materia, $id_his_academico, $id_estudiante,
                        $promedio_primer_quimestre, $promedio_segundo_quimestre,
                        $nota_final, $estado_calificacion
                    );
                    if (!$stmt_update_calificaciones->execute()) {
                        throw new Exception("Error al actualizar las calificaciones: " . $stmt_update_calificaciones->error);
                    }
                    $stmt_update_calificaciones->close();
                }

                $_SESSION['mensaje'] = "Calificaciones guardadas exitosamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "No se recibieron datos para guardar.";
                $_SESSION['tipo_mensaje'] = "error";
            }
        
        } else if ($accion === 'enviar') {
            // Obtener la cédula del profesor actualmente autenticado
            $cedula_profesor = $_SESSION['cedula_profesor'];
        
            // Obtener el ID del periodo actual desde la base de datos
            $query_periodo = "SELECT id_periodo FROM periodo_academico WHERE estado = 'A'"; // Suponiendo que hay un estado que indica el periodo activo
            $result_periodo = $conn->query($query_periodo);
            if ($result_periodo->num_rows > 0) {
                $row_periodo = $result_periodo->fetch_assoc();
                $id_periodo = $row_periodo['id_periodo'];
            } else {
                throw new Exception("No se encontró un periodo académico activo.");
            }
        
            // Enviar las notas al perfil de administrador
            // Primero, obtén las notas actualizadas junto con información adicional

            $query_notas = "
                SELECT r.id_estudiante, r.nota1_primer_parcial, r.nota2_primer_parcial, r.examen_primer_parcial,
                    r.nota1_segundo_parcial, r.nota2_segundo_parcial, r.examen_segundo_parcial,
                    c.promedio_primer_quimestre, c.promedio_segundo_quimestre, c.nota_final, c.estado_calificacion,
                    h.año, CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo, cu.nombre AS nombre_curso, 
                    j.nombre AS nombre_jornada, m.nombre AS nombre_materia
                FROM registro_nota r
                JOIN calificacion c ON r.id_estudiante = c.id_estudiante
                JOIN historial_academico h ON r.id_his_academico = h.id_his_academico
                JOIN profesor p ON r.cedula_profesor = p.cedula
                JOIN curso cu ON r.id_curso = cu.id_curso
                JOIN jornada j ON cu.id_jornada = j.id_jornada
                JOIN materia m ON r.id_materia = m.id_materia
                WHERE r.id_curso = ? AND r.id_materia = ? AND r.id_his_academico = ?
            ";

            $stmt_notas = $conn->prepare($query_notas);
            if (!$stmt_notas) {
                throw new Exception("Error al preparar la consulta de obtención de notas: " . $conn->error);
            }
            $stmt_notas->bind_param("iii", $id_curso, $id_materia, $id_his_academico);
            $stmt_notas->execute();
            $result_notas = $stmt_notas->get_result();
        
            // Almacenar las notas en el perfil del administrador
            while ($row = $result_notas->fetch_assoc()) {
                $query_insert_admin = "
                    INSERT INTO notas_administrador (
                        id_administrador, id_estudiante, id_curso, id_materia, id_his_academico, id_periodo, cedula_profesor,
                        nota1_primer_parcial, nota2_primer_parcial, examen_primer_parcial,
                        nota1_segundo_parcial, nota2_segundo_parcial, examen_segundo_parcial,
                        promedio_primer_quimestre, promedio_segundo_quimestre, nota_final, estado_calificacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        nota1_primer_parcial = VALUES(nota1_primer_parcial),
                        nota2_primer_parcial = VALUES(nota2_primer_parcial),
                        examen_primer_parcial = VALUES(examen_primer_parcial),
                        nota1_segundo_parcial = VALUES(nota1_segundo_parcial),
                        nota2_segundo_parcial = VALUES(nota2_segundo_parcial),
                        examen_segundo_parcial = VALUES(examen_segundo_parcial),
                        promedio_primer_quimestre = VALUES(promedio_primer_quimestre),
                        promedio_segundo_quimestre = VALUES(promedio_segundo_quimestre),
                        nota_final = VALUES(nota_final),
                        estado_calificacion = VALUES(estado_calificacion)
                ";
        
                $stmt_insert_admin = $conn->prepare($query_insert_admin);
                if (!$stmt_insert_admin) {
                    throw new Exception("Error al preparar la consulta de inserción para el perfil del administrador: " . $conn->error);
                }
                $stmt_insert_admin->bind_param(
                    "iiiiissddddddddd",
                    1, // ID del administrador
                    $row['id_estudiante'], $id_curso, $id_materia, $id_his_academico, $id_periodo, $cedula_profesor,
                    $row['nota1_primer_parcial'], $row['nota2_primer_parcial'], $row['examen_primer_parcial'],
                    $row['nota1_segundo_parcial'], $row['nota2_segundo_parcial'], $row['examen_segundo_parcial'],
                    $row['promedio_primer_quimestre'], $row['promedio_segundo_quimestre'],
                    $row['nota_final'], $row['estado_calificacion']
                );
                if (!$stmt_insert_admin->execute()) {
                    throw new Exception("Error al insertar las notas en el perfil del administrador: " . $stmt_insert_admin->error);
                }
                $stmt_insert_admin->close();
            }
            $stmt_notas->close();
    	    $conn->close();
        
            $_SESSION['mensaje'] = "Notas enviadas exitosamente al perfil del administrador.";
            $_SESSION['tipo_mensaje'] = "success";
        
        } else {
            $_SESSION['mensaje'] = "Acción no válida.";
            $_SESSION['tipo_mensaje'] = "error";
        }
        
        // Confirmar la transacción
        $conn->commit();
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }

    // Redirigir a la página de registro de calificaciones
    header("Location: registro_calificaciones.php?id_curso=$id_curso");
    exit();

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error en la transacción: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: registro_calificaciones.php?id_curso=$id_curso");
    exit();
}
function calcularNotasFinales($conn, $id_curso, $id_materia, $id_his_academico) {
    // Consulta para obtener las notas de los estudiantes
    $query = "
        SELECT id_estudiante,
            ROUND(
                SUM(nota1_primer_parcial * 0.35 +
                nota2_primer_parcial * 0.35 +
                examen_primer_parcial * 0.30) / COUNT(*), 2) AS promedio_primer_quimestre,
            ROUND(
                SUM(nota1_segundo_parcial * 0.35 +
                nota2_segundo_parcial * 0.35 +
                examen_segundo_parcial * 0.30) / COUNT(*), 2) AS promedio_segundo_quimestre
        FROM registro_nota
        WHERE id_curso = ? AND id_materia = ? AND id_his_academico = ?
        GROUP BY id_estudiante
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de cálculo de notas finales: " . $conn->error);
    }
    $stmt->bind_param("iii", $id_curso, $id_materia, $id_his_academico);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $promedio_primer_quimestre = $row['promedio_primer_quimestre'] ?? 0;
        $promedio_segundo_quimestre = $row['promedio_segundo_quimestre'] ?? 0;

        // Calcular promedio consolidado
        $promedio_consolidado = ($promedio_primer_quimestre + $promedio_segundo_quimestre) / 2;
        $nota_final = round($promedio_consolidado, 2);

        // Ajustar nota final si supera el máximo permitido
        $nota_final = min($nota_final, 10);

        // Verificar si se ha ingresado una nota de supletorio
        $supletorio = $_POST['supletorio'][$row['id_estudiante']] ?? 0;
        if ($nota_final < 7 && $supletorio > 0) {
            // Asegurarse de que la nota ajustada no supere 10
            $nota_ajustada = min(($nota_final + $supletorio) / 2, 10);
            $estado_calificacion = $nota_ajustada >= 7 ? 'A' : 'R';
            $nota_final = round($nota_ajustada, 2);
        } else {
            $estado_calificacion = $nota_final >= 7 ? 'A' : 'R';
        }

        // Consulta para actualizar la calificación final
        $query_update = "
            INSERT INTO calificacion (id_estudiante, id_curso, id_materia, id_his_academico,
                promedio_primer_quimestre, promedio_segundo_quimestre, nota_final, estado_calificacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                promedio_primer_quimestre = VALUES(promedio_primer_quimestre),
                promedio_segundo_quimestre = VALUES(promedio_segundo_quimestre),
                nota_final = VALUES(nota_final),
                estado_calificacion = VALUES(estado_calificacion)
        ";

        $stmt_update = $conn->prepare($query_update);
        if (!$stmt_update) {
            throw new Exception("Error al preparar la consulta de actualización de calificaciones: " . $conn->error);
        }
        $stmt_update->bind_param("iiiiddds",
            $row['id_estudiante'], $id_curso, $id_materia, $id_his_academico,
            $promedio_primer_quimestre, $promedio_segundo_quimestre, $nota_final, $estado_calificacion
        );
        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar la calificación: " . $stmt_update->error);
        }
    }
}
?>