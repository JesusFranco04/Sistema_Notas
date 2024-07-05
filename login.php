<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <!-- Configura la vista para que sea responsiva en dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Título de la página -->
    <title>INICIAR SESIÓN | SISTEMA DE GESTIÓN UEBF</title>
    <!-- Favicon de la página -->
    <link rel="shortcut icon" href="imagenes/logo.png" type="image/x-icon">
    <!-- Fuente de Google para los textos -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <!-- Hoja de estilos principal del proyecto -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Iconos de boxicons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/boxicons/2.0.7/css/boxicons.min.css">
    <!-- Estilos internos de la página -->
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

        /* Estilos del contenedor de login */
        .login-container {
            display: flex;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 900px;
            max-width: 100%;
            margin: 20px;
        }

        /* Estilos de la imagen del login */
        .login-image {
            flex: 1;
            background: url('imagenes/profesor.png') no-repeat center center;
            background-size: cover;
        }

        /* Estilos del formulario de login */
        .login-form {
            flex: 1;
            padding: 40px;
            text-align: center;
            color: #8B0000;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Estilos del título del formulario */
        .login-form h1 {
            margin: 0 0 10px;
            font-size: 24px;
            color: #961717;
        }

        /* Estilos del párrafo de instrucciones */
        .login-form p {
            margin: 0 0 20px;
            font-size: 14px;
            color: #6b7280;
        }

        /* Estilos del grupo de entrada */
        .input-group {
            margin-bottom: 20px;
            text-align: left;
            position: relative;
        }

        /* Estilos de las etiquetas */
        .input-group label {
            display: block;
            font-size: 14px;
            color: #525252;
            margin-bottom: 5px;
        }

        /* Estilos de los campos de entrada */
        .input-group input {
            width: calc(100% - 30px);
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background: #f9f9f9;
            transition: border 0.3s ease;
            box-sizing: border-box;
        }

        /* Estilos del botón para mostrar/ocultar contraseña */
        .input-group .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #ccc;
        }

        /* Cambia el color del botón de mostrar/ocultar contraseña al pasar el cursor */
        .input-group .toggle-password:hover {
            color: #8B0000;
        }

        /* Estilos del contenedor del botón */
        .button-container {
            display: flex;
            justify-content: center;
        }

        /* Estilos del botón de enviar */
        button {
            width: 100%;
            max-width: 300px;
            padding: 12px;
            background: #B22222;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 20px;
        }

        /* Cambia el color de fondo del botón al pasar el cursor */
        button:hover {
            background: #8B0000;
        }

        /* Estilos del mensaje de error */
        .error-message {
            color: #FF6347;
            font-size: 14px;
            display: none;
        }
    </style>
</head>

<body>
    <!-- Contenedor principal del login -->
    <div class="login-container">
        <!-- Imagen de la parte izquierda del contenedor -->
        <div class="login-image"></div>
        <!-- Formulario de la parte derecha del contenedor -->
        <div class="login-form">
            <!-- Título del formulario -->
            <h1>Iniciar Sesión</h1>
            <!-- Instrucciones para el usuario -->
            <p>Por favor, introduzca sus credenciales para acceder al sistema</p>
            <!-- Formulario de login -->
            <form id="loginForm" class="user" action="../Sistema_Notas/Crud/lg_admin.php" method="post" onsubmit="return validateForm()">
                <!-- Grupo de entrada para la cédula -->
                <div class="input-group">
                    <label for="cedula">Cédula</label>
                    <input type="text" id="cedula" name="cedula" required placeholder="Ingrese el número de identificación" maxlength="10" pattern="[0-9]{10}" title="Por favor, ingrese 10 números">
                </div>
                <!-- Grupo de entrada para la contraseña -->
                <div class="input-group">
                    <label for="contraseña">Contraseña</label>
                    <input type="password" id="contraseña" name="contraseña" required placeholder="Introduzca la contraseña" maxlength="20" pattern="[A-Za-z0-9]*" title="La contraseña debe contener solamente letras y números">
                    <!-- Botón para mostrar/ocultar la contraseña -->
                    <span class="toggle-password" onclick="togglePassword()">
                        <i class='bx bxs-show'></i>
                    </span>
                </div>
                <!-- Contenedor del botón de enviar -->
                <div class="button-container">
                    <button type="submit">Iniciar Sesión</button>
                </div>
                <!-- Mensaje de error -->
                <p class="error-message" id="errorMessage"></p>
            </form>
        </div>
    </div>

    <!-- Scripts para validar y mostrar/ocultar la contraseña -->
    <script>
        // Función para validar el formulario antes de enviarlo
        function validateForm() {
            const cedula = document.getElementById('cedula').value;
            const contraseña = document.getElementById('contraseña').value;
            const errorMessage = document.getElementById('errorMessage');

            // Verifica que la cédula tenga 10 dígitos
            if (cedula.length !== 10) {
                errorMessage.textContent = 'La cédula debe tener 10 dígitos.';
                errorMessage.style.display = 'block';
                return false;
            }

            // Verifica que la contraseña solo contenga letras y números
            if (!/^[A-Za-z0-9]*$/.test(contraseña)) {
                errorMessage.textContent = 'La contraseña debe contener solamente letras y números.';
                errorMessage.style.display = 'block';
                return false;
            }

            // Si todo está correcto, oculta el mensaje de error y permite el envío del formulario
            errorMessage.style.display = 'none';
            return true;
        }

        // Función para mostrar/ocultar la contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('contraseña');
            const togglePasswordIcon = document.querySelector('.toggle-password i');

            // Cambia el tipo de input entre 'password' y 'text'
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                togglePasswordIcon.classList.remove('bxs-show');
                togglePasswordIcon.classList.add('bxs-hide');
            } else {
                passwordInput.type = 'password';
                togglePasswordIcon.classList.remove('bxs-hide');
                togglePasswordIcon.classList.add('bxs-show');
            }
        }
    </script>
</body>

</html>
