<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta absoluta 

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

// Consulta SQL para obtener los usuarios
$sql = "SELECT * FROM materia";
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
    <title>Materias | Sistema de Gestión UEBF</title>
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
        background-color: #fcccce; /* Rojo claro para filas impares */
    }

    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa; /* Gris claro para filas pares */
    }

    .table tbody tr:hover {
        background-color: #f8a9ad; /* Rojo bonito */
        color: #0a0a0a; /* Letras negro al pasar el ratón */
    }

    .table tbody td {
        text-align: center;
        padding: 12px;
    }

    /* Estilo para contenedor de tabla con barras de desplazamiento */
    .table-container {
        max-height: 500px; /* Ajusta la altura máxima según tus necesidades */
        overflow-y: auto; /* Barra de desplazamiento vertical */
        overflow-x: auto; /* Barra de desplazamiento horizontal */
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
        /* Sombra más pronunciada */
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }

    .modal-header {
        background-color: #DE112D;
        /* Color rojo */
        color: white;
        /* Color del texto en blanco para que contraste */
    }

    .modal-title {
        font-weight: bold;
    }

    .close {
        color: white;
        /* Hacer que la "X" de cerrar sea blanca */
    }
</style>
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>


    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tabla de Materias</h5>
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
                                <a href="http://localhost/sistema_notas/Crud/admin/materia/agregar_materia.php"
                                    class="btn btn-primary">Agregar
                                    Materia</a>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-info" data-toggle="modal"
                                    data-target="#modalInstrucciones1">Ver Manual de Uso</button>
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
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $fila['id_materia']; ?></td>
                                <td><?php echo $fila['nombre']; ?></td>
                                <td><?php echo $fila['estado']; ?></td>
                                <td><?php echo $fila['usuario_ingreso']; ?></td>
                                <td><?php echo $fila['fecha_ingreso']; ?></td>
                                <td>
                                    <button class="btn btn-<?php echo $fila['estado'] == 'A' ? 'warning' : 'success'; ?>" 
                                            data-id="<?php echo $fila['id_materia']; ?>" 
                                            data-estado="<?php echo $fila['estado'] == 'A' ? 'inactivo' : 'activo'; ?>" 
                                            onclick="mostrarModalCambioEstado(this)">
                                        <?php echo $fila['estado'] == 'A' ? 'Inactivar' : 'Activar'; ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <!-- Manual de Uso - Parte 1 -->
                    <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
                        aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalInstruccionesLabel1">Manual de Uso - Materia (1/2)</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <ol>
                                        <li><strong>Agregar Materia:</strong>En este paso debes ingresar
                                        las Materias que tiene la Unidad Educativa </li>
                                    </ol>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                        onclick="openNextModal('#modalInstrucciones2')">Siguiente</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manual de Uso - Parte 2 -->
                    <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog"
                        aria-labelledby="modalInstruccionesLabel2" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalInstruccionesLabel2">Manual de Uso - Materia- (2/2)</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <ol start="2">
                                        <li><strong>Acciones:</strong> En Acciones tenemos Un boton de "inactivar" la cual al momento de apretarlo el registro quedara dasativado y asi mismo tendra
                                        el boton de "activar" para que el registro se pueda ver en los otros formularios.
                                        </li>
                                    </ol>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                        onclick="openNextModal('#modalInstrucciones3')">Cerrar</button>
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
                        <input type="hidden" id="inputIdMateria" name="id_materia" value="">
                        <input type="hidden" id="inputEstado" name="estado" value="">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="confirmarCambioEstado()">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
    </footer>
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
    function mostrarModalCambioEstado(button) {
        var id_materia = button.getAttribute('data-id');
        var estado = button.getAttribute('data-estado');
        var mensaje = estado === 'activo' ? '¿Está seguro que desea activar esta materia?' : '¿Está seguro que desea inactivar esta materia?';

        document.getElementById('mensajeConfirmacion').textContent = mensaje;
        document.getElementById('inputIdMateria').value = id_materia;
        document.getElementById('inputEstado').value = estado;

        $('#modalConfirmacion').modal('show');
    }

    function confirmarCambioEstado() {
        var formulario = document.getElementById('formularioConfirmacion');
        var id_materia = document.getElementById('inputIdMateria').value;
        var estado = document.getElementById('inputEstado').value;

        $.ajax({
            url: 'http://localhost/sistema_notas/Crud/admin/materia/inactivar_materia.php',
            type: 'POST',
            data: {
                id_materia: id_materia,
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

    function openNextModal(nextModalId) {
        // Cierra el modal actual
        $('.modal').modal('hide');

        // Abre el siguiente modal
        $(nextModalId).modal('show');
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