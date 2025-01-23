<?php 
session_start();
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_periodo = $_POST['periodo'];

    // Desactivar otros períodos (excepto los que deben permanecer siempre activos)
    $sql_desactivar = "UPDATE periodo_academico SET estado = 0 WHERE id_periodo NOT IN (3)";
    $conn->query($sql_desactivar);

    // Activar el período seleccionado
    $sql_activar = "UPDATE periodo_academico SET estado = 1 WHERE id_periodo = ?";
    $stmt = $conn->prepare($sql_activar);
    $stmt->bind_param("i", $id_periodo);
    $stmt->execute();

    // Redirigir con el mensaje adecuado
    if ($stmt->affected_rows > 0) {
        header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Período activado correctamente.&tipo=success');
    } else {
        header('Location: http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Error al activar el período. Por favor, inténtelo de nuevo.&tipo=error');
    }

    $stmt->close();
    $conn->close();
}
?>