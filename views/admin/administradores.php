<?php
// Incluir el archivo de conexión y verificar la conexión
include '../../Crud/config.php';

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Consulta SQL para obtener los administradores
$sql = "SELECT * FROM administrador";
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

    .card-statistic {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        height: 150px;
    }

    .card-statistic .card-body {
        padding: 20px;
        position: relative;
    }

    .card-statistic h5 {
        font-size: 1.2rem;
        font-weight: bold;
        color: #031d44;
        display: flex;
        align-items: center;
    }

    .card-statistic h5 i {
        font-size: 1.5rem;
        margin-left: 10px;
    }

    .card-statistic p {
        font-size: 2rem;
        font-weight: bold;
        color: #305B7A;
    }

    .border-left-primary {
        border-left: 5px solid #c42021;
    }

    .border-left-success {
        border-left: 5px solid #ffeaae;
    }

    .border-left-info {
        border-left: 5px solid #47a025;
    }

    .chart-container {
        margin-top: 20px;
        background-color: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Estilo para los botones de acción dentro de la tabla */
    .table tbody .btn-action {
        margin-bottom: 10px;
        display: 10px;
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

    .filter-container {
        margin-bottom: 1rem;
    }
    </style>
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Tabla de Administradores</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="searchCedula"><i class="fas fa-id-card filter-icon"></i>Cédula</label>
                        <input type="text" class="form-control" id="searchCedula" placeholder="Buscar por cédula">
                    </div>
                    <div class="col-md-4">
                        <label for="searchFecha"><i class="fas fa-calendar-alt filter-icon"></i>Fecha de
                            Creación</label>
                        <input type="date" class="form-control" id="searchFecha">
                    </div>
                </div>
                <div class="action-buttons ml-auto mb-3">
                    <a href="../../Crud/administrador/agregar_admin.php" class="btn btn-primary btn-action">Agregar
                        Admin</a>
                    <a href="reporte_admin.php" class="btn btn-success btn-action">Generar reportes</a>
                </div>
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
                                <th>Rol</th>
                                <th>Contraseña</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $fila['id']; ?></td>
                                <td><?php echo $fila['nombres']; ?></td>
                                <td><?php echo $fila['apellidos']; ?></td>
                                <td><?php echo $fila['cedula']; ?></td>
                                <td><?php echo $fila['telefono']; ?></td>
                                <td><?php echo $fila['correo_electronico']; ?></td>
                                <td><?php echo $fila['rol']; ?></td>
                                <td><?php echo $fila['contrasena']; ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($fila['date_creation'])); ?></td>
                                <td>
                                    <a href="../../Crud/administrador/editar_admin.php?id=<?php echo $fila['id']; ?>"
                                        class="btn btn-sm btn-primary">Editar</a>
                                    <a href="../../Crud/administrador/eliminar_admin.php?id=<?php echo $fila['id']; ?>"
                                        class="btn btn-sm btn-danger">Eliminar</a>
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
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/Spanish.json"
            }
        });

        // Aplicar los filtros
        $('#searchCedula').keyup(function() {
            $('#dataTable').DataTable().column(3) // Reemplaza el número con el índice de la columna de cédula
                .search($(this).val())
                .draw();
        });

        $('#searchFecha').change(function() {
            var fecha = $(this).val();
            if (fecha !== '') {
                $('#dataTable').DataTable().column(8) // Reemplaza el número con el índice de la columna de fecha
                    .search(fecha)
                    .draw();
            }
        });
    });
    </script>
</body>

</html>
