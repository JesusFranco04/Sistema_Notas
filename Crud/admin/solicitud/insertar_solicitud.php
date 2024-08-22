<?php
session_start();
include('../../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_administrador = $_POST['id_administrador'];
    $id_profesor = $_POST['id_profesor'];
    $id_periodo = $_POST['id_periodo'];
    $mensaje = $_POST['mensaje'];
    $fecha_solicitud = date('Y-m-d H:i:s');

    $sql = "INSERT INTO solicitudes_modificacion (id_administrador, id_profesor, id_periodo, mensaje, fecha_solicitud) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $id_administrador, $id_profesor, $id_periodo, $mensaje, $fecha_solicitud);

    if ($stmt->execute()) {
        echo "Solicitud insertada correctamente";
    } else {
        echo "Error: " . $conn->error;
    }

    // Crear notificación para el profesor
    $sql_notificacion = "INSERT INTO notificaciones_docente (id_profesor, mensaje, fecha_notificacion) VALUES (?, ?, ?)";
    $mensaje_notificacion = "Nueva solicitud de modificación de calificación";
    $stmt_notificacion = $conn->prepare($sql_notificacion);
    $stmt_notificacion->bind_param("iss", $id_profesor, $mensaje_notificacion, $fecha_solicitud);
    $stmt_notificacion->execute();
}
?>