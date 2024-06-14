<?php
include 'config.php';

if (isset($_POST['cedula'])) {
    // Obtener la cédula enviada desde el formulario
    $cedula = $_POST['cedula'];
    
    // Sentencia SQL para eliminar la solicitud
    $sql = "DELETE FROM solicitudes WHERE cedula = '$cedula'";
    
    if ($conn->query($sql) === TRUE) {
        echo '<script>alert("Eliminar Solicitud exitosamente.");</script>';
        echo '<script>window.location.replace("http://localhost/Sistema_Notas/ver_soli.php");</script>';
        exit;
    } else {
        echo "Error al eliminar la solicitud: " . $conn->error;
    }
}

// Cerrar la conexión
$conn->close();
?>
