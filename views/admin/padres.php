<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta absoluta 

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

// Consulta SQL para obtener los usuarios
$sql = "SELECT * FROM padre";
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
    <title>Padres | Sistema de Gestión UEBF</title>
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

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #c42021;
        /* Color de fondo rojo */
        color: white;
        /* Color del texto */
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 15px;
        /* Espacio interno alrededor del contenido del encabezado */
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
                <h5 class="mb-0">Tabla de los Representantes</h5>
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
                                <a href="http://localhost/sistema_notas/Crud/admin/usuario/registrar_usuario.php"
                                    class="btn btn-primary">Agregar
                                    Padres</a>
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
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Cédula</th>
                                <th>Parentesco</th>
                                <th>Telefono</th>
                                <th>Correo Electronico</th>
                                <th>Dirección</th>
                                <th>Fecha de Nacimiento</th>
                                <th>Genero</th>
                                <th>Discapacidad</th>
                                <th>ID_Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($fila = mysqli_fetch_assoc($resultado)) {
                                ?>
                            <tr>
                                <td><?php echo $fila['id_padre']; ?></td>
                                <td><?php echo $fila['nombres']; ?></td>
                                <td><?php echo $fila['apellidos']; ?></td>
                                <td><?php echo $fila['cedula']; ?></td>
                                <td><?php echo $fila['parentesco']; ?></td>
                                <td><?php echo $fila['telefono']; ?></td>
                                <td><?php echo $fila['correo_electronico']; ?></td>
                                <td><?php echo $fila['direccion']; ?></td>
                                <td><?php echo $fila['fecha_nacimiento']; ?></td>
                                <td><?php echo $fila['genero']; ?></td>
                                <td><?php echo $fila['discapacidad']; ?></td>
                                <td><?php echo $fila['id_usuario']; ?></td>
                                <td>
                                    <a href="http://localhost/sistema_notas/Crud/admin/usuario/editar_usuario.php?cedula=<?php echo $fila['id_padre']; ?>"
                                        class="btn btn-warning btn-action">Editar</a>
                                    <button type="button" class="btn btn-danger btn-action"
                                        onclick="mostrarModalCambioEstado('<?php echo $fila['cedula']; ?>', '<?php echo $fila['id_padre']; ?>');">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
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
                                    <form id="formularioConfirmacion" method="POST" action="http://localhost/sistema_notas/Crud/admin/administrador/eliminar_admin.php">
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

                    <!-- Modal de Instrucciones -->
                    <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
                        aria-labelledby="modalInstrucciones1Label" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalInstrucciones1Label">Manual de Usuario del Sistema de
                                        Gestión
                                        UEBF</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <embed src="Manual_de_Usuario.pdf" type="application/pdf" width="100%"
                                        height="600px" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fin Modal de Instrucciones -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- SB Admin 2 JS-->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>

    <!-- Script para mostrar modal de confirmación -->
    <script>
    function mostrarModalCambioEstado(cedula, estado) {
        var mensaje = '';
        if (estado === 'A') {
            mensaje = '¿Está seguro que desea eliminar este usuario?';
        } else {
            mensaje = '¿Está seguro que desea activar este usuario?';
        }
        $('#mensajeConfirmacion').text(mensaje);
        $('#inputCedula').val(cedula);
        $('#inputEstado').val(estado);
        $('#modalConfirmacion').modal('show');
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
