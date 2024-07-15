<?php
// Iniciar la sesión para obtener datos del usuario que está logueado
session_start();
// Incluir el archivo de configuración para conectarte a la base de datos
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

// Obtener el ID del usuario a editar desde la URL
$id_usuario = $_GET['id'];

// Consultar los datos del usuario en la base de datos
$sql = "SELECT * FROM usuarios WHERE id = $id_usuario";
$result = mysqli_query($conexion, $sql);
$usuario = mysqli_fetch_assoc($result);

// Si no se encuentra el usuario, redirigir a la página de error o lista de usuarios
if (!$usuario) {
    header('Location: error.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="estilos.css"> <!-- Asegúrate de tener un archivo de estilos -->
</head>
<body>
    <h2>Editar Usuario</h2>
    <form action="actualizar_usuario.php" method="post">
        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
        
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo $usuario['nombre']; ?>" required>
        
        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" value="<?php echo $usuario['apellido']; ?>" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $usuario['email']; ?>" required>
        
        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" value="<?php echo $usuario['telefono']; ?>" required>
        
        <label for="direccion">Dirección:</label>
        <input type="text" id="direccion" name="direccion" value="<?php echo $usuario['direccion']; ?>" required>
        
        <!-- Puedes agregar más campos según lo necesario -->
        
        <button type="submit">Actualizar Usuario</button>
    </form>
</body>
</html>
