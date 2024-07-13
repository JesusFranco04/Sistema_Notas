<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_administrador = isset($_POST['id_administrador']) ? $_POST['id_administrador'] : null;
    $nombres = isset($_POST['nombres']) ? $_POST['nombres'] : null;
    $apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : null;
    $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : null;
    $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : null;
    $correo_electronico = isset($_POST['correo_electronico']) ? $_POST['correo_electronico'] : null;
    $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : null;
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
    $genero = isset($_POST['genero']) ? $_POST['genero'] : null;
    $discapacidad = isset($_POST['discapacidad']) ? $_POST['discapacidad'] : null;
    $id_rol = isset($_POST['id_rol']) ? $_POST['id_rol'] : null;
    $contraseña = isset($_POST['contraseña']) ? $_POST['contraseña'] : null;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : 'A';
    $usuario_ingreso = $_SESSION['cedula']; // Variable de sesión para el campo usuario_ingreso
    $fecha_ingreso = date('Y-m-d H:i:s'); // Fecha y hora actual

    // Validar campos requeridos
    if (empty($id_administrador) || empty($nombres) || empty($apellidos) || empty($cedula) || empty($telefono) || empty($correo_electronico) || empty($direccion) || empty($fecha_nacimiento) || empty($genero) || empty($discapacidad) || empty($id_rol) || empty($contraseña) || empty($usuario_ingreso) || empty($fecha_ingreso)) {
        echo '<div class="alert alert-danger" role="alert">Por favor, complete todos los campos obligatorios.</div>';
        exit;
    }

    // Validar formato de correo electrónico
    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger" role="alert">Por favor, ingrese un correo electrónico válido.</div>';
        exit;
    }

    // Validar contraseña
    if (strlen($contraseña) < 8 || !preg_match("#[0-9]+#", $contraseña) || !preg_match("#[a-zA-Z]+#", $contraseña)) {
        echo '<div class="alert alert-danger" role="alert">La contraseña debe tener al menos 8 caracteres, incluyendo números y letras.</div>';
        exit;
    }

    // Validar fecha de nacimiento y edad
    $fecha_actual = new DateTime();
    $fecha_nacimiento_dt = new DateTime($fecha_nacimiento);
    $edad = $fecha_actual->diff($fecha_nacimiento_dt)->y;

    if ($edad < 18 || $edad > 80 || $fecha_nacimiento_dt > $fecha_actual) {
        echo '<div class="alert alert-danger" role="alert">La fecha de nacimiento no es válida. Debe tener 18 años o más y menos de 80 años. Además, no puede ser una fecha futura.</div>';
        exit;
    }

    // Escapar valores para evitar inyección SQL
    $id_administrador = mysqli_real_escape_string($conn, $id_administrador);
    $nombres = mysqli_real_escape_string($conn, $nombres);
    $apellidos = mysqli_real_escape_string($conn, $apellidos);
    $cedula = mysqli_real_escape_string($conn, $cedula);
    $telefono = mysqli_real_escape_string($conn, $telefono);
    $correo_electronico = mysqli_real_escape_string($conn, $correo_electronico);
    $direccion = mysqli_real_escape_string($conn, $direccion);
    $fecha_nacimiento = mysqli_real_escape_string($conn, $fecha_nacimiento);
    $genero = mysqli_real_escape_string($conn, $genero);
    $discapacidad = mysqli_real_escape_string($conn, $discapacidad);
    $id_rol = mysqli_real_escape_string($conn, $id_rol);
    $contraseña = mysqli_real_escape_string($conn, $contraseña);
    $estado = mysqli_real_escape_string($conn, $estado);
    $usuario_ingreso = mysqli_real_escape_string($conn, $usuario_ingreso);
    $fecha_ingreso = mysqli_real_escape_string($conn, $fecha_ingreso);

    // Actualizar datos en la base de datos (consulta preparada)
    $stmt = $conn->prepare("UPDATE administrador SET nombres = ?, apellidos = ?, cedula = ?, telefono = ?, correo_electronico = ?, direccion = ?, fecha_nacimiento = ?, genero = ?, discapacidad = ?, id_rol = ?, contraseña = ?, estado = ?, usuario_ingreso = ?, fecha_ingreso = ? WHERE id_administrador = ?");
    $stmt->bind_param("sssssssssssssi", $nombres, $apellidos, $cedula, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $id_rol, $contraseña, $estado, $usuario_ingreso, $fecha_ingreso, $id_administrador);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success" role="alert">Los datos se actualizaron correctamente.</div>';
        $stmt->close();
        mysqli_close($conn);
        header("Location: ../../views/admin/administradores.php");
        exit;
    } else {
        echo '<div class="alert alert-danger" role="alert">Hubo un error al intentar actualizar los datos. Por favor, inténtelo nuevamente.</div>';
    }

    $stmt->close();
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Administrador | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Incluye Bootstrap CSS para estilos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Incluye Boxicons para iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/css/boxicons.min.css">
</head>

<style>
.required::after {
    content: '*';
    color: red;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 800px;
    margin: 20px auto;
    /* Añadimos margen y centramos verticalmente */
    padding: 20px;
    background-color: #fff;
    /* Fondo blanco */
    border: 1px solid #ccc;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #dc3545;
    /* Rojo claro */
    color: #fff;
    padding: 15px;
    border-radius: 10px 10px 0 0;
}

.card-title {
    margin: 0;
    font-size: 1.5rem;
}

.card-body {
    padding: 20px;
}

.form-label {
    font-weight: bold;
}

.form-label.required::after {
    content: " *";
    color: #dc3545;
}

.input-group-append .btn {
    background-color: #007bff;
    /* Azul oscuro bonito */
    border-color: #007bff;
    color: #fff;
}

.input-group-append .btn:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

input[type="text"],
input[type="password"],
input[type="email"],
input[type="date"],
select {
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 6px;
    width: 100%;
    box-sizing: border-box;
}

input[type="radio"] {
    margin-top: 5px;
    margin-right: 5px;
}

.form-control:disabled,
.form-control[readonly] {
    background-color: #e9ecef;
    opacity: 1;
}

.btn {
    margin-top: 10px;
}

.alert {
    margin-top: 10px;
}

</style>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Editar Administrador</h3>
            </div>
            <div class="card-body">
                <?php
                // Obtener el ID del administrador a editar desde la URL
                $id_administrador = $_GET['id_administrador'] ?? null;

                if (!$id_administrador) {
                    echo '<div class="alert alert-danger" role="alert">No se ha proporcionado un ID válido.</div>';
                    exit;
                }

                // Consulta para obtener los datos del administrador a editar
                $query = "SELECT * FROM administrador WHERE id_administrador = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id_administrador);
                $stmt->execute();
                $result = $stmt->get_result();
                $administrador = $result->fetch_assoc();

                if (!$administrador) {
                    echo '<div class="alert alert-danger" role="alert">No se encontró ningún administrador con ese ID.</div>';
                    exit;
                }

                // Liberar el resultado y cerrar la consulta preparada
                $stmt->close();

                // Mostrar el formulario con los datos del administrador a editar
                ?>
                <form method="post">
                    <input type="hidden" name="id_administrador" value="<?php echo $administrador['id_administrador']; ?>">
                    <div class="mb-3 row">
                        <label for="nombres" class="col-sm-3 col-form-label form-label required">Nombres</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo htmlspecialchars($administrador['nombres']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="apellidos" class="col-sm-3 col-form-label form-label required">Apellidos</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($administrador['apellidos']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="cedula" class="col-sm-3 col-form-label form-label required">Cédula</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="cedula" name="cedula" value="<?php echo htmlspecialchars($administrador['cedula']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="telefono" class="col-sm-3 col-form-label form-label required">Teléfono</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($administrador['telefono']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="correo_electronico" class="col-sm-3 col-form-label form-label required">Correo Electrónico</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" value="<?php echo htmlspecialchars($administrador['correo_electronico']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="direccion" class="col-sm-3 col-form-label form-label required">Dirección</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($administrador['direccion']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="fecha_nacimiento" class="col-sm-3 col-form-label form-label required">Fecha de Nacimiento</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo $administrador['fecha_nacimiento']; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="genero" class="col-sm-3 col-form-label form-label required">Género</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="genero" name="genero" required>
                                <option value="M" <?php if ($administrador['genero'] == 'M') echo 'selected'; ?>>Masculino</option>
                                <option value="F" <?php if ($administrador['genero'] == 'F') echo 'selected'; ?>>Femenino</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="discapacidad" class="col-sm-3 col-form-label form-label required">Discapacidad</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="discapacidad" name="discapacidad" value="<?php echo htmlspecialchars($administrador['discapacidad']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="id_rol" class="col-sm-3 col-form-label form-label required">Rol</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="id_rol" name="id_rol" required>
                                <?php
                                // Consulta para obtener los roles disponibles
                                $query_roles = "SELECT id_rol, nombre FROM rol";
                                $result_roles = mysqli_query($conn, $query_roles);

                                // Mostrar opciones de roles en un select
                                while ($row = mysqli_fetch_assoc($result_roles)) {
                                    $selected = ($administrador['id_rol'] == $row['id_rol']) ? 'selected' : '';
                                    echo "<option value='" . $row['id_rol'] . "' $selected>" . htmlspecialchars($row['nombre']) . "</option>";
                                }

                                mysqli_free_result($result_roles);
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="contraseña" class="col-sm-3 col-form-label form-label required">Contraseña</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="contraseña" name="contraseña" value="<?php echo htmlspecialchars($administrador['contraseña']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="estado" class="col-sm-3 col-form-label form-label required">Estado</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="A" <?php if ($administrador['estado'] == 'A') echo 'selected'; ?>>Activo</option>
                                <option value="I" <?php if ($administrador['estado'] == 'I') echo 'selected'; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label form-label">Usuario que Ingresa</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($administrador['usuario_ingreso']); ?>" readonly>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label form-label">Fecha y Hora de Ingreso</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($administrador['fecha_ingreso']); ?>" readonly>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-sm-12 text-end">
                            <a href="../../views/admin/administradores.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
