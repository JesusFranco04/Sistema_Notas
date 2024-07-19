<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil');

$mensaje = array();

// Verificar que se ha pasado el parámetro cedula
if (!isset($_GET['cedula']) || empty($_GET['cedula'])) {
    $mensaje = array(
        'texto' => 'ID de estudiante no proporcionado.',
        'clase' => 'alert-danger'
    );
} else {
    $id_estudiante = $_GET['cedula'];

    // Consultar los datos del estudiante
    $sql = "SELECT * FROM estudiante WHERE cedula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id_estudiante);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $mensaje = array(
            'texto' => 'No se encontró el estudiante con la cédula proporcionada.',
            'clase' => 'alert-danger'
        );
    } else {
        $estudiante = $result->fetch_assoc();

        // Procesar el formulario cuando se envía
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombres = trim($_POST['nombres']);
            $apellidos = trim($_POST['apellidos']);
            $telefono = !empty(trim($_POST['telefono'])) ? trim($_POST['telefono']) : null;
            $correo_electronico = !empty(trim($_POST['correo_electronico'])) ? trim($_POST['correo_electronico']) : null;
            $direccion = trim($_POST['direccion']);
            $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
            $genero = $_POST['genero'];
            $discapacidad = $_POST['discapacidad'];
            $estado = $_POST['estado'];

            // Capturar valores de usuario_ingreso y fecha_modificacion
            $usuario_ingreso = isset($_SESSION['cedula']) ? $_SESSION['cedula'] : 'admin'; // Valor por defecto si no está disponible
            $fecha_ingreso = date('Y-m-d H:i:s');

            // Construir la consulta de actualización
            $sql_update = "UPDATE estudiante SET nombres = ?, apellidos = ?, telefono = ?, correo_electronico = ?, direccion = ?, fecha_nacimiento = ?, genero = ?, discapacidad = ?, estado = ?, usuario_ingreso = ?, fecha_ingreso = ? WHERE cedula = ?";
            
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update) {
                $stmt_update->bind_param("ssssssssssss", $nombres, $apellidos, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $estado, $usuario_ingreso, $fecha_ingreso, $id_estudiante);

                if ($stmt_update->execute()) {
                    $mensaje = array(
                        'texto' => 'Estudiante actualizado correctamente.',
                        'clase' => 'alert-success'
                    );
                } else {
                    $mensaje = array(
                        'texto' => 'Error al actualizar el estudiante. Inténtalo nuevamente.',
                        'clase' => 'alert-danger'
                    );
                }

                $stmt_update->close();
            } else {
                $mensaje = array(
                    'texto' => 'Error al preparar la consulta para actualización.',
                    'clase' => 'alert-danger'
                );
            }
        }
    }
}

if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estudiante | Sistema de Gestión UEBF</title>
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
    <header class="header-banner">
        <h1>Formulario de Edición de Estudiantes | Sistema de Gestión UEBF</h1>
    </header>

    <div class="container">
        <h2><i class='bx bx-user-pin'></i>Editar Estudiante</h2>
        <?php if (!empty($mensaje)) : ?>
        <div class="alert <?= $mensaje['clase']; ?>" role="alert">
            <?= htmlspecialchars($mensaje['texto']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($estudiante)) : ?>
        <form method="POST" onsubmit="return validarFormulario()">
            <div class="form-row">
                <div class="form-col col-md-6">
                    <div class="form-group">
                        <label for="nombres" class="form-label required">Nombres:</label>
                        <input type="text" id="nombres" name="nombres" class="form-control"
                            value="<?= htmlspecialchars($estudiante['nombres']); ?>" required>
                    </div>
                </div>

                <div class="form-col col-md-6">
                    <div class="form-group">
                        <label for="apellidos" class="form-label required">Apellidos:</label>
                        <input type="text" id="apellidos" name="apellidos" class="form-control"
                            value="<?= htmlspecialchars($estudiante['apellidos']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col col-md-6">
                    <div class="form-group">
                        <label for="telefono" class="form-label">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" class="form-control"
                            value="<?= htmlspecialchars($estudiante['telefono']); ?>">
                    </div>
                </div>

                <div class="form-col col-md-6">
                    <div class="form-group">
                        <label for="correo_electronico" class="form-label">Correo Electrónico:</label>
                        <input type="email" id="correo_electronico" name="correo_electronico" class="form-control"
                            value="<?= htmlspecialchars($estudiante['correo_electronico']); ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col col-md-6">
                    <div class="form-group">
                        <label for="direccion" class="form-label required">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" class="form-control"
                            value="<?= htmlspecialchars($estudiante['direccion']); ?>" required>
                    </div>
                </div>

                <div class="form-col col-md-6">
                    <div class="form-group">
                        <label for="fecha_nacimiento" class="form-label required">Fecha de Nacimiento:</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control"
                            value="<?= htmlspecialchars($estudiante['fecha_nacimiento']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col col-md-6">
                    <div class="form-group">
                        <label for="genero" class="form-label required">Género:</label>
                        <select id="genero" name="genero" class="form-control" required>
                            <option value="">Seleccione género</option>
                            <option value="masculino" <?= $estudiante['genero'] == 'masculino' ? 'selected' : ''; ?>>
                                Masculino
                            </option>
                            <option value="femenino" <?= $estudiante['genero'] == 'femenino' ? 'selected' : ''; ?>>
                                Femenino
                            </option>
                            <option value="otros" <?= $estudiante['genero'] == 'otros' ? 'selected' : ''; ?>>Otros
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-col col-md-6">
                    <div class="form-group">
                        <label for="discapacidad" class="form-label required">Discapacidad:</label>
                        <select id="discapacidad" name="discapacidad" class="form-control" required>
                            <option value="si" <?= $estudiante['discapacidad'] == 'si' ? 'selected' : ''; ?>>Sí</option>
                            <option value="no" <?= $estudiante['discapacidad'] == 'no' ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col col-md-6">
                    <div class="form-group">
                        <label for="estado" class="form-label required">Estado:</label>
                        <select id="estado" name="estado" class="form-control" required>
                            <option value="A" <?= $estudiante['estado'] == 'A' ? 'selected' : ''; ?>>Activo</option>
                            <option value="I" <?= $estudiante['estado'] == 'I' ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="button-group mt-4">
                <button type="button" class="btn btn-regresar"
                    onclick="location.href='http://localhost/sistema_notas/views/admin/estudiantes.php';">
                    <i class='bx bx-arrow-back'></i> Regresar
                </button>
                <button type="submit" class="btn btn-crear-usuario">
                    <i class='bx bx-save'></i> Actualizar
                </button>
            </div>
        </form>
        <?php endif; ?>
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

        // Validar edad entre 11 y 18 años
        if (edad < 11 || edad > 18) {
            alert('La fecha de nacimiento no es válida. Debe tener entre 11 y 18 años.');
            return false;
        }


        var nombres = document.getElementById('nombres').value;
        var apellidos = document.getElementById('apellidos').value;
        var telefono = document.getElementById('telefono').value;
        var correo_electronico = document.getElementById('correo_electronico').value;
        var direccion = document.getElementById('direccion').value;
        var genero = document.getElementById('genero').value;
        var discapacidad = document.getElementById('discapacidad').value;
        var estado = document.getElementById('estado').value;

        if (nombres.trim() === '' || apellidos.trim() === '' || direccion.trim() === '' || fecha_nacimiento.trim() ===
            '' || genero.trim() === '' || discapacidad.trim() === '' || estado.trim() === '') {
            alert('Por favor, complete todos los campos obligatorios.');
            return false;
        }

        return true;
    }
    </script>
</body>

</html>