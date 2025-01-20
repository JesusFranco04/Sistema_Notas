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

// Inicializar las variables de filtro
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
// Consulta SQL para obtener las materias con los filtros aplicados
$sql = "SELECT * FROM materia WHERE 1=1";

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

    /* Estilo para contenedor de tabla con barras de desplazamiento */
    .table-container {
        max-height: 500px;
        /* Ajusta la altura máxima según tus necesidades */
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

    /* Estilo del header del modal */
    .modal-header {
        background-color: #DE112D;
        /* Rojo */
        color: white;
        /* Texto en blanco */
        border-bottom: 2px solid #B50D22;
        /* Bordes más definidos */
    }

    .modal-title {
        font-weight: bold;
        font-size: 1.25rem;
        /* Tamaño ligeramente más grande */
    }

    /* Estilo para el botón de cerrar */
    .close {
        color: white;
        /* "X" en blanco */
        opacity: 0.8;
        /* Transparencia sutil */
    }

    .close:hover {
        opacity: 1;
        /* Más visible al pasar el cursor */
    }

    /* Botones del modal */
    .modal-footer .btn-secondary {
        background-color: #07244a;
        /* Azul oscuro */
        color: white;
        /* Texto en blanco */
        border: none;
        /* Sin borde */
        transition: background-color 0.3s ease;
        /* Animación suave */
    }

    .modal-footer .btn-secondary:hover {
        background-color: #053166;
        /* Azul más claro al pasar el cursor */
    }

    /* Botón Siguiente (verde) */
    .modal-footer .btn-success {
        background-color: #28a745;
        /* Verde */
        color: white;
        /* Texto en blanco */
        border: none;
        /* Sin borde */
        transition: background-color 0.3s ease;
        /* Animación suave */
    }

    .modal-footer .btn-success:hover {
        background-color: #218838;
        /* Verde más oscuro al pasar el cursor */
    }

    /* Botón Atrás (azul oscuro) */
    .modal-footer .btn-info {
        background-color: #17a2b8;
        /* Azul claro */
        color: white;
        /* Texto en blanco */
        border: none;
        /* Sin borde */
        transition: background-color 0.3s ease;
        /* Animación suave */
    }

    .modal-footer .btn-info:hover {
        background-color: #117a8b;
        /* Azul más oscuro al pasar el cursor */
    }

    /* Botón Cerrar (gris oscuro) */
    .modal-footer .btn-dark {
        background-color: #343a40;
        /* Gris oscuro */
        color: white;
        /* Texto en blanco */
        border: none;
        /* Sin borde */
        transition: background-color 0.3s ease;
        /* Animación suave */
    }

    .modal-footer .btn-dark:hover {
        background-color: #23272b;
        /* Gris más oscuro al pasar el cursor */
    }

    /* Ajustes generales del modal */
    .modal-content {
        border-radius: 8px;
        /* Bordes redondeados */
        overflow: hidden;
        /* Evitar desbordes */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        /* Sombra para profundidad */
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
                            <!-- Botón para agregar materia -->
                            <div class="col-auto">
                                <a href="http://localhost/sistema_notas/Crud/admin/materia/agregar_materia.php"
                                    class="btn btn-primary">Agregar
                                    Materia</a>
                            </div>
                            <!-- Botón para ver manual de uso -->
                            <div class="col-auto">
                                <button type="button" class="btn btn-secondary" data-toggle="modal"
                                    data-target="#modalInstrucciones1">
                                    Ver Manual de Uso
                                </button>
                            </div>
                            <!-- Botón para descargar reporte -->
                            <div class="col-auto">
                                <a href="http://localhost/sistema_notas/views/admin/reporte_materia.php"
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
                            <?php if ($resultado->num_rows > 0): ?>
                            <!-- Verifica si hay registros en la base de datos -->
                            <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $fila['id_materia']; ?></td>
                                <td><?php echo $fila['nombre']; ?></td>
                                <td><?php echo $fila['estado']; ?></td>
                                <td><?php echo $fila['usuario_ingreso']; ?></td>
                                <td><?php echo $fila['fecha_ingreso']; ?></td>
                                <td>
                                    <button
                                        class="btn btn-<?php echo $fila['estado'] == 'A' ? 'warning' : 'success'; ?>"
                                        data-id="<?php echo $fila['id_materia']; ?>"
                                        data-estado="<?php echo $fila['estado'] == 'A' ? 'inactivo' : 'activo'; ?>"
                                        onclick="mostrarModalCambioEstado(this)">
                                        <?php echo $fila['estado'] == 'A' ? 'Inactivar' : 'Activar'; ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <!-- Aquí se coloca el colspan correcto, 6 columnas -->
                                <td colspan="6" class="text-center">No se encontraron registros disponibles en este
                                    momento.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Modal 1 - Registro de Materia -->
                    <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
                        aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalInstruccionesLabel1">Manual de Uso - Gestión de
                                        Materias (1/3)</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>¿Cómo puedo agregar una nueva materia?</strong></p>
                                    <p>Para agregar una nueva materia, sigue estos pasos:</p>
                                    <ol>
                                        <li><strong>¿Dónde debo ir?</strong> En la parte superior de la página,
                                            encontrarás un botón que dice <strong><i>"Agregar Materia"</i></strong>.
                                            Este
                                            botón te llevará a la página donde podrás registrar la materia.</li>
                                        <li><strong>¿Qué debo ingresar?</strong> Una vez que estés en el formulario,
                                            verás un campo llamado <strong>"Nombre"</strong>. Aquí deberás
                                            escribir el nombre de la materia que deseas agregar, como por ejemplo:
                                            "Matemáticas", "Historia", "Ciencias", etc. ¡Este campo es obligatorio!</li>
                                        <li><strong>¿Qué pasa con el <i>Estado</i> y la <i>Fecha de
                                                    Ingreso</i>?</strong> No te preocupes por estos campos. El
                                            <strong>"Estado"</strong> de la materia se asignará automáticamente como
                                            <strong>Activo</strong>, y la <strong>"Fecha de Ingreso"</strong> también se
                                            completará automáticamente con la fecha y hora actuales.
                                        </li>
                                        <li><strong>¿Cómo finalizo el registro?</strong> Después de escribir el nombre
                                            de la materia, solo haz clic en el botón rojo que dice <strong><i>"Crear
                                                    Materia"</i></strong>. Este botón registrará la materia en el
                                            sistema.</li>
                                    </ol>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary"
                                        onclick="openModal('#modalInstrucciones2')">Siguiente</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal 2 - Filtrar Materias -->
                    <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog"
                        aria-labelledby="modalInstruccionesLabel2" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalInstruccionesLabel2">Manual de Uso - Filtrar
                                        Materias (2/3)</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>¿Cómo puedo buscar y filtrar las materias?</strong></p>
                                    <p>En la parte superior de la tabla de materias, tienes opciones para filtrar los
                                        resultados:</p>
                                    <ul>
                                        <li><strong>¿Cómo filtrar por fecha de creación?</strong> Usa el campo
                                            <strong><i>"Fecha de Creación"</i></strong>. Selecciona una fecha específica
                                            o
                                            un rango de fechas para ver solo las materias creadas en ese período.
                                        </li>
                                        <li><strong>¿Cómo filtrar por estado?</strong> Usa el campo
                                            <strong><i>"Estado"</i></strong> para elegir entre las opciones disponibles:
                                            <ul>
                                                <li><strong>Todos:</strong> Ver todas las materias, sin importar su
                                                    estado.</li>
                                                <li><strong>Activos:</strong> Ver solo las materias que están activas.
                                                </li>
                                                <li><strong>Inactivos:</strong> Ver solo las materias que están
                                                    inactivas.</li>
                                            </ul>
                                        </li>
                                        <li><strong>¿Cómo aplicar los filtros?</strong> Después de seleccionar las
                                            opciones de filtro que deseas, haz clic en el botón
                                            <strong><i>"Filtrar"</i></strong>. Esto actualizará la tabla para mostrarte
                                            solo las materias que coincidan con los filtros aplicados.
                                        </li>
                                    </ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        onclick="openModal('#modalInstrucciones1')">Atrás</button>
                                    <button type="button" class="btn btn-primary"
                                        onclick="openModal('#modalInstrucciones3')">Siguiente</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal 3 - Gestionar Estado de Materias -->
                    <div class="modal fade" id="modalInstrucciones3" tabindex="-1" role="dialog"
                        aria-labelledby="modalInstruccionesLabel3" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalInstruccionesLabel3">Manual de Uso - Gestionar
                                        Estado de Materias (3/3)</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>¿Cómo puedo activar o inactivar una materia?</strong></p>
                                    <p>En la tabla de materias, encontrarás un botón en la columna de
                                        <strong><i>"Acciones"</i></strong> junto a cada materia. Este botón te permite
                                        cambiar el estado de la materia:
                                    </p>
                                    <ul>
                                        <li><strong>¿Cómo inactivar una materia?</strong> Si la materia está activa y
                                            quieres desactivarla (por ejemplo, si ya no está disponible), haz clic
                                            en el botón que dice <strong><i>"Inactivar"</i></strong>.</li>
                                        <li><strong>¿Cómo activar una materia?</strong> Si la materia está inactiva y
                                            deseas volver a activarla (por ejemplo, si va a estar disponible
                                            nuevamente),
                                            haz clic en el botón que dice <strong><i>"Activar"</i></strong>.</li>
                                    </ul>
                                    <p>Recuerda que esta acción cambiará el estado de la materia en el sistema.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        onclick="openModal('#modalInstrucciones2')">Atrás</button>
                                    <button type="button" class="btn btn-dark" data-dismiss="modal">Cerrar</button>
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
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Cancelar</button>
                                        <button type="button" class="btn btn-primary"
                                            onclick="confirmarCambioEstado()">Confirmar</button>
                                    </form>
                                </div>
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
        var mensaje = estado === 'activo' ? '¿Está seguro que desea activar esta materia?' :
            '¿Está seguro que desea inactivar esta materia?';

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

    function openModal(modalId) {
        // Ocultar todos los modales abiertos
        $('.modal').modal('hide');

        // Mostrar el modal correspondiente
        if ($(modalId).length) {
            $(modalId).modal('show');
        } else {
            console.error('Modal no encontrado: ' + modalId);
        }
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