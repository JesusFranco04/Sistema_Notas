<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil');

$mensaje = array(); // Inicializar el array de mensajes

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['nombre'], $_POST['abreviatura'])) {
        $nombre = $_POST['nombre'];
        $abreviatura = $_POST['abreviatura'];
        $estado = 'A';
        $usuario_ingreso = $_SESSION['cedula'];
        $fecha_ingreso = date('Y-m-d H:i:s');

        // Verificar si el nombre y la abreviatura ya existen en la base de datos
        $check_query = "SELECT * FROM subnivel WHERE nombre = ? OR abreviatura = ?";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->bind_param("ss", $nombre, $abreviatura);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $mensaje = array(
                'texto' => 'El nombre o la abreviatura ya existen en la base de datos.',
                'clase' => 'error-message'
            );
        } else {
            $stmt_check->close();

            if (!empty($nombre) && !empty($abreviatura) && !empty($estado)) {
                $query = "INSERT INTO subnivel (nombre, abreviatura, estado, usuario_ingreso, fecha_ingreso) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);

                if ($stmt) {
                    $stmt->bind_param("sssss", $nombre, $abreviatura, $estado, $usuario_ingreso, $fecha_ingreso);

                    if ($stmt->execute()) {
                        $mensaje = array(
                            'texto' => 'Subnivel registrado correctamente.',
                            'clase' => 'success-message'
                        );
                    } else {
                        $mensaje = array(
                            'texto' => 'Error al crear el subnivel. Inténtalo nuevamente.',
                            'clase' => 'error-message'
                        );
                    }
    
                    $stmt->close();
                } else {
                    $mensaje = array(
                        'texto' => 'Error al preparar la consulta.',
                        'clase' => 'error-message'
                    );
                }
            } else {
                $mensaje = array(
                    'texto' => 'Todos los campos son obligatorios.',
                    'clase' => 'error-message'
                );
            }
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
    <title>Registro de Subniveles | Sistema de Gestión UEBF</title>
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
        <h1>Formulario de Registro de Subniveles | Sistema de Gestión UEBF</h1>
    </div>
    <div class="container">
        <h2><i class='bx bxs-folder-plus'></i> Registro de Subnivel</h2>
        <div class="card-body">
            <?php
                // Mostrar mensajes de éxito o error si están presentes
                if (isset($mensaje['texto']) && isset($mensaje['clase'])) {
                    echo '<div class="' . $mensaje['clase'] . '">' . $mensaje['texto'] . '</div>';
                }
                ?>
            <form action="" method="POST" onsubmit="return validarFormulario()">
                <div class="mb-3">
                    <label for="nombre" class="form-label required"><i class='bx bxs-chalkboard'></i> Nombre:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                        title="Ingrese solo letras y espacios" maxlength="40" required>
                </div>

                <div class="mb-3">
                    <label for="abreviatura" class="form-label required"><i class='bx bxl-behance'></i>
                        Abreviatura:</label>
                    <input type="text" class="form-control" id="abreviatura" name="abreviatura"
                        pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras" maxlength="3" required>
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
                <div class="mb-3 text-center">
                    <button type="button" class="btn btn-secondary"
                        onclick="location.href='http://localhost/sistema_notas/views/admin/subnivel.php';"><i
                            class='bx bx-arrow-back'></i> Regresar</button>
                    <button type="submit" class="btn btn-registrar"><i class='bx bx-save'></i> Crear Subnivel</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

    <script>
    function validarFormulario() {
        var nombre = document.getElementById("nombre").value;
        var abreviatura = document.getElementById("abreviatura").value;

        if (!nombrePattern.test(nombre)) {
            alert("El nombre solo puede contener letras y espacios.");
            return false;
        }

        if (!abreviaturaPattern.test(abreviatura)) {
            alert("La abreviatura solo puede contener letras.");
            return false;
        }

        return true;
    }
    </script>
</body>

</html>