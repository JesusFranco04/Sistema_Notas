<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador..


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $estado = 'A';
    $usuario_ingreso = $_SESSION['cedula'];
    $fecha_ingreso = date('Y-m-d H:i:s');

    if (!empty($nombre) && !empty($estado)) {
        $query = "INSERT INTO paralelo (nombre, estado, usuario_ingreso, fecha_ingreso) 
                  VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("ssss", $nombre, $estado, $usuario_ingreso, $fecha_ingreso);

            if ($stmt->execute()) {
                header("Location: http://localhost/sistema_notas/views/admin/index_admin.php");
                exit;
            } else {
                echo '<div style="color: red;">Error al crear el nivel. Int�ntalo nuevamente.</div>';
            }

            $stmt->close();
        } else {
            echo '<div style="color: red;">Error en la preparaci�n de la consulta: ' . $conn->error . '</div>';
        }
    } else {
        echo '<div style="color: red;">Por favor completa todos los campos requeridos.</div>';
    }
}

// Cerrar la conexi�n a la base de datos al finalizar
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
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/css/boxicons.min.css">
    <!-- Estilos personalizados -->
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
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        /* Asegura que el cuerpo ocupe al menos el 100% de la altura del viewport */
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
        background-color: #ef233c;
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

    .input-group-append .btn,
    .btn-primary,
    .btn-danger {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }

    .input-group-append .btn:hover,
    .btn-primary:hover,
    .btn-danger:hover {
        background-color: #0056b3;
        border-color: #0056b3;
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
        margin-right: 5px;
    }

    .text-center {
        text-align: center;
    }

    .mt-4 {
        margin-top: 1.5rem;
    }

    .mt-5 {
        margin-top: 3rem;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
    }

    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .col-md-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    .topbar {
        height: 22px;
        background-color: #c1121f;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

    <style>.required::after {
        content: '*';
        color: red;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        /* Asegura que el cuerpo ocupe al menos el 100% de la altura del viewport */
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
        background-color: #ef233c;
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

    .input-group-append .btn,
    .btn-primary,
    .btn-danger {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }

    .input-group-append .btn:hover,
    .btn-primary:hover,
    .btn-danger:hover {
        background-color: #0056b3;
        border-color: #0056b3;
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
        margin-right: 5px;
    }

    .text-center {
        text-align: center;
    }

    .mt-4 {
        margin-top: 1.5rem;
    }

    .mt-5 {
        margin-top: 3rem;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
    }

    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .col-md-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    .topbar {
        height: 22px;
        background-color: #c1121f;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
    </style>
</head>

<body>
    <div class="topbar"></div> <!-- Barra superior vacía -->
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Formulario de Registro de Paralelo</h5>
            </div>
            <div class="card-body">
                <form action="http://localhost/sistema_notas/Crud/guardar_paralelo.php" method="POST"
                    onsubmit="return validarFormulario()">
                    <div class="mb-3">
                        <label for="nombre" class="form-label required"><i class='bx bx-id-card'></i> Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required"><i class='bx bx-check'></i> Estado:</label>
                        <input type="text" class="form-control" id="estado" name="estado" value="A" readonly disabled>
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

                    <div class="button-group mt-4">
                        <button type="button" class="btn btn-secondary"
                            onclick="location.href='http://localhost/sistema_notas/views/admin/index_admin.php';"><i
                                class='bx bx-arrow-back'></i> Regresar</button>
                        <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Crear Nivel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano.
            Todos los derechos reservados.</p>
    </footer>

    <!-- Incluye Bootstrap JS para funcionalidades -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Incluye Boxicons JS para iconos -->
    <script src="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/js/boxicons.min.js"></script>
    <!-- Bootstrap core JavaScript-->
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>

</html>