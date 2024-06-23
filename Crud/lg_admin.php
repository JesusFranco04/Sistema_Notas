<?php


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos del formulario
    $cedula = $_POST["cedula"];
    $contrasena = $_POST["contrasena"];
 
    include 'config.php';

    // Consulta SQL para verificar las credenciales del usuario
    $sql = "SELECT codigo_de_perfil FROM soli_profe WHERE cedula = '$cedula' AND contrasena = '$contrasena'";
    // Ejecutar la consulta
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Obtener el código del perfil del usuario
        $row = $result->fetch_assoc();
        $codigo_de_perfil = $row['codigo_de_perfil'];

        // Redireccionar según el código del perfil
        if ($codigo_de_perfil == 1) {
            header("Location: http://localhost/Sistema_Notas/views/admin/index_admin.php"); // Panel de administrador
        } elseif ($codigo_de_perfil == 2) {
            header("Location: http://localhost/Sistema_Notas/views/profe/index_profe.php"); // Panel de profesor
        } elseif ($codigo_de_perfil == 3) {
            header("Location: http://localhost/Sistema_Notas/views/family/index_family.php"); // Panel de padres de familia
        }
        exit;
    } else {
        echo '<script>alert("Usuario o Contraseña incorrectas!"); window.location.href = "http://localhost/Sistema_Notas/login_profe.php";</script>';
    }

    // Cerrar la conexión (opcional, ya que se cerrará automáticamente al final del script)
    $conn->close();
}
?>
