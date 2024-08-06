<?php
session_start();
include '../../config.php';

// Verifica si se ha enviado un período y una fecha de cierre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_periodo']) && isset($_POST['fecha_cierre'])) {
    $id_periodo = (int)$_POST['id_periodo'];
    $fecha_cierre = $_POST['fecha_cierre'];

    // Validar que la fecha no sea anterior a la fecha y hora actual
    $current_date = (new DateTime())->format('Y-m-d\TH:i');
    if ($fecha_cierre < $current_date) {
        header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=La fecha de cierre no puede ser anterior a la fecha y hora actual.&tipo=error');
        exit();
    }

    // Validar que la fecha no esté más allá de 3 años en el futuro
    $max_date = (new DateTime())->add(new DateInterval('P3Y'))->format('Y-m-d\TH:i');
    if ($fecha_cierre > $max_date) {
        header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=La fecha de cierre no puede ser más allá de 3 años en el futuro.&tipo=error');
        exit();
    }

    // Actualiza la fecha de cierre programada para el período seleccionado
    $sql_programar = $conn->prepare("UPDATE historial_academico SET fecha_cierre_programada = ? WHERE id_his_academico = ?");
    $sql_programar->bind_param("si", $fecha_cierre, $id_periodo);
    
    if ($sql_programar->execute()) {
        header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Fecha de cierre programada correctamente.&tipo=success');
    } else {
        header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Error al programar el cierre. Por favor, inténtelo de nuevo.&tipo=error');
    }
} else {
    header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Datos inválidos.&tipo=error');
}
?>
