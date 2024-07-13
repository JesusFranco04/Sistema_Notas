<?php
include '../config.php'; 

// Verificar si se proporciona el parámetro 'id' en la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Conectar a la base de datos (usando mysqli)
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // Verificar la conexión
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Consulta para obtener los detalles del profesor basado en el ID
    $sql = "SELECT * FROM profesores WHERE id='" . $id . "'";
    $resultado = mysqli_query($conn, $sql);

    if ($resultado) {
        $fila = mysqli_fetch_assoc($resultado);
?>
<div>
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
        <input type="hidden" name="txtid" value="<?php echo $fila['id']?>">
        <label for="nombres">Nombres:</label>
        <input type="text" id="nombres" name="nombres" value="<?php echo $fila['nombres']; ?>" required><br>
        <label for="apellidos">Apellidos:</label>
        <input type="text" id="apellidos" name="apellidos" value="<?php echo $fila['apellidos']; ?>" required><br>
        <label for="cedula">Cédula:</label>
        <input type="text" id="cedula" name="cedula" value="<?php echo $fila['cedula']; ?>" required><br>
        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" value="<?php echo $fila['telefono']; ?>" required><br>
        <label for="correo_electronico">Correo Electrónico:</label>
        <input type="email" id="correo_electronico" name="correo_electronico"
            value="<?php echo $fila['correo_electronico']; ?>" required><br>
        <label for="direccion">Dirección:</label>
        <input type="text" id="direccion" name="direccion" value="<?php echo $fila['direccion']; ?>"><br>
        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
            value="<?php echo $fila['fecha_nacimiento']; ?>"><br>
        <label for="genero">Género:</label>
        <select id="genero" name="genero">
            <option value="Masculino" <?php if ($fila['genero'] == 'Masculino') echo 'selected'; ?>>Masculino</option>
            <option value="Femenino" <?php if ($fila['genero'] == 'Femenino') echo 'selected'; ?>>Femenino</option>
        </select><br>
        <label for="discapacidad">Discapacidad:</label>
        <select id="discapacidad" name="discapacidad">
            <option value="Si" <?php if ($fila['discapacidad'] == 'Si') echo 'selected'; ?>>Si</option>
            <option value="No" <?php if ($fila['discapacidad'] == 'No') echo 'selected'; ?>>No</option>
        </select><br>
        <label for="rol">Rol:</label>
        <select id="rol" name="rol" required>
            <option value="2" <?php if ($fila['rol'] == '2') echo 'selected'; ?>>Profesor</option>
        </select><br>
        <label for="date_creation">Fecha de Creación:</label>
        <input type="text" id="date_creation" name="date_creation" value="<?php echo date('Y-m-d H:i:s'); ?>"
            readonly><br>
        <input type="submit" name="actualizar" value="Actualizar">
        <a href="../../views/admin/profesores.php">Regresar</a>
    </form>
</div>
<?php
    } else {
        echo "Error al obtener los datos del profesor.";
    }

    // Cerrar la conexión
    mysqli_close($conn);
} else {
    echo "No se ha proporcionado el parámetro 'id' en la URL.";
}

if (isset($_POST['actualizar'])) {
    // Obtener datos actualizados del formulario
    $idp = $_POST['txtid'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $cedula = $_POST['cedula'];
    $telefono = $_POST['telefono'];
    $correo_electronico = $_POST['correo_electronico'];
    $direccion = $_POST['direccion'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $discapacidad = $_POST['discapacidad'];
    $rol = $_POST['rol'];
    $date_creation = $_POST['date_creation']; // Fecha de creación

    // Validar y actualizar en la base de datos
    if (!empty($nombres) && !empty($apellidos) && !empty($cedula) && !empty($telefono) && !empty($correo_electronico) && !empty($direccion) && !empty($fecha_nacimiento) && !empty($genero) && !empty($discapacidad) && !empty($rol) && !empty($date_creation)) {
        // Conectar a la base de datos (usando mysqli)
        $conn = mysqli_connect($servername, $username, $password, $dbname);

        // Verificar la conexión
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Consulta de actualización (usando sentencias preparadas para mayor seguridad)
        $sql2 = "UPDATE profesores SET nombres=?, apellidos=?, cedula=?, telefono=?, correo_electronico=?, direccion=?, fecha_nacimiento=?, genero=?, discapacidad=?, rol=?, date_creation=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql2);

        // Verificar si la declaración fue preparada correctamente
        if ($stmt === false) {
            die("Error al preparar la declaración: " . mysqli_error($conn));
        }

        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "sssssssssssi", $nombres, $apellidos, $cedula, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $rol, $date_creation, $idp);

        // Ejecutar consulta
        if (mysqli_stmt_execute($stmt)) {
            // Redireccionar después de la actualización
            header("Location: ../../views/admin/profesores.php");
            exit();
        } else {
            die("Error al ejecutar la declaración: " . mysqli_stmt_error($stmt));
        }

        // Cerrar la consulta y la conexión
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        echo "Todos los campos son obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Editar Profesores | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Estilos personalizados -->
    <style>
        .sidebar-heading .collapse-header .bx {
            color: #ff8b97;
            /* Color rosa claro para los iconos en los encabezados de sección */
        }

        .bg-gradient-primary {
            background-color: #a2000e;
            /* Color rojo oscuro para el fondo de la barra lateral */
            background-image: none;
            /* Asegurar que no haya imagen de fondo (gradiente) */
        }
    </style>
</head>
<!-- Bootstrap core JavaScript-->
<script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>

</html>