<?php


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos del formulario
    $cedula = $_POST["cedula"];
    $contrasena = $_POST["contrasena"];
 
    include 'config.php';

    // Consulta SQL para verificar las credenciales del usuario
    $sql = "SELECT * FROM soli_profe WHERE cedula = '$cedula' AND contrasena = '$contrasena'";

    // Ejecutar la consulta
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
    
        header("Location: http://localhost/Sistema_Notas/views/admin/index_admin.php"); // Redireccionar al panel de control
        exit;
    } else {
        echo '<script>alert("Usuario o Contraseña incorrectas!"); window.location.href = "http://localhost/Sistema_Notas/login_profe.php";</script>';
        
        
    }

    // Cerrar la conexión (opcional, ya que se cerrará automáticamente al final del script)
    $conn->close();
}
?>
