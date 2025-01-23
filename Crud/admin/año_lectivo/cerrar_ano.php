<?php
session_start();
include '../../config.php';
date_default_timezone_set('America/Guayaquil');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_periodo'])) {
    $id_periodo = (int)$_POST['id_periodo'];
    $fecha_cierre = (new DateTime())->format('Y-m-d H:i:s');

    // Verificar si el período ya tiene una fecha de cierre programada
    $sql_check = $conn->prepare("SELECT fecha_cierre_programada FROM historial_academico WHERE id_his_academico = ?");
    $sql_check->bind_param("i", $id_periodo);
    $sql_check->execute();
    $result_check = $sql_check->get_result();
    $row = $result_check->fetch_assoc();

    // Si ya tiene una fecha de cierre o no, actualizamos la fecha y el estado
    $sql_cerrar = $conn->prepare("UPDATE historial_academico SET estado = 'I', fecha_cierre_programada = ? WHERE id_his_academico = ?");
    $sql_cerrar->bind_param("si", $fecha_cierre, $id_periodo);
    
    // Redirigir con el mensaje adecuado
    if ($sql_cerrar->execute()) {
        header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Año cerrado correctamente.&tipo=success');
    } else {
        header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Error al cerrar el año. Por favor, inténtelo de nuevo.&tipo=error');
    }

    $sql_check->close();
    $sql_cerrar->close();
    $conn->close();
} else {
    header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Datos inválidos.&tipo=error');
}
?>