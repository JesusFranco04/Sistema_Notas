<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta absoluta 

// Verificar si el usuario ha iniciado sesión y si su rol es "Administrador" o "Superusuario"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

// Obtener los filtros de búsqueda
$cedulaFiltro = isset($_GET['cedula']) ? $_GET['cedula'] : '';
$generoFiltro = isset($_GET['genero']) ? $_GET['genero'] : '';

// Consulta SQL para obtener los usuarios con filtros
$sql = "SELECT * FROM administrador WHERE cedula LIKE '%$cedulaFiltro%'";

if ($generoFiltro) {
    $sql .= " AND genero = '$generoFiltro'";
}

$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Administradores | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Estilos personalizados -->
    <style>
    /* Estilo general del cuerpo */
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f0f0;
    }

    .container-fluid {
        padding: 20px;
    }

    /* Estilo de la tarjeta */
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #E62433;
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 15px;
    }

    /* Estilo de los botones de acción */
    .action-buttons .btn {
        margin-right: 10px;
    }

    .btn-primary {
        background-color: #E62433;
        border-color: #E62433;
    }

    .btn-primary:hover {
        background-color: #DE112D;
        border-color: #DE112D;
    }

    .btn-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-info:hover {
        background-color: #138496;
        border-color: #117a8b;
    }

    .btn-success {
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .btn-success:hover {
        background-color: #e0a800;
        border-color: #d39e00;
    }

    /* Estilo de la tabla */
    .table {
        border-radius: 10px;
        overflow: hidden;
        background-color: white;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background-color: #E62433;
        color: white;
        text-align: center;
        font-weight: bold;
        border: none;
    }

    .table tbody tr {
        border-bottom: 1px solid #dee2e6;
    }

    .table tbody tr:nth-child(odd) {
        background-color: #fcccce;
        /* Rojo claro para filas impares */
    }

    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
        /* Gris claro para filas pares */
    }

    .table tbody tr:hover {
        background-color: #f8a9ad;
        /* Rojo bonito */
        color: #0a0a0a;
        /* Letras negro al pasar el ratón */
    }

    .table tbody td {
        text-align: center;
        padding: 12px;
    }

    /* Estilo para contenedor de tabla */
    .table-container {
        max-height: 500px;
        overflow-y: auto;
        /* Barra de desplazamiento vertical */
        overflow-x: auto;
        /* Barra de desplazamiento horizontal */
    }


    .section-title {
        font-size: 1.2rem;
        font-weight: bold;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    .filter-icon {
        margin-right: 5px;
    }

    .filter-container {
        margin-bottom: 1rem;
    }

    footer {
        background-color: white;
        /* Color de fondo blanco */
        color: #737373;
        /* Color del texto en gris oscuro */
        text-align: center;
        /* Centrar el texto */
        padding: 20px 0;
        /* Espaciado interno vertical */
        width: 100%;
        /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }
    </style>

</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tabla de Administradores</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="searchCedula"><i class="fas fa-search filter-icon"></i>Búsqueda por
                                Cédula</label>
                            <input type="text" class="form-control" id="searchCedula" name="cedula"
                                value="<?php echo htmlspecialchars($cedulaFiltro); ?>" maxlength="10" pattern="^\d{10}$"
                                title="Por favor ingrese exactamente 10 caracteres numéricos.">
                        </div>
                        <div class="col-md-4">
                            <label for="searchGenero"><i class="fas fa-filter filter-icon"></i>Filtrar por
                                Género</label>
                            <select class="form-control" id="searchGenero" name="genero">
                                <option value="">Todos</option>
                                <option value="femenino" <?php echo $generoFiltro == 'femenino' ? 'selected' : ''; ?>>
                                    Femenino</option>
                                <option value="masculino" <?php echo $generoFiltro == 'masculino' ? 'selected' : ''; ?>>
                                    Masculino</option>
                                <option value="otros" <?php echo $generoFiltro == 'otros' ? 'selected' : ''; ?>>Otros
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                    <div class="mb-4 mt-3">
                        <div class="row justify-content-start action-buttons">
                            <div class="col-auto">
                                <a href="http://localhost/sistema_notas/Crud/admin/usuario/registrar_usuario.php"
                                    class="btn btn-primary">
                                    Agregar Administrador
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="http://localhost/sistema_notas/views/admin/reporte_admin.php"
                                    class="btn btn-success">
                                    Descargar Reporte
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive table-container">
                    <table class="table table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Cédula</th>
                                <th>Teléfono</th>
                                <th>Correo Electrónico</th>
                                <th>Dirección</th>
                                <th>Fecha de Nacimiento</th>
                                <th>Género</th>
                                <th>Discapacidad</th>
                                <th>ID_Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($resultado) > 0) { ?>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                            <tr>
                                <td><?php echo $fila['id_administrador']; ?></td>
                                <td><?php echo $fila['nombres']; ?></td>
                                <td><?php echo $fila['apellidos']; ?></td>
                                <td><?php echo $fila['cedula']; ?></td>
                                <td><?php echo $fila['telefono']; ?></td>
                                <td><?php echo $fila['correo_electronico']; ?></td>
                                <td><?php echo $fila['direccion']; ?></td>
                                <td><?php echo $fila['fecha_nacimiento']; ?></td>
                                <td><?php echo $fila['genero']; ?></td>
                                <td><?php echo $fila['discapacidad']; ?></td>
                                <td><?php echo $fila['id_usuario']; ?></td>
                            </tr>
                            <?php } ?>
                            <?php } else { ?>
                            <tr>
                                <td colspan="11" class="text-center">No se encontraron registros que coincidan con los
                                    criterios de búsqueda.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>


                    <!-- Modal de Confirmación -->
                    <div id="modalConfirmacion" class="modal fade" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirmar Cambio de Estado</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p id="mensajeConfirmacion"></p>
                                </div>
                                <div class="modal-footer">
                                    <form id="formularioConfirmacion" method="POST"
                                        action="http://localhost/sistema_notas/Crud/admin/administrador/eliminar_admin.php">
                                        <input type="hidden" id="inputCedula" name="cedula" value="">
                                        <input type="hidden" id="inputEstado" name="estado" value="">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Cancelar</button>
                                        <button type="submit" id="botonConfirmacion"
                                            class="btn btn-primary">Confirmar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fin Modal de Confirmación -->

                </div>
            </div>
        </div>
    </div>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
    </footer>

    <!-- Scripts de JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- SB Admin 2 JS -->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <script>
    // Script para manejar la confirmación de eliminación
    document.querySelectorAll('.btn-confirmar').forEach(button => {
        button.addEventListener('click', function() {
            const cedula = this.getAttribute('data-cedula');
            const estado = this.getAttribute('data-estado');
            document.getElementById('inputCedula').value = cedula;
            document.getElementById('inputEstado').value = estado;
            document.getElementById('mensajeConfirmacion').innerText =
                `¿Estás seguro de que quieres cambiar el estado del usuario con cédula ${cedula}?`;
        });
    });
    </script>
</body>

</html>