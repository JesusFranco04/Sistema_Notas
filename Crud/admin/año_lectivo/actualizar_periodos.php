<?php
session_start();
include '../../config.php';


/**
 * Registra una actividad en la base de datos.
 *
 * @param mysqli $conn La conexión a la base de datos.
 * @param string $accion La descripción de la acción realizada.
 */
function registrarActividad($conn, $accion) {
    if (!isset($_SESSION['usuario'])) {
        die("Error: Usuario no autenticado.");
    }
    
    $usuario = $conn->real_escape_string($_SESSION['usuario']);
    $sql_registro = $conn->prepare("INSERT INTO registro_actividades (usuario, accion, fecha) VALUES (?, ?, NOW())");
    $sql_registro->bind_param("ss", $usuario, $accion);

    if (!$sql_registro->execute()) {
        die("Error al registrar la actividad: " . $sql_registro->error);
    }
}

// Verifica si se ha enviado un período
if (isset($_POST['periodo'])) {
    $id_periodo = (int)$_POST['periodo'];

    // Desactivar otros períodos
    $sql_desactivar = $conn->prepare("UPDATE periodo_academico SET estado = '0' WHERE id_periodo IN (1, 2) AND id_periodo != ?");
    $sql_desactivar->bind_param("i", $id_periodo);

    if (!$sql_desactivar->execute()) {
        die("Error al desactivar los períodos: " . $sql_desactivar->error);
    }

    // Activar el período seleccionado
    $sql_activar = $conn->prepare("UPDATE periodo_academico SET estado = '1' WHERE id_periodo = ?");
    $sql_activar->bind_param("i", $id_periodo);

    if (!$sql_activar->execute()) {
        die("Error al activar el período: " . $sql_activar->error);
    }

    registrarActividad($conn, "Cambiado el estado del período $id_periodo a activo");
} else {
    die("Error: No se ha seleccionado ningún período.");
}
?>
