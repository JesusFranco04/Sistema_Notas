<?php
session_start();
include('../../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el ID del subnivel y el nuevo estado
    $id_subnivel = $_POST['id_subnivel'];
    $estado = $_POST['estado'];

    // Validar los datos
    if (empty($id_subnivel) || empty($estado)) {
        die("Error: Datos incompletos.");
    }

    // Cambiar el estado a 'A' o 'I'
    $estado = $estado == 'activo' ? 'A' : 'I';

    // Preparar la consulta para cambiar el estado del subnivel
    $sql = "UPDATE subnivel SET estado = ? WHERE id_subnivel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $estado, $id_subnivel);

    if ($stmt->execute()) {
        echo "Estado actualizado exitosamente.";
    } else {
        echo "Error al cambiar el estado: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
