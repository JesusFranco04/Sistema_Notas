<?php
session_start();
include('../../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el ID del nivel y el nuevo estado
    $id_nivel = $_POST['id_nivel'];
    $estado = $_POST['estado'];

    // Validar los datos
    if (empty($id_nivel) || empty($estado)) {
        die("Error: Datos incompletos.");
    }

    // Cambiar el estado a 'A' o 'I'
    $estado = $estado == 'activo' ? 'A' : 'I';

    // Preparar la consulta para cambiar el estado del nivel
    $sql = "UPDATE nivel SET estado = ? WHERE id_nivel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $estado, $id_nivel);

    if ($stmt->execute()) {
        // Redirigir a la página de administración después de la actualización
        header("Location: http://localhost/sistema_notas/views/admin/nivel_admin.php");
        exit();
    } else {
        die("Error al cambiar el estado: " . $stmt->error);
    }
}
?>
