<?php
include '../config.php'; // Asegúrate de incluir el archivo de configuración adecuado

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
    $sql = "SELECT * FROM niveles WHERE id='" . $id . "'";
    $resultado = mysqli_query($conn, $sql);

    if ($resultado) {
        $fila = mysqli_fetch_assoc($resultado);
?>
<div>
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
        <input type="hidden" name="txtid" value="<?php echo $fila['id']?>">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo $fila['nombre']; ?>" required><br>
        <label for="fecha_ingreso">Fecha de ingreso:</label>
        <input type="text" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo date('Y-m-d H:i:s'); ?>"
            readonly><br>
        <input type="submit" name="actualizar" value="Actualizar">
        <a href="../../views/admin/nivel_admin.php">Regresar</a>
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
    $nombre = $_POST['nombre'];
    $fecha_ingreso = $_POST['fecha_ingreso']; // Fecha de creación

    // Validar y actualizar en la base de datos
    if (!empty($nombre) && !empty($fecha_ingreso)) {
        // Conectar a la base de datos (usando mysqli)
        $conn = mysqli_connect($servername, $username, $password, $dbname);

        // Verificar la conexión
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Consulta de actualización (usando sentencias preparadas para mayor seguridad)
        $sql2 = "UPDATE niveles SET nombre=?, fecha_ingreso=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql2);

        // Verificar si la declaración fue preparada correctamente
        if ($stmt === false) {
            die("Error al preparar la declaración: " . mysqli_error($conn));
        }

        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "ssi", $nombre, $fecha_ingreso, $idp);

        // Ejecutar consulta
        if (mysqli_stmt_execute($stmt)) {
            // Redireccionar después de la actualización
            header("Location: ../../views/admin/nivel_admin.php");
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
    <title>Editar Materia| Sistema de Gestión UEBF</title>
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