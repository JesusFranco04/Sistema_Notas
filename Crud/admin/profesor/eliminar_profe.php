<?php
include '../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Crear una conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Preparar la declaración SQL
    $stmt = $conn->prepare("DELETE FROM profesores WHERE id = ?");
    if ($stmt) {
        // Vincular el parámetro
        $stmt->bind_param("i", $id);

        // Ejecutar la declaración
        if ($stmt->execute()) {
            // Redirigir a la página de profesores
            header('Location: ../../views/admin/profesores.php');
            exit;
        } else {
            echo "Error al ejecutar la consulta: " . $stmt->error;
        }

        // Cerrar la declaración
        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }

    // Cerrar la conexión
    $conn->close();
} else {
    echo "No se proporcionó ID";
}
?>
