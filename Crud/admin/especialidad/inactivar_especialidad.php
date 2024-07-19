<?php
session_start();
include('../../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el ID de la especialidad y el nuevo estado
    $id_especialidad = $_POST['id_especialidad'];
    $estado = $_POST['estado'];

    // Validar los datos
    if (empty($id_especialidad) || empty($estado)) {
        die("Error: Datos incompletos.");
    }

    // Cambiar el estado a 'A' o 'I'
    $estado = $estado == 'activo' ? 'A' : 'I';

    // Preparar la consulta para cambiar el estado de la especialidad
    $sql = "UPDATE especialidad SET estado = ? WHERE id_especialidad = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $estado, $id_especialidad);

    if ($stmt->execute()) {
        echo "Estado actualizado exitosamente.";
    } else {
        echo "Error al cambiar el estado: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
