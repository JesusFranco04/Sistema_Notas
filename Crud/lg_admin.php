<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos del formulario
    $cedula = $_POST["cedula"];
    $contraseña = $_POST["contraseña"];
    
    include 'config.php';

    // Preparar la declaración SQL
    $stmt = $conn->prepare("SELECT rol FROM usuarios WHERE cedula = ? AND contraseña = ?");
    if ($stmt) {
        // Vincular los parámetros
        $stmt->bind_param("ss", $cedula, $contraseña);
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener el resultado
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            // Obtener el código del perfil del usuario
            $row = $result->fetch_assoc();
            $rol = $row['rol'];

        // Almacenar datos en variables de sesión
        $_SESSION["cedula"] = $cedula;
        $_SESSION["rol"] = $rol;
            
            // Redireccionar según el código del perfil
            if ($rol == 1) {
                header("Location: http://localhost/Sistema_Notas/views/admin/index_admin.php"); // Panel de administrador
            } elseif ($rol == 2) {
                header("Location: http://localhost/Sistema_Notas/views/profe/index_profe.php"); // Panel de profesor
            } elseif ($rol == 3) {
                header("Location: http://localhost/Sistema_Notas/views/family/index_family.php"); // Panel de padres de familia
            }
            exit;
        } else {
            echo '<script>alert("Usuario o Contraseña incorrectas!"); window.location.href = "http://localhost/Sistema_Notas/login_profe.php";</script>';
        }
        
        // Cerrar la declaración
        $stmt->close();
    } else {
        // Manejar error de SQL
        echo '<script>alert("Error al preparar la consulta SQL: ' . $conn->error . '"); window.location.href = "http://localhost/Sistema_Notas/login_profe.php";</script>';
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
}
?>