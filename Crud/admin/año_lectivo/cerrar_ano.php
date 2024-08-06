<?php
session_start();
include '../../config.php';
date_default_timezone_set('America/Guayaquil');

// Verifica si se ha enviado un id_periodo para cerrar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_periodo'])) {
    $id_periodo = (int)$_POST['id_periodo'];
    $fecha_cierre = (new DateTime())->format('Y-m-d H:i:s');

    // Verifica si el período ya tiene una fecha de cierre programada
    $sql_check = $conn->prepare("SELECT fecha_cierre_programada FROM historial_academico WHERE id_his_academico = ?");
    $sql_check->bind_param("i", $id_periodo);
    $sql_check->execute();
    $result_check = $sql_check->get_result();
    $row = $result_check->fetch_assoc();

    if ($row && $row['fecha_cierre_programada'] !== null) {
        // Si ya tiene una fecha de cierre, actualiza la fecha
        $sql_cerrar = $conn->prepare("UPDATE historial_academico SET estado = 'I', fecha_cierre_programada = ? WHERE id_his_academico = ?");
    } else {
        // Si no tiene una fecha de cierre, inserta una nueva
        $sql_cerrar = $conn->prepare("UPDATE historial_academico SET estado = 'I', fecha_cierre_programada = ? WHERE id_his_academico = ?");
    }
    $sql_cerrar->bind_param("si", $fecha_cierre, $id_periodo);
    
    if ($sql_cerrar->execute()) {
        echo json_encode(['mensaje' => 'Año cerrado correctamente.', 'tipo' => 'success']);
    } else {
        echo json_encode(['mensaje' => 'Error al cerrar el año. Por favor, inténtelo de nuevo.', 'tipo' => 'error']);
    }
} else {
    echo json_encode(['mensaje' => 'Datos inválidos.', 'tipo' => 'error']);
}
?>
