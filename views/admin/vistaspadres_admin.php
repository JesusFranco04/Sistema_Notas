<?php
// Incluir el archivo de conexión y verificar la conexión
include '../../Crud/config.php';

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Consulta SQL para obtener los estudiantes
$sql = "SELECT * FROM padres";

// Obtener los valores de los filtros
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Consulta SQL para obtener los estudiantes con filtros
$sql = "SELECT * FROM padres WHERE 1=1";

if (!empty($fecha)) {
    $sql .= " AND DATE(date_creation) = '$fecha'";
}

if (!empty($estado)) {
    $sql .= " AND estado = '$estado'";
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
    <title>Representantes | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Estilos personalizados -->
<style>
        /* Estilo para el contenedor de la tabla */
        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }

        /* Estilo para separar los botones de acciones */
        .action-buttons .btn {
            margin-right: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .container-fluid {
            padding: 20px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #c42021; /* Color de fondo rojo */
            color: white; /* Color del texto */
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 15px; /* Espacio interno alrededor del contenido del encabezado */
        }

        .table thead th {
            background-color: #dc3545;
            color: white;
            text-align: center;
        }

        .table tbody td {
            text-align: center;
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
        
        .table tbody .btn-action {
            margin-bottom: 10px;
            display: inline-block;
        }


        .filter-container {
            margin-bottom: 1rem;
        }
</style>
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tabla de Representantes</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="searchFecha"><i class="fas fa-calendar-alt filter-icon"></i>Fecha de
                                Creación</label>
                            <input type="date" class="form-control" id="searchFecha" name="fecha"
                                value="<?php echo $fecha; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="searchEstado"><i class="fas fa-filter filter-icon"></i>Estado</label>
                            <select class="form-control" id="searchEstado" name="estado">
                                <option value="">Todos</option>
                                <option value="activo" <?php echo $estado == 'activo' ? 'selected' : ''; ?>>Activos
                                </option>
                                <option value="inactivo" <?php echo $estado == 'inactivo' ? 'selected' : ''; ?>>
                                    Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                    <div class="mb-4 mt-3">
                        <div class="row justify-content-start action-buttons">
                            <div class="col-auto">
                                <a href="../../Crud/padres/agregar_padres.php" class="btn btn-primary">Agregar
                                    Representante</a>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-info" data-toggle="modal"
                                    data-target="#modalInstrucciones1">Ver Manual de Uso</button>
                            </div>
                            <div class="col-auto">
                                <a href="reporte_estudiante.php" class="btn btn-success">Generar reportes</a>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive table-container">
                    <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <!-- Columnas de la tabla -->
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
                                <th>Rol</th>
                                <th>Contraseña</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                            <tr>
                                <!-- Datos de cada fila -->
                                <td><?php echo $fila['id']; ?></td>
                                <td><?php echo $fila['nombres']; ?></td>
                                <td><?php echo $fila['apellidos']; ?></td>
                                <td><?php echo $fila['cedula']; ?></td>
                                <td><?php echo $fila['telefono']; ?></td>
                                <td><?php echo $fila['correo_electronico']; ?></td>
                                <td><?php echo $fila['direccion']; ?></td>
                                <td><?php echo $fila['fecha_nacimiento']; ?></td>
                                <td><?php echo $fila['genero']; ?></td>
                                <td><?php echo $fila['discapacidad']; ?></td>
                                <td><?php echo $fila['rol']; ?></td>
                                <td><?php echo $fila['contrasena']; ?></td>
                                <td><?php echo $fila['date_creation']; ?></td>
                                <td>
                                    <a href="../../Crud/padres/actualizar_padres.php?cedula=<?php echo $fila['cedula']; ?>"
                                        class="btn btn-warning btn-action">Editar</a>
                                    <a href="../../Crud/padres/eliminar_padres.php?cedula=<?php echo $fila['cedula']; ?>"
                                        class="btn btn-danger btn-action"
                                        onclick="return confirm('¿Está seguro de eliminar este registro?');">Eliminar</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts adicionales aquí -->
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <!-- Page level plugins -->
    <script src="http://localhost/sistema_notas/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Script personalizado para la tabla -->

    <script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json"
            }
        });

        // Aplicar los filtros
        $('#searchCedula').keyup(function() {
            $('#dataTable').DataTable().column(
                    3) // Reemplaza el número con el índice de la columna de nombre
                .search(this.value)
                .draw();
        });

        $('#searchNombre').keyup(function() {
            $('#dataTable').DataTable().column(
                    1) // Reemplaza el número con el índice de la columna de nombre
                .search(this.value)
                .draw();
        });

        $('#searchFecha').keyup(function() {
            $('#dataTable').DataTable().column(
                    8) // Reemplaza el número con el índice de la columna de fecha
                .search(this.value)
                .draw();
        });
    });
    </script>
</body>

</html>