<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil');

// Verificar si se proporciona el parámetro 'id' en la URL
if (isset($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario'];

    // Conectar a la base de datos (usando mysqli)
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Consulta para obtener los detalles del usuario basado en el ID
    $sql = "SELECT * FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <meta name="description" content="">
            <meta name="author" content="">
            <title>Editar Usuario | Sistema de Gestión UEBF</title>
            <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
            <!-- Custom fonts for this template-->
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" type="text/css">
            <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
            <!-- Custom styles for this template-->
            <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
            <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
            <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
            <!-- Estilos personalizados -->
            <style>
                .sidebar-heading .collapse-header .bx {
                    color: #ff8b97;
                }
                .bg-gradient-primary {
                    background-color: #a2000e;
                    background-image: none;
                }
            </style>
        </head>
        <body>
        <div>
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                <input type="hidden" name="txtid" value="<?php echo $fila['id_usuario']; ?>">
                <label for="cedula">Cédula:</label>
                <input type="text" id="cedula" name="cedula" value="<?php echo $fila['cedula']; ?>" required><br>
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" value="<?php echo $fila['contraseña']; ?>" required><br>
                <label for="id_rol">ID Rol:</label>
                <input type="text" id="id_rol" name="id_rol" value="<?php echo $fila['id_rol']; ?>" required><br>
                <label for="estado">Estado:</label>
                <input type="text" id="estado" name="estado" value="<?php echo $fila['estado']; ?>" required><br>
                <label for="usuario_ingreso">Usuario Ingreso:</label>
                <input type="text" id="usuario_ingreso" name="usuario_ingreso" value="<?php echo $fila['usuario_ingreso']; ?>" required><br>
                <label for="fecha_ingreso">Fecha de ingreso:</label>
                <input type="text" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo date('Y-m-d H:i:s'); ?>" readonly><br>
                <input type="submit" name="actualizar" value="Actualizar">
                <a href="../../views/admin/usuario">Regresar</a>
            </form>
        </div>
        </body>
        <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
        <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
        </html>
        <?php
    } else {
        echo "Error al obtener los datos del usuario.";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
} else {
    echo "No se ha proporcionado el parámetro 'id' en la URL.";
}

if (isset($_POST['actualizar'])) {
    // Obtener datos actualizados del formulario
    $id_uauriop = $_POST['txtid_usuario'];
    $cedula = $_POST['cedula'];
    $contraseña = $_POST['contraseña'];
    $id_rol = $_POST['id_rol'];
    $estado = $_POST['estado'];
    $usuario_ingreso = $_POST['usuario_ingreso'];
    $fecha_ingreso = $_POST['fecha_ingreso']; // Fecha de creación

    // Validar y actualizar en la base de datos
    if (!empty($cedula) && !empty($contraseña) && !empty($id_rol) && !empty($estado) && !empty($usuario_ingreso) && !empty($fecha_ingreso)) {
        // Conectar a la base de datos (usando mysqli)
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificar la conexión
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Consulta de actualización (usando sentencias preparadas para mayor seguridad)
        $sql2 = "UPDATE usuario SET cedula = ?, contraseña = ?, id_rol = ?, estado = ?, usuario_ingreso = ?, fecha_ingreso = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql2);

        // Verificar si la declaración fue preparada correctamente
        if ($stmt === false) {
            die("Error al preparar la declaración: " . $conn->error);
        }

        // Vincular parámetros
        $stmt->bind_param("ssisssi", $cedula, $contraseña, $id_rol, $estado, $usuario_ingreso, $fecha_ingreso, $idp);

        // Ejecutar consulta
        if ($stmt->execute()) {
            // Redireccionar después de la actualización
            header("Location: ../../views/admin/usuario.php");
            exit();
        } else {
            die("Error al ejecutar la declaración: " . $stmt->error);
        }

        // Cerrar la consulta y la conexión
        $stmt->close();
        $conn->close();
    } else {
        echo "Todos los campos son obligatorios.";
    }
}
?>
