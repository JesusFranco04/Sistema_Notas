<?php
session_start();
include('../../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el ID del usuario y el nuevo estado
    $id_usuario = $_POST['id_usuario'];
    $estado = $_POST['estado'];

    // Validar los datos
    if (empty($id_usuario) || empty($estado)) {
        die("Error: Datos incompletos.");
    }

    // Cambiar el estado a 'A' o 'I'
    $estado = $estado == 'activo' ? 'A' : 'I';

    // Preparar la consulta para cambiar el estado del usuario
    $sql = "UPDATE usuario SET estado = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $estado, $id_usuario);

    if ($stmt->execute()) {
        echo "Estado actualizado exitosamente.";
    } else {
        echo "Error al cambiar el estado: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
