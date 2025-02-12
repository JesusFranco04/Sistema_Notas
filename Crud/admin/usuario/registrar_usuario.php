<?php
// Iniciar la sesión para obtener datos del usuario que está logueado
session_start();
// Incluir el archivo de configuración para conectarte a la base de datos
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

if (!isset($_SESSION['cedula'])) {
    header("Location: http://localhost/sistema_notas/login.php");
    exit();
}

// Variables para mensajes de error y éxito
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validación si ya existe el usuario por cédula
    $cedula = $_POST['cedula'];
    $sql_check_cedula = "SELECT * FROM usuario WHERE cedula = ?";
    $stmt_check_cedula = $conn->prepare($sql_check_cedula);
    $stmt_check_cedula->bind_param('s', $cedula);
    $stmt_check_cedula->execute();
    $result_check_cedula = $stmt_check_cedula->get_result();

    if ($result_check_cedula->num_rows > 0) {
        $error_message = 'El usuario con esa cédula ya está registrado.';
    } else {
        // Validación del correo electrónico
        $correo_electronico = $_POST['correo_electronico'];
        
        // Utilizando una expresión regular para verificar el formato del correo electrónico
        if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'El correo electrónico no tiene un formato válido.';
        } else {
            // Procede con el proceso de inserción en la base de datos
            $nombres = $_POST['nombres'];
            $apellidos = $_POST['apellidos'];
            $telefono = $_POST['telefono'];
            $direccion = $_POST['direccion'];
            $fecha_nacimiento = $_POST['fecha_nacimiento'];
            $genero = $_POST['genero'];
            $discapacidad = $_POST['discapacidad'];
            $id_rol = $_POST['id_rol'];
            $contraseña = $_POST['contraseña']; // Contraseña en texto plano
            $estado = 'A';
            $usuario_ingreso = $_SESSION['cedula'];
            $fecha_ingreso = date('Y-m-d H:i:s');

            $conn->begin_transaction();

            try {
                // Insertar en la tabla usuario
                $sql_usuario = "INSERT INTO usuario (cedula, contraseña, id_rol, estado, usuario_ingreso, fecha_ingreso) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_usuario = $conn->prepare($sql_usuario);
                $stmt_usuario->bind_param('ssisss', $cedula, $contraseña, $id_rol, $estado, $usuario_ingreso, $fecha_ingreso);
                $stmt_usuario->execute();
                $id_usuario = $conn->insert_id; // Obtener el ID del usuario recién insertado

                // Insertar en la tabla específica del rol
                if ($id_rol == 1) {
                    // Insertar administrador
                    $sql_admin = "INSERT INTO administrador (nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, id_usuario) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_admin = $conn->prepare($sql_admin);
                    $stmt_admin->bind_param('sssssssssi', $nombres, $apellidos, $cedula, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $id_usuario);
                    $stmt_admin->execute();


                } elseif ($id_rol == 2) {
                    // Insertar profesor
                    $sql_prof = "INSERT INTO profesor (nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, id_usuario) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_prof = $conn->prepare($sql_prof);
                    $stmt_prof->bind_param('sssssssssi', $nombres, $apellidos, $cedula, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $id_usuario);
                    $stmt_prof->execute();


                } elseif ($id_rol == 3) {
                    // Insertar padre
                    $parentesco = $_POST['parentesco'];
                    $sql_padre = "INSERT INTO padre (nombres, apellidos, cedula, parentesco, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, id_usuario) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_padre = $conn->prepare($sql_padre);
                    $stmt_padre->bind_param('ssssssssssi', $nombres, $apellidos, $cedula, $parentesco, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $id_usuario);
                    $stmt_padre->execute();
                }

                $conn->commit();
                $success_message = "Usuario registrado exitosamente.";
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Error al registrar el usuario: " . $e->getMessage();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro de Usuarios | Sistema de Gestión UEBF</title>
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

    .required {
        color: red;
        /* Color rojo para los campos obligatorios */
        margin-left: 5px;
    }

    .form-group {
        margin-bottom: 20px;
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

    .btn-primary {
        background-color: #e71b2a;
        /* Color de fondo del botón */
        color: #fff;
        /* Color del texto dentro del botón */
        border: none;
        /* Elimina cualquier borde alrededor del botón */
        border-radius: 10px;
        /* Redondea las esquinas del botón con un radio de 10 píxeles */
        padding: 10px 20px;
        /* Añade espacio interno alrededor del contenido del botón */
        cursor: pointer;
        /* Cambia el cursor a una mano cuando se pasa el ratón sobre el botón */
        transition: background-color 0.3s ease;
        /* Añade una transición suave al color de fondo cuando cambia */
        margin-left: 0px;
        /* Espacio a la izquierda del botón */
        width: 150px;
        /* Ancho del botón */
        height: 38px;
        /* Altura del botón */
    }

    .btn-primary:hover {
        background-color: #c1121f;
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
    </style>
</head>

<body>
    <div class="header-banner">
        <h1>Formulario de Registro de Usuarios | Sistema de Gestión UEBF</h1>
    </div>
    <div class="container">
        <h2><i class='bx bxs-user-plus'></i> Registro de Usuario</h2>
        <!-- Mostrar mensaje de error o éxito -->
        <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <?php elseif ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validarFormulario();">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombres"><i class='bx bxs-user'></i> Nombres:<span class="required">*</span></label>
                        <input type="text" class="form-control" id="nombres" name="nombres"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras y espacios" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="apellidos"><i class='bx bxs-user-detail'></i> Apellidos:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras y espacios" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cedula"><i class='bx bxs-id-card'></i> Cédula:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="cedula" name="cedula" maxlength="10"
                            pattern="[0-9]{10}" title="Ingrese exactamente 10 dígitos numéricos" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono"><i class='bx bxs-phone'></i> Teléfono:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="telefono" name="telefono" maxlength="10"
                            pattern="09[0-9]{8}" title="El teléfono debe iniciar con 09 seguido de 8 dígitos numéricos"
                            required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="correo_electronico"><i class='bx bxs-envelope'></i> Correo Electrónico:<span
                                class="required">*</span></label>
                        <input type="email" class="form-control" id="correo_electronico" name="correo_electronico"
                            required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="direccion"><i class='bx bxs-map'></i> Dirección:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="direccion" name="direccion" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha_nacimiento"><i class='bx bxs-calendar'></i> Fecha de Nacimiento:<span
                                class="required">*</span></label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="genero"><i class='bx bx-female-sign'></i> Género:<span
                                class="required">*</span></label>
                        <select class="form-control" id="genero" name="genero" required>
                            <option value="">Seleccionar género</option>
                            <option value="femenino">Femenino</option>
                            <option value="masculino">Masculino</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="discapacidad"><i class='bx bx-handicap'></i> Discapacidad:<span
                                class="required">*</span></label>
                        <select class="form-control" id="discapacidad" name="discapacidad" required>
                            <option value="">Seleccionar discapacidad</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_rol"><i class='bx bxs-user-circle'></i> Rol:<span
                                class="required">*</span></label>
                        <select class="form-control" id="id_rol" name="id_rol" required>
                            <option value="">Seleccionar rol</option>
                            <option value="1">Administrador</option>
                            <option value="2">Profesor</option>
                            <option value="3">Padre</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="contraseña"><i class='bx bxs-lock'></i> Contraseña:<span
                                class="required">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="contraseña" name="contraseña" minlength="8"
                                required>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="button-generate"
                                    onclick="generarClave()">Generar Clave</button>
                            </div>
                        </div>
                        <small id="passwordHelp" class="form-text text-muted" ondblclick="mostrarContrasena()">
                            Haga doble clic para mostrar/ocultar la contraseña.
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="parentescoDiv" class="form-group" style="display:none;">
                        <label for="parentesco"><i class='bx bxs-group'></i> Parentesco:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="parentesco" name="parentesco"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras y espacios">
                    </div>
                </div>
            </div>
            <div class="button-group mt-4">
                <button type="button" class="btn btn-regresar"
                    onclick="location.href='http://localhost/sistema_notas/views/admin/usuario.php';">
                    <i class='bx bx-arrow-back'></i> Regresar
                </button>
                <button type="submit" class="btn btn-crear-usuario">
                    <i class='bx bx-save'></i> Crear Usuario
                </button>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano. Todos los derechos reservados.</p>
    </footer>

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

    function mostrarContrasena() {
        var contraseña = document.getElementById("contraseña");
        if (contraseña.type === "password") {
            contraseña.type = "text";
        } else {
            contraseña.type = "password";
        }
    }

    function generarClave() {
        // Definir los tipos de caracteres que se pueden usar
        const mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const minusculas = 'abcdefghijklmnopqrstuvwxyz';
        const numeros = '0123456789';
        const especiales = '!@#$%^&*()_+[]{}|;:,.<>?';
        
        // Unir todos los tipos de caracteres
        const caracteres = mayusculas + minusculas + numeros + especiales;

        // Inicializar la contraseña generada
        let clave = '';

        // Asegurarse de que la contraseña tenga al menos un carácter de cada tipo
        clave += mayusculas[Math.floor(Math.random() * mayusculas.length)];
        clave += minusculas[Math.floor(Math.random() * minusculas.length)];
        clave += numeros[Math.floor(Math.random() * numeros.length)];
        clave += especiales[Math.floor(Math.random() * especiales.length)];

        // Generar el resto de la contraseña para completar 8 caracteres
        for (let i = clave.length; i < 8; i++) {  // Limitar la longitud a 8
            const randomIndex = Math.floor(Math.random() * caracteres.length);
            clave += caracteres[randomIndex];
        }

        // Mezclar la contraseña para evitar que los primeros caracteres estén en un orden predecible
        clave = clave.split('').sort(() => Math.random() - 0.5).join('');

        // Asignar la contraseña generada al campo de entrada
        const input_contrasena = document.getElementById('contraseña');
        input_contrasena.value = clave;

        // Habilitar el campo de contraseña si estaba deshabilitado
        input_contrasena.disabled = false;

        // Deshabilitar el botón de generar para evitar múltiples clics
        document.getElementById('button-generate').disabled = true;
    }

    function mostrarMensajeError(mensaje) {
        var errorMessageContainer = document.querySelector('.error-message');
        if (!errorMessageContainer) {
            errorMessageContainer = document.createElement('div');
            errorMessageContainer.className = 'error-message';
            document.querySelector('.container').insertBefore(errorMessageContainer, document.querySelector('form'));
        }
        errorMessageContainer.textContent = mensaje;
    }

    document.getElementById('id_rol').addEventListener('change', function() {
        var rol = this.value;
        var parentescoDiv = document.getElementById('parentescoDiv');
        if (rol == 3) {
            parentescoDiv.style.display = 'block';
        } else {
            parentescoDiv.style.display = 'none';
        }


    });
    </script>
</body>

</html>