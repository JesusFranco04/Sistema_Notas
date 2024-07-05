<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos del formulario
    $cedula = $_POST["cedula"];
    $contraseña = $_POST["contraseña"];
    
    // Definir credenciales del super usuario
    define('SUPER_USER_KEY', '0954352185');
    define('SUPER_USER_PASSWORD', 'admin340');
	
    include 'config.php';

    // Verificar si es el super usuario
    if ($cedula === SUPER_USER_KEY && $contraseña === SUPER_USER_PASSWORD) {
        // Credenciales correctas para el super usuario
        $_SESSION['user'] = 'superuser';
        $_SESSION['role'] = 'administrador'; // Definimos el rol como administrador
        header("Location: http://localhost/sistema_notas/views/admin/index_admin.php"); // Redirigir al índice del administrador
        exit();
    }

    // Preparar la declaración SQL
    $stmt = $conn->prepare("SELECT codigo_de_perfil, nombres, apellidos, rol FROM usuarios WHERE cedula = ? AND contraseña = ?");
    if ($stmt) {
        // Vincular los parámetros
        $stmt->bind_param("ss", $cedula, $contraseña);
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener el resultado
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            // Obtener los datos del usuario
            $row = $result->fetch_assoc();
            $codigo_de_perfil = $row['codigo_de_perfil'];
            $_SESSION["nombres"] = $row['nombres'];
            $_SESSION["apellidos"] = $row['apellidos'];
            $_SESSION["rol"] = $row['rol'];
            
            // Redireccionar según el rol del usuario
            switch ($row['rol']) {
                case 1:
                    header("Location: http://localhost/sistema_notas/views/admin/index_admin.php"); // Panel de administrador
                    break;
                case 2:
                    header("Location: http://localhost/Sistema_Notas/views/profe/index_profe.php"); // Panel de profesor
                    break;
                case 3:
                    header("Location: http://localhost/Sistema_Notas/views/family/index_family.php"); // Panel de padres de familia
                    break;
                default:
                    echo '<script>alert("¡Rol desconocido! Por favor, contacte al administrador."); window.location.href = "http://localhost/Sistema_Notas/login.php";</script>';
                    break;
            }
            exit();
        } else {
            echo '<script>alert("¡Usuario o contraseña incorrectos! Por favor, inténtalo de nuevo."); window.location.href = "http://localhost/Sistema_Notas/login.php";</script>';
        }
        
        // Cerrar la declaración
        $stmt->close();
    } else {
        // Manejar error de SQL
        echo '<script>alert("Error al preparar la consulta SQL: ' . $conn->error . '"); window.location.href = "http://localhost/Sistema_Notas/login.php";</script>';
    }
    
    // Cerrar la conexión
    $conn->close();
}
?>
