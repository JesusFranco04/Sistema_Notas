<?php
session_start();
include('../../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el ID de la materia y el nuevo estado
    $id_materia = $_POST['id_materia'];
    $estado = $_POST['estado'];

    // Validar los datos
    if (empty($id_materia) || empty($estado)) {
        die("Error: Datos incompletos.");
    }

    // Cambiar el estado a 'A' o 'I'
    $estado = $estado == 'activo' ? 'A' : 'I';

    // Preparar la consulta para cambiar el estado de la materia
    $sql = "UPDATE materia SET estado = ? WHERE id_materia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $estado, $id_materia);

    if ($stmt->execute()) {
        echo "Estado actualizado exitosamente.";
    } else {
        echo "Error al cambiar el estado: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
