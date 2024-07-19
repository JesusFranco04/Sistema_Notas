<?php
// Iniciar la sesión para obtener datos del usuario que está logueado
session_start();

// Incluir el archivo de configuración para conectarte a la base de datos
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

// Verificar si el usuario está logueado
if (!isset($_SESSION['cedula'])) {
    header("Location: http://localhost/sistema_notas/login.php");
    exit();
}

$error_message = '';
$success_message = '';

// Obtener cédula del usuario a editar
$cedula = $_GET['cedula'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $discapacidad = $_POST['discapacidad'];
    $id_rol = $_POST['id_rol'];
    $correo_electronico = $_POST['correo_electronico'];
    
    // Validación del correo electrónico
    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'El correo electrónico no tiene un formato válido.';
    } else {
        try {
            // Iniciar la transacción
            $conn->begin_transaction();

            // Actualizar en la tabla usuario
            $sql_usuario = "UPDATE usuario SET id_rol = ? WHERE cedula = ?";
            $stmt_usuario = $conn->prepare($sql_usuario);
            $stmt_usuario->bind_param('is', $id_rol, $cedula);
            $stmt_usuario->execute();

            // Actualizar en la tabla específica del rol
            if ($id_rol == 1) {
                $sql_admin = "UPDATE administrador SET nombres = ?, apellidos = ?, telefono = ?, correo_electronico = ?, direccion = ?, fecha_nacimiento = ?, genero = ?, discapacidad = ? WHERE cedula = ?";
                $stmt_admin = $conn->prepare($sql_admin);
                $stmt_admin->bind_param('sssssssss', $nombres, $apellidos, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $cedula);
                $stmt_admin->execute();
            } elseif ($id_rol == 2) {
                $sql_prof = "UPDATE profesor SET nombres = ?, apellidos = ?, telefono = ?, correo_electronico = ?, direccion = ?, fecha_nacimiento = ?, genero = ?, discapacidad = ? WHERE cedula = ?";
                $stmt_prof = $conn->prepare($sql_prof);
                $stmt_prof->bind_param('sssssssss', $nombres, $apellidos, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $cedula);
                $stmt_prof->execute();
            } elseif ($id_rol == 3) {
                $parentesco = $_POST['parentesco'];
                $sql_padre = "UPDATE padre SET nombres = ?, apellidos = ?, parentesco = ?, telefono = ?, correo_electronico = ?, direccion = ?, fecha_nacimiento = ?, genero = ?, discapacidad = ? WHERE cedula = ?";
                $stmt_padre = $conn->prepare($sql_padre);
                $stmt_padre->bind_param('ssssssssss', $nombres, $apellidos, $parentesco, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $cedula);
                $stmt_padre->execute();
            }

            // Confirmar la transacción
            $conn->commit();
            $success_message = "Usuario actualizado exitosamente.";
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $conn->rollback();
            $error_message = "Error al actualizar el usuario: " . $e->getMessage();
        }
    }
}

// Obtener datos actuales del usuario
$sql = "SELECT * FROM usuario WHERE cedula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $cedula);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Usuario no encontrado.");
}

$datos_usuario = $user;
$id_rol = $datos_usuario['id_rol'];

// Obtener datos del usuario según el rol
if ($id_rol == 1) {
    $sql_roles = "SELECT * FROM administrador WHERE cedula = ?";
} elseif ($id_rol == 2) {
    $sql_roles = "SELECT * FROM profesor WHERE cedula = ?";
} elseif ($id_rol == 3) {
    $sql_roles = "SELECT * FROM padre WHERE cedula = ?";
}
$stmt_roles = $conn->prepare($sql_roles);
$stmt_roles->bind_param('s', $cedula);
$stmt_roles->execute();
$result_roles = $stmt_roles->get_result();
$rol_data = $result_roles->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Usuario | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .header-banner {
        background-color: #c1121f;
        color: #fff;
        text-align: center;
        padding: 20px 0;
    }

    .header-banner h1 {
        margin: 0;
        font-size: 24px;
    }

    .required {
        color: red;
        /* Color rojo para los campos obligatorios */
        margin-left: 5px;
    }


    .container {
        max-width: 800px;
        margin: 50px auto;
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
        background-color: #e71b2a;
        padding: 10px;
        border-radius: 10px;
        color: #fff;
    }

    .form-label.required::after {
        content: " *";
        color: red;
        margin-left: 5px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .btn-cancelar {
        background-color: #6c757d;
        color: #fff;
    }

    .btn-registrar {
        background-color: #e71b2a;
        color: #fff;
    }

    .form-label {
        font-weight: bold;
        color: #333;
    }

    .bx {
        margin-right: 10px;
    }

    #button-generate {
        background-color: #e71b2a;
        color: #fff;
        border-color: #e71b2a;
        width: 100%;
    }

    #button-generate:hover {
        background-color: #c1121f;
        border-color: #c1121f;
    }

    .optional-text {
        font-size: 12px;
        /* Tamaño pequeño de la letra */
        color: #999;
        /* Color gris */
        margin-left: 5px;
    }


    footer {
        background-color: #c1121f;
        color: #fff;
        text-align: center;
        padding: 20px 0;
        width: 100%;
    }

    footer p {
        margin: 0;
    }

    .error-message,
    .success-message {
        margin: 10px 0;
        padding: 10px;
        border-radius: 5px;
        text-align: center;
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
    }

    .success-message {
        background-color: #d4edda;
        color: #155724;
    }

    /* Alinear el grupo de botones a la derecha */
    .button-group {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        /* Espacio entre los botones */
    }

    /* Estilos generales para los botones */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        /* Tamaño de fuente reducido */
        padding: 8px 16px;
        /* Reducción de relleno */
        border-radius: 6px;
        /* Borde más suave */
        border: none;
        cursor: pointer;
        /* Cambia el cursor a una mano cuando se pasa el ratón sobre el botón */
        transition: background-color 0.3s ease;
        /* Añade una transición suave al color de fondo cuando cambia */
        text-transform: uppercase;
        font-weight: bold;
        color: white;
        /* Color del texto en todos los botones */
    }

    /* Estilos para el botón Regresar */
    .btn-regresar {
        background-color: #6c757d;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-regresar:hover {
        background-color: #5a6268;
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-regresar:active {
        background-color: #545b62;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    /* Estilos para el botón Crear Usuario */
    .btn-crear-usuario {
        background-color: #e71b2a;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-crear-usuario:hover {
        background-color: #c21623;
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-crear-usuario:active {
        background-color: #a0121d;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    /* Icono dentro del botón */
    .btn i {
        margin-right: 8px;
    }
    </style>
</head>

<body>
    <div class="header-banner">
        <h1>Formulario de Edición de Usuarios | Sistema de Gestión UEBF</h1>
    </div>
    <div class="container">
        <h2><i class='bx bxs-user'></i> Editar Usuario</h2>
        <!-- Mostrar mensaje de error o éxito -->
        <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <?php elseif ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validarFormulario()">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombres"><i class='bx bxs-user'></i> Nombres: <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nombres" name="nombres"
                            value="<?php echo htmlspecialchars($rol_data['nombres']); ?>"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras y espacios" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="apellidos"><i class='bx bxs-user-detail'></i> Apellidos: <span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos"
                            value="<?php echo htmlspecialchars($rol_data['apellidos']); ?>"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras y espacios" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cedula"><i class='bx bxs-id-card'></i> Cédula: <span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="cedula" name="cedula"
                            value="<?php echo htmlspecialchars($cedula); ?>" maxlength="10" pattern="[0-9]{10}"
                            title="Ingrese exactamente 10 dígitos numéricos" readonly required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono"><i class='bx bxs-phone'></i> Teléfono: <span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="telefono" name="telefono"
                            value="<?php echo htmlspecialchars($rol_data['telefono']); ?>" maxlength="10"
                            pattern="09[0-9]{8}" title="El teléfono debe iniciar con 09 seguido de 8 dígitos numéricos"
                            required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="correo_electronico"><i class='bx bxs-envelope'></i> Correo Electrónico: <span
                                class="required">*</span></label>
                        <input type="email" class="form-control" id="correo_electronico" name="correo_electronico"
                            value="<?php echo htmlspecialchars($rol_data['correo_electronico']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="direccion"><i class='bx bxs-home'></i> Dirección: <span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="direccion" name="direccion"
                            value="<?php echo htmlspecialchars($rol_data['direccion']); ?>" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha_nacimiento"><i class='bx bxs-calendar'></i> Fecha de Nacimiento: <span
                                class="required">*</span></label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                            value="<?php echo htmlspecialchars($rol_data['fecha_nacimiento']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="genero"><i class='bx bx-female-sign'></i> Género: <span
                                class="required">*</span></label>
                        <select class="form-control" id="genero" name="genero" required>
                            <option value="">Seleccione género</option>
                            <option value="Masculino"
                                <?php echo $rol_data['genero'] == 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="Femenino" <?php echo $rol_data['genero'] == 'Femenino' ? 'selected' : ''; ?>>
                                Femenino</option>
                            <option value="Otros" <?php echo $rol_data['genero'] == 'Otros' ? 'selected' : ''; ?>>Otros
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="discapacidad"><i class='bx bx-handicap'></i> Discapacidad: <span class="required">*</span></label>
                        <input type="text" class="form-control" id="discapacidad" name="discapacidad"
                            value="<?php echo htmlspecialchars($rol_data['discapacidad']); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_rol"><i class='bx bxs-user-circle'></i> Rol: <span
                                class="required">*</span></label>
                        <select class="form-control" id="id_rol" name="id_rol" required>
                            <option value="1" <?php echo $id_rol == 1 ? 'selected' : ''; ?>>Administrador</option>
                            <option value="2" <?php echo $id_rol == 2 ? 'selected' : ''; ?>>Profesor</option>
                            <option value="3" <?php echo $id_rol == 3 ? 'selected' : ''; ?>>Padre</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php if ($id_rol == 3): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="parentesco"><i class='bx bxs-user'></i> Parentesco: </label>
                        <input type="text" class="form-control" id="parentesco" name="parentesco"
                            value="<?php echo htmlspecialchars($rol_data['parentesco']); ?>">
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="button-group mt-4">
                <button type="button" class="btn btn-regresar"
                    onclick="location.href='http://localhost/sistema_notas/views/admin/usuario.php';">
                    <i class='bx bx-arrow-back'></i> Regresar
                </button>
                <button type="submit" class="btn btn-crear-usuario">
                    <i class='bx bx-save'></i> Actualizar
                </button>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano. Todos los derechos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function validarFormulario() {
        var fechaNacimiento = document.getElementById('fecha_nacimiento').value.trim();
        var hoy = new Date();
        var fechaNac = new Date(fechaNacimiento);
        var edad = hoy.getFullYear() - fechaNac.getFullYear();
        var mes = hoy.getMonth() - fechaNac.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }
        if (edad < 18 || edad > 80) {
            alert('La fecha de nacimiento no es válida. Debe tener entre 18 y 80 años.');
            return false;
        }
        return true;
    } 
    </script>
</body>

</html>