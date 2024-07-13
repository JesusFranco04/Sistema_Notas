<?php
// Iniciar la sesión para obtener datos del usuario que está logueado
session_start();
// Incluir el archivo de configuración para conectarte a la base de datos
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = $_POST['cedula'];
    $contraseña = $_POST['contraseña'];
    $id_rol = $_POST['id_rol'];
    $estado = 'A'; // Estado activo por defecto
    $usuario_ingreso = $_SESSION['cedula'];
    $fecha_ingreso = date('Y-m-d H:i:s');


    if (!empty($cedula) && !empty($contraseña) && !empty($id_rol)) {
        $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO usuario (cedula, contraseña, id_rol, estado, usuario_ingreso, fecha_ingreso) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("ssisss", $cedula, $contraseña_hash, $id_rol, $estado, $usuario_ingreso, $fecha_ingreso);
            
            if ($stmt->execute()) {
                header("Location: http://localhost/sistema_notas/views/admin/index_admin.php");
                exit;
            } else {
                echo '<div style="color: red;">Error al crear el usuario. Inténtalo nuevamente.</div>';
            }
            
            $stmt->close();
        } else {
            echo '<div style="color: red;">Error en la preparación de la consulta.</div>';
        }
        
    } else {
        echo '<div style="color: red;">Por favor completa todos los campos.</div>';
    }
}

// Cerrar la conexión a la base de datos al finalizar
if (isset($conn)) {
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios | Sistema de Gestión UEBF</title>
    <!-- Icono del sitio -->
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Incluye Boxicons para iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/css/boxicons.min.css">
    <style>
    /* Estilos adicionales personalizados */
    .required::after {
        content: '*'; /* Añade un asterisco rojo a los campos obligatorios */
        color: red;
    }

    body {
        font-family: Arial, sans-serif; /* Fuente y estilo de la página */
        background-color: #f8f9fa; /* Color de fondo */
        margin: 0; /* Elimina el margen predeterminado */
        padding: 0; /* Elimina el relleno predeterminado */
        display: flex;
        flex-direction: column;
        min-height: 100vh; /* Altura mínima del viewport */
    }

    .container {
        max-width: 800px;
        margin: auto;
        /* Auto para centrar horizontalmente */
        margin-top: 20px;
        /* Margen superior */
        margin-bottom: 50px;
        /* Margen inferior */
        padding: 10px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        flex: 1;
        /* Para que ocupe el espacio restante verticalmente */
    }

    .card-header {
        background-color: #ef233c; /* Color de fondo del encabezado de la tarjeta */
        color: #fff; /* Color del texto del encabezado */
        padding: 15px; /* Relleno interior del encabezado */
        border-radius: 10px 10px 0 0; /* Radio de borde */
    }

    .card-title {
        margin: 0; /* Elimina el margen del título */
        font-size: 1.5rem; /* Tamaño de fuente del título */
    }

    .card-body {
        padding: 20px; /* Relleno interior del cuerpo de la tarjeta */
    }

    .form-label {
        font-weight: bold; /* Texto en negrita para etiquetas de formulario */
    }

    .form-label.required::after {
        content: " *"; /* Añade un asterisco rojo a las etiquetas de campo obligatorio */
        color: #dc3545; /* Color rojo */
    }

    .input-group-append .btn,
    .btn-primary,
    .btn-danger {
        background-color: #007bff; /* Color de fondo del botón primario */
        border-color: #007bff; /* Color del borde del botón primario */
        color: #fff; /* Color del texto del botón primario */
    }

    .input-group-append .btn:hover,
    .btn-primary:hover,
    .btn-danger:hover {
        background-color: #0056b3; /* Color de fondo al pasar el ratón por encima del botón */
        border-color: #0056b3; /* Color del borde al pasar el ratón por encima del botón */
    }

    input[type="text"],
    input[type="password"],
    input[type="email"],
    input[type="date"],
    select {
        border: 1px solid #ced4da; /* Borde de los campos de entrada */
        border-radius: 4px; /* Radio de borde */
        padding: 6px; /* Relleno interior */
        width: 100%; /* Ancho completo */
        box-sizing: border-box; /* Tamaño del borde al calcular el ancho total */
    }

    input[type="radio"] {
        margin-right: 5px; /* Margen derecho para los botones de radio */
    }

    .text-center {
        text-align: center; /* Centra el texto */
    }

    .mt-4 {
        margin-top: 1.5rem; /* Margen superior */
    }

    .mt-5 {
        margin-top: 3rem; /* Margen superior */
    }

    .mb-3 {
        margin-bottom: 1rem; /* Margen inferior */
    }

    .row {
        display: flex; /* Muestra como flexbox */
        flex-wrap: wrap; /* Envuelve en múltiples líneas si es necesario */
    }

    .col-md-6 {
        flex: 0 0 50%; /* Ocupa la mitad del espacio disponible */
        max-width: 50%; /* Ancho máximo */
        padding: 0 15px; /* Relleno interior izquierdo y derecho */
        box-sizing: border-box; /* Tamaño del borde al calcular el ancho total */
    }

    @media (max-width: 768px) {
        .col-md-6 {
            flex: 0 0 100%; /* En dispositivos pequeños, ocupa todo el ancho */
            max-width: 100%; /* Ancho máximo */
        }
    }

    .topbar {
        height: 22px; /* Altura de la barra superior */
        background-color: #c1121f; /* Color de fondo de la barra superior */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Sombra */
    }

    footer {
        background-color: #c1121f; /* Color de fondo del pie de página */
        color: #fff; /* Color del texto del pie de página */
        text-align: center; /* Alineación del texto al centro */
        padding: 20px 0; /* Relleno interior */
        width: 100%; /* Ancho completo */
    }

    footer p {
        margin: 0; /* Elimina el margen del párrafo */
    }
    </style>

</head>

<body>
    <div class="topbar"></div> <!-- Barra superior -->
    <div class="container mt-5"> <!-- Contenedor principal -->
        <div class="card"> <!-- Tarjeta -->
            <div class="card-header"> <!-- Encabezado de la tarjeta -->
                <h5 class="card-title">Formulario de Registro de Usuario</h5> <!-- Título de la tarjeta -->
            </div>
            <div class="card-body">
                <!-- Formulario de Registro -->
                <form action="http://localhost/sistema_notas/Crud/guardar_usuario.php" method="POST"
                    onsubmit="return validarFormulario()">
                    <div class="mb-3">
                        <label for="cedula" class="form-label required"><i class='bx bx-id-card'></i> Cédula:</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" maxlength="10"
                            pattern="[0-9]{10}" required>
                    </div>
                    <div class="mb-3">
                        <label for="contraseña" class="form-label required"><i class='bx bx-lock'></i>
                            Contraseña:</label>
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
                    <div class="mb-3">
                        <label for="id_rol" class="form-label required"><i class='bx bx-user-circle'></i> Rol:</label>
                        <select class="form-select" id="id_rol" name="id_rol" required>
                            <option value="">Seleccionar Rol</option>
                            <?php
                            // Consulta SQL para obtener los roles activos
                            $sql_rol = "SELECT id_rol, nombre FROM rol WHERE estado = 'A'";
                            $result_rol = mysqli_query($conn, $sql_rol);
                            if ($result_rol && mysqli_num_rows($result_rol) > 0) {
                                while ($row = mysqli_fetch_assoc($result_rol)) {
                                    echo '<option value="' . $row['id_rol'] . '">' . $row['nombre'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row row-cols-3">
                        <div class="mb-3">
                            <label class="form-label required"><i class='bx bx-check'></i> Estado:</label>
                            <input type="text" class="form-control" id="estado" name="estado" value="A" readonly
                                disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required"><i class='bx bx-user'></i> Usuario de Ingreso:</label>
                            <input type="text" class="form-control" id="usuario_ingreso" name="usuario_ingreso"
                                value="<?php echo $_SESSION['cedula']; ?>" readonly disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required"><i class='bx bx-calendar'></i> Fecha de Ingreso:</label>
                            <input type="text" class="form-control" id="fecha_ingreso" name="fecha_ingreso"
                                value="<?php echo date('Y-m-d H:i:s'); ?>" readonly disabled>
                        </div>
                    </div>
                    <div class="button-group mt-4">
                        <button type="button" class="btn btn-secondary"
                            onclick="location.href='http://localhost/sistema_notas/views/admin/index_admin.php';"><i
                                class='bx bx-arrow-back'></i> Regresar</button>
                        <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer> <!-- Pie de página -->
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano.
            Todos los derechos reservados.</p>
    </footer>
    <!-- Scripts JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
    // Función para mostrar u ocultar la contraseña
    function mostrarContrasena() {
        var contraseña = document.getElementById("contraseña");
        if (contraseña.type === "password") {
            contraseña.type = "text";
        } else {
            contraseña.type = "password";
        }
    }

    // Función para generar una clave aleatoria de 8 caracteres
    function generarClave() {
        const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let clave = '';
        for (let i = 0; i < 8; i++) {
            const randomIndex = Math.floor(Math.random() * caracteres.length);
            clave += caracteres[randomIndex];
        }
        const input_contrasena = document.getElementById('contraseña');
        input_contrasena.value = clave;
        input_contrasena.disabled = false; // Habilitar el campo si estaba deshabilitado
        document.getElementById('button-generate').disabled = true; // Deshabilitar el botón de generar
    }

    // Función para validar el formulario
    function validarFormulario() {
        var contraseña = document.getElementById('contraseña');
        contraseña.disabled = false; // Asegúrate de que el campo no esté deshabilitado antes de enviar el formulario
        return true; // Puedes agregar más validaciones si es necesario
    }
    </script>
</body>

</html>