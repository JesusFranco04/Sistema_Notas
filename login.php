<?php
include('Crud/config.php');

// Definición de claves de superusuario (sin cambios)
define('SUPER_USER_KEY', '0954352185');
define('SUPER_USER_PASSWORD', 'admin340');

session_start(); // Iniciar sesión para manejar intentos fallidos
$error_message = ''; // Inicializa la variable de mensaje de error

// Inicializar la estructura de intentos fallidos si no existe
if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = [];
}

// Verificar si se está enviando el formulario por método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = trim($_POST['cedula']);
    $contraseña = trim($_POST['contraseña']);

    // Bloqueo temporal por demasiados intentos
    if (isset($_SESSION['intentos'][$cedula]) && $_SESSION['intentos'][$cedula]['bloqueado']) {
        $ultimo_intento = $_SESSION['intentos'][$cedula]['ultimo_intento'];
        $tiempo_bloqueo = strtotime($ultimo_intento) + 900; // 15 minutos de bloqueo

        if (time() < $tiempo_bloqueo) {
            $error_message = "Cuenta bloqueada temporalmente. Intente nuevamente en 15 minutos.";
        } else {
            // Desbloquear después de 15 minutos
            unset($_SESSION['intentos'][$cedula]);
        }
    }

    // Validación de superusuario
    if (empty($error_message)) { // Solo si no está bloqueado
        if ($cedula === SUPER_USER_KEY && $contraseña === SUPER_USER_PASSWORD) {
            $_SESSION['cedula'] = $cedula;
            $_SESSION['rol'] = 'Superusuario';
            header('Location: http://localhost/sistema_notas/views/admin/index_admin.php');
            exit;
        }

        // Validación de usuarios regulares
        require 'Crud/config.php';
        $sql = "SELECT u.id_usuario, u.cedula, u.contraseña, u.estado, r.nombre AS nombre_rol 
                FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
                WHERE u.cedula = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $cedula);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $contraseña === $user['contraseña']) {
            // Verificar el estado del usuario
            if ($user['estado'] === 'I') {
                $error_message = "Su cuenta se encuentra inactiva temporalmente. Si considera que es un error, contáctese con un administrador.";
            } else {
                // Reiniciar intentos fallidos al iniciar sesión correctamente
                unset($_SESSION['intentos'][$cedula]);

                // Iniciar sesión
                $_SESSION['cedula'] = $user['cedula'];
                $_SESSION['rol'] = $user['nombre_rol'];

                // Obtener id_profesor si el rol es 'Profesor'
                if ($user['nombre_rol'] === 'Profesor') {
                    $sql_profesor = "SELECT id_profesor FROM profesor WHERE id_usuario = ?";
                    $stmt_profesor = $conn->prepare($sql_profesor);
                    $stmt_profesor->bind_param('i', $user['id_usuario']);
                    $stmt_profesor->execute();
                    $result_profesor = $stmt_profesor->get_result();
                    $profesor = $result_profesor->fetch_assoc();

                    if ($profesor) {
                        $_SESSION['id_profesor'] = $profesor['id_profesor'];
                    } else {
                        $error_message = "No se encontró el ID del profesor.";
                    }
                }

                // Redirigir según el rol del usuario con un 'switch'
                if (empty($error_message)) { // Solo redirigir si no hay errores
                    switch ($user['nombre_rol']) {
                        case 'Administrador':
                        case 'Superadministrador':  // El Superadministrador tiene el mismo perfil que el Administrador
                            header("Location: http://localhost/sistema_notas/views/admin/index_admin.php");
                            break;
                        case 'Profesor':
                            header("Location: http://localhost/sistema_notas/views/profe/index_profe.php");
                            break;
                        case 'Padre':
                            header("Location: http://localhost/sistema_notas/views/family/index_family.php");
                            break;
                        default:
                            header("Location: http://localhost/sistema_notas/login.php");
                            break;
                    }
                    exit();
                }
            }
        } else {
            // Manejo de intentos fallidos
            if (!isset($_SESSION['intentos'][$cedula])) {
                $_SESSION['intentos'][$cedula] = ['contador' => 0, 'bloqueado' => false, 'ultimo_intento' => null];
            }

            $_SESSION['intentos'][$cedula]['contador']++;
            $_SESSION['intentos'][$cedula]['ultimo_intento'] = date('Y-m-d H:i:s');

            if ($_SESSION['intentos'][$cedula]['contador'] >= 3) {
                $_SESSION['intentos'][$cedula]['bloqueado'] = true;
                $error_message = "Demasiados intentos fallidos. Cuenta bloqueada temporalmente.";
            } else {
                $error_message = "Cédula o contraseña incorrecta.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INICIAR SESIÓN | SISTEMA DE GESTIÓN UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    /* Estilos generales del cuerpo */
    body {
        font-family: 'Nunito', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background: linear-gradient(135deg, #8B0000, #FF6347);
        color: #fff;
    }

    .alert-danger {
        display: <?php echo ( !empty($error_message)) ? 'block': 'none';
        ?>;
        /* Mostrar mensaje de error si existe */
    }

    /* Estilo para el contenedor principal del formulario de inicio de sesión */
    .login-container {
        display: flex;
        /* Permite al contenedor y sus hijos alinearse en una fila */
        background: #fff;
        /* Fondo blanco para el contenedor */
        border-radius: 15px;
        /* Bordes redondeados */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        /* Sombra para dar profundidad */
        overflow: hidden;
        /* Oculta el desbordamiento del contenido */
        width: 900px;
        /* Ancho fijo para el contenedor */
        max-width: 100%;
        /* Asegura que el contenedor no exceda el ancho de la pantalla */
        margin: 20px;
        /* Espacio alrededor del contenedor */
    }

    /* Estilo para la sección de la imagen en el contenedor de inicio de sesión */
    .login-image {
        flex: 1;
        /* Ocupa la mitad del contenedor */
        background: url('/Sistema_Notas/imagenes/profesor.png') no-repeat center center;
        /* Imagen de fondo centrada */
        background-size: cover;
        /* Escala la imagen para cubrir todo el área */
    }

    /* Estilo para el formulario de inicio de sesión */
    .login-form {
        flex: 1;
        /* Ocupa la otra mitad del contenedor */
        padding: 40px;
        /* Espaciado interno */
        text-align: center;
        /* Centra el texto */
        color: #8B0000;
        /* Color del texto */
        display: flex;
        /* Flexbox para el contenedor del formulario */
        flex-direction: column;
        /* Alinea los elementos en columna */
        justify-content: center;
        /* Centra los elementos verticalmente */
    }

    /* Estilo para el encabezado (h1) en el formulario de inicio de sesión */
    .login-form h1 {
        margin: 0 0 10px;
        /* Margen inferior para espacio con el siguiente elemento */
        font-size: 24px;
        /* Tamaño de fuente */
        color: #961717;
        /* Color del texto */
    }

    /* Estilo para el párrafo (p) en el formulario de inicio de sesión */
    .login-form p {
        margin: 0 0 20px;
        /* Margen inferior para espacio con el siguiente elemento */
        font-size: 14px;
        /* Tamaño de fuente */
        color: #6b7280;
        /* Color del texto */
    }

    /* Estilo para los grupos de entrada */
    .input-group {
        margin-bottom: 20px;
        /* Espacio inferior entre grupos de entrada */
        text-align: left;
        /* Alinea el texto a la izquierda */
    }

    /* Estilo para las etiquetas (label) dentro de los grupos de entrada */
    .input-group label {
        display: block;
        /* Hace que la etiqueta ocupe toda la línea */
        font-size: 14px;
        /* Tamaño de fuente */
        color: #525252;
        /* Color del texto */
        margin-bottom: 5px;
        /* Espacio inferior entre la etiqueta y el campo de entrada */
    }

    /* Estilo para los campos de entrada */
    .input-group input {
        width: 100%;
        /* Ancho completo del contenedor */
        padding: 12px;
        /* Espaciado interno */
        border: 1px solid #ddd;
        /* Borde gris claro */
        border-radius: 5px;
        /* Bordes redondeados */
        font-size: 14px;
        /* Tamaño de fuente */
        background: #f9f9f9;
        /* Fondo gris claro */
        transition: border 0.3s ease;
        /* Transición suave para el borde */
        box-sizing: border-box;
        /* Incluye el borde y el padding en el tamaño total */
    }

    /* Estilo para el texto de ayuda (small) en los grupos de entrada */
    .input-group small {
        display: block;
        /* Hace que el texto de ayuda ocupe toda la línea */
        font-size: 12px;
        /* Tamaño de fuente */
        color: #6b7280;
        /* Color del texto */
        margin-top: 5px;
        /* Espacio superior entre el campo de entrada y el texto de ayuda */
        cursor: pointer;
        /* Muestra el cursor de puntero al pasar sobre el texto */
    }

    /* Estilo para el contenedor del botón */
    .button-container {
        display: flex;
        /* Flexbox para el contenedor del botón */
        justify-content: center;
        /* Centra el botón horizontalmente */
    }

    /* Estilo para el botón de inicio de sesión */
    button {
        width: 100%;
        /* Ancho completo del contenedor del botón */
        max-width: 300px;
        /* Ancho máximo del botón */
        padding: 12px;
        /* Espaciado interno */
        background: #B22222;
        /* Color de fondo */
        border: none;
        /* Sin borde */
        border-radius: 5px;
        /* Bordes redondeados */
        color: #fff;
        /* Color del texto */
        font-size: 16px;
        /* Tamaño de fuente */
        cursor: pointer;
        /* Muestra el cursor de puntero */
        transition: background 0.3s ease;
        /* Transición suave para el fondo */
        margin-top: 20px;
        /* Espacio superior entre el formulario y el botón */
    }

    /* Estilo para el botón de inicio de sesión cuando el usuario pasa el cursor sobre él */
    button:hover {
        background: #8B0000;
        /* Color de fondo más oscuro */
    }

    /* Estilo para el mensaje de error */
    .error-message {
        color: #FF6347;
        /* Color del texto */
        font-size: 14px;
        /* Tamaño de fuente */
        display: none;
        /* Oculta el mensaje por defecto */
    }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-image"></div>
        <div class="login-form">
            <h1>Iniciar Sesión</h1>
            <p>Ingrese sus credenciales para acceder al sistema</p>

            <!-- Mostrar mensaje de error si existe -->
            <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <form id="loginForm" class="user" action="login.php" method="post" onsubmit="return validateForm()">
                <div class="input-group">
                    <label for="cedula">Cédula</label>
                    <input type="text" id="cedula" name="cedula" required
                        placeholder="Ingrese el número de identificación" maxlength="10" pattern="[0-9]{10}"
                        title="Por favor, ingrese 10 números">
                </div>
                <div class="input-group">
                    <label for="contraseña">Contraseña</label>
                    <input type="password" id="contraseña" name="contraseña" required
                        placeholder="Introduzca la contraseña" maxlength="8" pattern="[A-Za-z0-9]*"
                        title="La contraseña debe contener solamente letras y números">
                    <small id="passwordHelp" class="form-text text-muted" ondblclick="mostrarContrasena()">
                        Haga doble clic para mostrar/ocultar la contraseña.
                    </small>
                </div>
                <div class="button-container">
                    <button type="submit">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    // Función para validar el formulario de inicio de sesión
    function validateForm() {
        const cedula = document.getElementById('cedula').value;
        const contraseña = document.getElementById('contraseña').value;
        const errorMessage = document.getElementById('errorMessage');
        if (cedula.length !== 10) {
            errorMessage.textContent = 'La cédula debe tener 10 dígitos.';
            errorMessage.style.display = 'block';
            return false;
        }
        if (!/^[A-Za-z0-9]*$/.test(contraseña)) {
            errorMessage.textContent = 'La contraseña debe contener solamente letras y números.';
            errorMessage.style.display = 'block';
            return false;
        }
        errorMessage.style.display = 'none';
        return true;
    }

    // Función para mostrar u ocultar la contraseña
    function mostrarContrasena() {
        var contraseña = document.getElementById("contraseña");
        if (contraseña.type === "password") {
            contraseña.type = "text";
        } else {
            contraseña.type = "password";
        }
    }
    </script>
</body>

</html>