<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta absoluta 

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

// Inicializar las variables de filtro
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Construir la consulta SQL con filtros si existen
$sql = "SELECT * FROM nivel WHERE 1=1";
if (!empty($fecha)) {
    $sql .= " AND DATE(fecha_ingreso) = '$fecha'";
}
if (!empty($estado)) {
    $estadoFiltro = $estado == 'activo' ? 'A' : 'I';
    $sql .= " AND estado = '$estadoFiltro'";
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
    <title>Niveles | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
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
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    .card-header {
        background-color: #c42021;
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 15px;
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
                <h5 class="mb-0">Tabla de Niveles</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="searchFecha"><i class="fas fa-calendar-alt filter-icon"></i>Fecha de Creación</label>
                            <input type="date" class="form-control" id="searchFecha" name="fecha" value="<?php echo $fecha; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="searchEstado"><i class="fas fa-filter filter-icon"></i>Estado</label>
                            <select class="form-control" id="searchEstado" name="estado">
                                <option value="">Todos</option>
                                <option value="activo" <?php echo $estado == 'activo' ? 'selected' : ''; ?>>Activos</option>
                                <option value="inactivo" <?php echo $estado == 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                    <div class="mb-4 mt-3">
                        <div class="row justify-content-start action-buttons">
                            <div class="col-auto">
                                <a href="http://localhost/sistema_notas/Crud/admin/nivel/agregar_nivel.php" class="btn btn-primary">Agregar Nivel</a>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalInstrucciones1">Ver Manual de Uso</button>
                            </div>
                            <div class="col-auto">
                                <a href="reporte_usuario.php" class="btn btn-success">Generar reportes</a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive table-container">
                    <table class="table table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <!-- tener que estar igual que la base de datos -->
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Usuario de Ingreso</th>
                                <th>Fecha de Ingreso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td><?php echo $fila['id_nivel']; ?></td>
                                <td><?php echo $fila['nombre']; ?></td>
                                <td><?php echo $fila['estado'] == 'A' ? 'Activo' : 'Inactivo'; ?></td>
                                <td><?php echo $fila['usuario_ingreso']; ?></td>
                                <td><?php echo $fila['fecha_ingreso']; ?></td>
                                <td>
                                    <form action="http://localhost/sistema_notas/Crud/admin/nivel/inactivar_nivel.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_nivel" value="<?php echo $fila['id_nivel']; ?>">
                                        <input type="hidden" name="estado" value="<?php echo $fila['estado'] == 'A' ? 'inactivo' : 'activo'; ?>">
                                        <button type="submit" class="btn btn-<?php echo $fila['estado'] == 'A' ? 'warning' : 'success'; ?>" onclick="return mostrarModalCambioEstado(<?php echo $fila['id_nivel']; ?>, '<?php echo $fila['estado']; ?>')">
                                            <?php echo $fila['estado'] == 'A' ? 'Inactivar' : 'Activar'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                    <form id="formularioConfirmacion" method="POST" action="http://localhost/sistema_notas/Crud/admin/nivel/inactivar_nivel.php">
                        <input type="hidden" id="inputIdNivel" name="id_nivel" value="">
                        <input type="hidden" id="inputEstado" name="estado" value="">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" id="botonConfirmacion" class="btn btn-primary">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin Modal de Confirmación -->

    <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- SB Admin 2 JS -->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>

    <!-- Script para mostrar modal de confirmación -->
    <script>
    function mostrarModalCambioEstado(id_nivel, estado) {
        var mensaje = '';
        if (estado === 'A') {
            mensaje = '¿Está seguro que desea inactivar este nivel?';
        } else {
            mensaje = '¿Está seguro que desea activar este nivel?';
        }
        $('#mensajeConfirmacion').text(mensaje);
        $('#inputIdNivel').val(id_nivel);
        $('#inputEstado').val(estado === 'A' ? 'inactivo' : 'activo');
        $('#modalConfirmacion').modal('show');
        return false; // Evitar el envío del formulario inmediatamente
    }
    </script>

</body>
</html>

<?php
// Liberar resultado
mysqli_free_result($resultado);

// Cerrar conexión
$conn->close();
?>
