<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta absoluta 

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

// Inicializar las variables de filtro
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$rol = isset($_GET['rol']) ? $_GET['rol'] : '';

// Obtener roles de la base de datos
$sql_roles = "SELECT id_rol, nombre FROM rol WHERE estado = 'A'";
$resultado_roles = $conn->query($sql_roles);

if (!$resultado_roles) {
    die("Error al obtener roles: " . $conn->error);
}

// Construir la consulta SQL con filtros si existen
$sql = "SELECT * FROM usuario WHERE 1=1";
if (!empty($fecha)) {
    $sql .= " AND DATE(fecha_ingreso) = '$fecha'";
}
if (!empty($estado)) {
    $estadoFiltro = $estado == 'activo' ? 'A' : 'I';
    $sql .= " AND estado = '$estadoFiltro'";
}
if (!empty($rol)) {
    $sql .= " AND id_rol = '$rol'";
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
    <title>Usuarios | Sistema de Gestión UEBF</title>
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
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
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
        background-color: #28a745;
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
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-primary:hover {
        background-color: #218838;
        border-color: #1e7e34;
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
        background-color: #28a745;
        color: white;
        text-align: center;
        font-weight: bold;
        border: none;
    }

    .table tbody tr {
        border-bottom: 1px solid #dee2e6;
    }

    .table tbody tr:nth-child(odd) {
        background-color: #d4edda;
        /* Verde claro para filas impares */
    }

    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
        /* Gris claro para filas pares */
    }

    .table tbody tr:hover {
        background-color: #e2e6ea;
        /* Color de fondo al pasar el ratón */
    }

    .table tbody td {
        text-align: center;
        padding: 12px;
    }

    /* Estilo para contenedor de tabla */
    .table-container {
        max-height: 500px;
        overflow-y: auto;
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js">
    </script>
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tabla de Usuarios</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="row mb-4 align-items-end">
                        <div class="col-md-3">
                            <label for="searchFecha"><i class="fas fa-calendar-alt filter-icon"></i>Fecha de
                                Creación</label>
                            <input type="date" class="form-control" id="searchFecha" name="fecha"
                                value="<?php echo $fecha; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="searchEstado"><i class="fas fa-filter filter-icon"></i>Estado</label>
                            <select class="form-control" id="searchEstado" name="estado">
                                <option value="">Todos</option>
                                <option value="activo" <?php echo $estado == 'activo' ? 'selected' : ''; ?>>Activos
                                </option>
                                <option value="inactivo" <?php echo $estado == 'inactivo' ? 'selected' : ''; ?>>
                                    Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="searchRol"><i class="fas fa-user-tag filter-icon"></i>Rol</label>
                            <select class="form-control" id="searchRol" name="rol">
                                <option value="">Todos</option>
                                <?php while ($rol_row = $resultado_roles->fetch_assoc()): ?>
                                <option value="<?php echo $rol_row['id_rol']; ?>"
                                    <?php echo $rol == $rol_row['id_rol'] ? 'selected' : ''; ?>>
                                    <?php echo $rol_row['nombre']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <!-- Mantener el tamaño normal del botón -->
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                    <div class="mb-4 mt-3">
                        <div class="row justify-content-start action-buttons">
                            <div class="col-auto">
                                <a href="http://localhost/sistema_notas/Crud/admin/usuario/registrar_usuario.php"
                                    class="btn btn-primary">Agregar Usuario</a>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-info" data-toggle="modal"
                                    data-target="#modalInstrucciones1">Ver Manual de Uso</button>
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
                                <th>ID</th>
                                <th>Cédula</th>
                                <th>Contraseña</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Usuario de Ingreso</th>
                                <th>Fecha de Ingreso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td><?php echo $fila['id_usuario']; ?></td>
                                <td><?php echo $fila['cedula']; ?></td>
                                <td><?php echo $fila['contraseña']; ?></td>
                                <td><?php echo $fila['id_rol']; ?></td>
                                <td><?php echo $fila['estado'] == 'A' ? 'Activo' : 'Inactivo'; ?></td>
                                <td><?php echo $fila['usuario_ingreso']; ?></td>
                                <td><?php echo $fila['fecha_ingreso']; ?></td>
                                <td>
                                    <a href="http://localhost/sistema_notas/Crud/admin/usuario/editar_usuario.php?cedula=<?php echo $fila['cedula']; ?>"
                                        class="btn btn-info ml-2">Editar</a>
                                    <button
                                        class="btn btn-<?php echo $fila['estado'] == 'A' ? 'warning' : 'success'; ?>"
                                        data-id="<?php echo $fila['id_usuario']; ?>"
                                        data-estado="<?php echo $fila['estado'] == 'A' ? 'inactivo' : 'activo'; ?>"
                                        onclick="mostrarModalCambioEstado(this)">
                                        <?php echo $fila['estado'] == 'A' ? 'Inactivar' : 'Activar'; ?>
                                    </button>
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
                    <form id="formularioConfirmacion" method="POST">
                        <input type="hidden" id="inputIdUsuario" name="id_usuario" value="">
                        <input type="hidden" id="inputEstado" name="estado" value="">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary"
                            onclick="confirmarCambioEstado()">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin Modal de Confirmación -->

</html>

<!-- Bootstrap core JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- Core plugin JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
<!-- SB Admin 2 JS -->
<script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>

<!-- Script para mostrar modal de confirmación -->
<script>
function mostrarModalCambioEstado(button) {
    var id_usuario = button.getAttribute('data-id');
    var estado = button.getAttribute('data-estado');
    var mensaje = estado === 'activo' ? '¿Está seguro que desea activar este usuario?' :
        '¿Está seguro que desea inactivar este usuario?';

    document.getElementById('mensajeConfirmacion').textContent = mensaje;
    document.getElementById('inputIdUsuario').value = id_usuario;
    document.getElementById('inputEstado').value = estado;

    $('#modalConfirmacion').modal('show');
}

function confirmarCambioEstado() {
    var formulario = document.getElementById('formularioConfirmacion');
    var id_usuario = document.getElementById('inputIdUsuario').value;
    var estado = document.getElementById('inputEstado').value;

    $.ajax({
        url: 'http://localhost/sistema_notas/Crud/admin/usuario/inactivar_usuario.php',
        type: 'POST',
        data: {
            id_usuario: id_usuario,
            estado: estado
        },
        success: function(response) {
            $('#modalConfirmacion').modal('hide');
            location.reload(); // Recargar la página para reflejar los cambios
        },
        error: function(xhr, status, error) {
            alert('Error al cambiar el estado: ' + xhr.responseText);
        }
    });
}
</script>

</body>

</html>

<?php
// Liberar resultado
$resultado->free();

// Cerrar conexión
$conn->close();
?>