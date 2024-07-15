<?php
session_start();

include('../../config.php'); // Incluir el archivo de configuración para conectarte a la base de datos
date_default_timezone_set('America/Guayaquil');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Limpiar y validar los datos del formulario
    $año = $_POST['año'];
    $estado = 'A'; // Estado por defecto al crear
    $usuario_ingreso = $_SESSION['cedula'];
    $fecha_ingreso = date('Y-m-d H:i:s');

    try {
        // Verificar si el registro ya existe
        $query_check = "SELECT COUNT(*) AS count FROM historial_academico WHERE año = ?";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("s", $año);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();

        if ($row_check['count'] > 0) {
            $error = "Ya existe un registro en el historial académico para el año '$año'.";
        } else {
            // Insertar el nuevo registro en el historial académico
            $query_insert = "INSERT INTO historial_academico (año, estado, usuario_ingreso, fecha_ingreso) 
                             VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($query_insert);
            $stmt_insert->bind_param("ssss", $año, $estado, $usuario_ingreso, $fecha_ingreso);

            if ($stmt_insert->execute()) {
                $success = "El registro en el historial académico para el año '$año' ha sido creado exitosamente.";
            } else {
                $error = "Error al crear el registro en el historial académico. Inténtalo nuevamente.";
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
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
    <title>Registro de Historial Académico | Sistema de Gestión UEBF</title>
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
        <h1>Formulario de Registro del Historial Académico | Sistema de Gestión UEBF</h1>
    </div>
    <div class="container">
        <h2><i class='bx bxs-folder-plus'></i> Registro del Historial Académico</h2>
        <div class="card-body">
            <?php
            // Mostrar mensajes de éxito o error si están presentes
            if (isset($success)) {
                echo '<div class="alert alert-success">' . $success . '</div>';
            }
            if (isset($error)) {
                echo '<div class="alert alert-danger">' . $error . '</div>';
            }
            ?>
            <form action="" method="POST" onsubmit="return validarFormulario()">
                <div class="mb-3">
                    <label for="año" class="form-label required"><i class='bx bxs-calendar-plus'></i> Año:</label>
                    <input type="text" class="form-control" id="año" name="año" pattern="\d{4}\s?-\s?\d{4}"
                        title="Por favor, ingrese el año en el formato 'YYYY - YYYY', por ejemplo: '2024 - 2025'" maxlength="11" required>
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label required"><i class='bx bxs-check-square'></i> Estado:</label>
                    <input type="text" class="form-control" id="estado" name="estado" value="A" readonly disabled>
                </div>

                <div class="mb-3">
                    <label for="usuario_ingreso" class="form-label required"><i class='bx bxs-user-circle'></i> Usuario
                        de Ingreso:</label>
                    <input type="text" class="form-control" id="usuario_ingreso" name="usuario_ingreso"
                        value="<?php echo $_SESSION['cedula']; ?>" readonly disabled>
                </div>

                <div class="mb-3">
                    <label for="fecha_ingreso" class="form-label required"><i class='bx bxs-calendar'></i> Fecha de
                        Ingreso:</label>
                    <input type="text" class="form-control" id="fecha_ingreso" name="fecha_ingreso"
                        value="<?php echo date('Y-m-d H:i:s'); ?>" readonly disabled>
                </div>

                <div class="mb-3 text-center">
                    <a href="../index_admin.php" class="btn btn-cancelar"><i class='bx bx-x-circle'></i> Cancelar</a>
                    <button type="submit" class="btn btn-registrar"><i class='bx bx-save'></i> Crear Historial
                        Académico</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
    </footer>
    <!-- Incluye Bootstrap JS para funcionalidades -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validación personalizada para el campo de año
    document.getElementById('registroForm').addEventListener('submit', function(event) {
        var añoInput = document.getElementById('año');
        var pattern = /^\d{4}\s?-\s?\d{4}$/;
        if (añoInput.value.trim() === '' || !pattern.test(añoInput.value)) {
            añoInput.classList.add('is-invalid');
            event.preventDefault();
        } else {
            añoInput.classList.remove('is-invalid');
        }
    });
    </script>
</body>

</html>