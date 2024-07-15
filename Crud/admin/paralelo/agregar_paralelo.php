<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador.

// Mensajes de error y éxito
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $estado = 'A';
    $usuario_ingreso = $_SESSION['cedula'];
    $fecha_ingreso = date('Y-m-d H:i:s');

    // Verificar si el paralelo ya existe
    $query_check = "SELECT COUNT(*) as total FROM paralelo WHERE nombre = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("s", $nombre);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();

    if ($row['total'] > 0) {
        $error = "El paralelo '$nombre' ya está registrado.";
    } else {
        // Insertar en la base de datos
        $query_insert = "INSERT INTO paralelo (nombre, estado, usuario_ingreso, fecha_ingreso) 
                         VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("ssss", $nombre, $estado, $usuario_ingreso, $fecha_ingreso);

        if ($stmt_insert->execute()) {
            $success = "El paralelo '$nombre' ha sido creado exitosamente.";
        } else {
            $error = "Error al crear el paralelo. Inténtalo nuevamente.";
        }
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
    <title>Registro de Paralelos | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <style>
    /* Estilos adicionales */
    body {
        background-color: #f8f9fa;
        font-family: Arial, sans-serif;
    }

    .container {
        max-width: 800px;
        margin: 50px auto;
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
    }

    .header-banner {
        background-color: #c1121f;
        color: #fff;
        text-align: center;
        padding: 10px 0;
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

    .button-group {
        display: flex;
        justify-content: flex-end;
        /* Alineación a la derecha */
        margin-top: 20px;
        /* Ajusta según sea necesario */
    }

    .btn-secondary {
        background-color: #6c757d;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-left: 10px;
        /* Espacio entre los botones */
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .btn-primary {
        background-color: #e71b2a;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-left: 10px;
        /* Espacio entre los botones */
    }

    .btn-primary:hover {
        background-color: #c1121f;
    }
    </style>
</head>

<body>
    <div class="header-banner">
        <h1>Formulario de Registro de Paralelos | Sistema de Gestión UEBF</h1>
    </div>
    <div class="container">
        <h2><i class='bx bxs-folder-plus'></i> Registro de Paralelo</h2>
        <div class="card-body">
            <?php
            // Mostrar mensajes de éxito o error si están presentes
            if (!empty($error)) {
                echo '<div class="error-message">' . $error . '</div>';
            }
            if (!empty($success)) {
                echo '<div class="success-message">' . $success . '</div>';
            }
            ?>
            <form action="" method="POST" onsubmit="return validarFormulario()">
                <div class="mb-3">
                    <label for="nombre" class="form-label required"><i class='bx bx-font-family' ></i> Nombre:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                        title="Debe ser una única letra de la A a la Z" maxlength="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label required"><i class='bx bxs-check-square'></i> Estado:</label>
                    <input type="text" class="form-control" id="estado" name="estado" value="A" readonly disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label required"><i class='bx bxs-user-circle'></i> Usuario de Ingreso:</label>
                    <input type="text" class="form-control" id="usuario_ingreso" name="usuario_ingreso"
                        value="<?php echo $_SESSION['cedula']; ?>" readonly disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label required"><i class='bx bxs-calendar'></i> Fecha de Ingreso:</label>
                    <input type="text" class="form-control" id="fecha_ingreso" name="fecha_ingreso"
                        value="<?php echo date('Y-m-d H:i:s'); ?>" readonly disabled>
                </div>

                <div class="button-group mt-4">
                    <button type="button" class="btn btn-secondary"
                        onclick="location.href='http://localhost/sistema_notas/views/admin/index_admin.php';"><i
                            class='bx bx-arrow-back'></i> Regresar</button>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Crear Paralelo</button>
                </div>
            </form>
        </div>
    </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano. Todos los derechos reservados.</p>
    </footer>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
    function validarFormulario() {
        var nombre = document.getElementById('nombre').value.trim();

        // Validar que el campo nombre no esté vacío
        if (nombre === '') {
            document.getElementById('error-nombre').textContent = 'Por favor, ingrese exactamente un carácter.';
            return false;
        } else {
            document.getElementById('error-nombre').textContent = '';
        }

        return true; // Permitir el envío del formulario si todas las validaciones pasan
    }
    </script>
</body>

</html>