<?php
session_start();

// Establecer el tiempo de expiración de la sesión en segundos (por ejemplo, 45 minutos)
$tiempo_expiracion = 2700; // 2700 segundos = 45 minutos

// Verificar si la sesión ha expirado por inactividad
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $tiempo_expiracion) {
    // Si ha pasado más de 45 minutos, destruir la sesión y redirigir al login
    session_unset();  // Elimina todas las variables de sesión
    session_destroy();  // Destruye la sesión
    header("Location: ../../login.php");  // Redirige al login
    exit();  // Asegura que no se ejecute más código
}

// Actualizar el último acceso
$_SESSION['ultimo_acceso'] = time();  // Actualiza el tiempo de acceso

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
$rol = isset($_GET['rol']) ? $_GET['rol'] : '';

// Obtener roles de la base de datos
$sql_roles = "SELECT id_rol, nombre FROM rol WHERE estado = 'A'";
$resultado_roles = $conn->query($sql_roles);

if (!$resultado_roles) {
    die("Error al obtener roles: " . $conn->error);
}

// Construir la consulta SQL con filtros si existen
$sql = "
    SELECT 
        u.id_usuario, 
        u.cedula, 
        u.contraseña, 
        r.nombre AS nombre_rol, 
        u.estado, 
        u.usuario_ingreso, 
        u.fecha_ingreso 
    FROM usuario u
    INNER JOIN rol r ON u.id_rol = r.id_rol
    WHERE 1=1
";

$param_types = '';  // Para concatenar los tipos de las variables en la consulta
$param_values = [];  // Para almacenar los valores que se van a utilizar en la consulta

if (!empty($fecha)) {
    $sql .= " AND DATE(u.fecha_ingreso) = ?";
    $param_types .= 's';  // Tipo de parámetro string
    $param_values[] = $fecha;
}

if (!empty($estado)) {
    $estadoFiltro = $estado == 'activo' ? 'A' : 'I';
    $sql .= " AND u.estado = ?";
    $param_types .= 's';  // Tipo de parámetro string
    $param_values[] = $estadoFiltro;
}

if (!empty($rol)) {
    $sql .= " AND u.id_rol = ?";
    $param_types .= 'i';  // Tipo de parámetro integer
    $param_values[] = $rol;
}

// Preparar la consulta
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Vincular los parámetros a la consulta
if ($param_types !== '') {
    $stmt->bind_param($param_types, ...$param_values);
}

// Ejecutar la consulta
$stmt->execute();
$resultado = $stmt->get_result();

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
        font-family: 'Roboto', sans-serif;
        background-color: #f7f9fc;
        display: flex;
        flex-direction: column;
        height: 100vh;
        /* Asegura que el body ocupe toda la altura de la pantalla */
        margin: 0;
        /* Elimina márgenes predeterminados */
    }

    /* Espacio entre el contenido y el footer */
    .container-fluid {
        padding: 20px;
        flex: 1;
        /* Permite que el contenedor crezca y ocupe el espacio disponible */
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

    .user-name {
        font-weight: bold;
        color:  #6d6d6d;
        /* Color moderno y limpio */
    }

    .divider {
        border-left: 2px solid #ddd;
        /* Línea vertical suave */
        height: 20px;
    }

    .badge {
        font-size: 0.80rem;
        /* Tamaño ajustado del badge */
        background-color: #cd0200;
        /* ´rojo moderno para los roles */
    }

    .nav-link .bx-user-circle {
        font-size: 1.3rem;
        /* Tamaño del ícono */
        color:  #6d6d6d;
        /* Coincide con el nombre */
        position: relative;
        top: 3px;
        /* Baja ligeramente el ícono */
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
                            <!-- Botón para agregar usuario -->
                            <div class="col-auto">
                                <a href="http://localhost/sistema_notas/Crud/admin/usuario/registrar_usuario.php"
                                    class="btn btn-primary">Agregar Usuario</a>
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
                                <a href="http://localhost/sistema_notas/views/admin/reporte_usuarios.php"
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
                            <?php if (mysqli_num_rows($resultado) > 0): ?>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td><?php echo $fila['id_usuario']; ?></td>
                                <td><?php echo $fila['cedula']; ?></td>
                                <td>
				                    <!-- Mostrar la contraseña como asteriscos por defecto -->
				                    <span class="password" id="password-<?php echo $fila['id_usuario']; ?>">******</span>
				                    <!-- Mostrar la contraseña real solo para administradores -->
				                    <?php if ($_SESSION['rol'] == 'Superusuario'): ?>
				                        <input type="text" id="real-password-<?php echo $fila['id_usuario']; ?>" 
				                               value="<?php echo $fila['contraseña']; ?>" 
				                               style="display:none;" disabled>
				                        <!-- Ícono del ojo para cambiar la visibilidad -->
                                        <i class="bx bx-show" id="eye-open-<?php echo $fila['id_usuario']; ?>" onclick="togglePasswordVisibility(<?php echo $fila['id_usuario']; ?>)"></i>
                                        <i class="bx bx-hide" id="eye-closed-<?php echo $fila['id_usuario']; ?>" onclick="togglePasswordVisibility(<?php echo $fila['id_usuario']; ?>)" style="display:none;"></i>
				                    <?php endif; ?>
				                </td>
                                <td><?php echo $fila['nombre_rol']; ?></td>
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
                            <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No se encontraron registros disponibles en este
                                    momento.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 1 -->
    <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel1">Manual de Uso - Gestión de Usuarios (1/3)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Cómo agregar usuarios:</strong></p>
                    <p>En este paso, deberá ingresar los datos del usuario, como su nombre, correo electrónico y
                        contraseña.
                        Además, es importante que seleccione el rol correspondiente al usuario:</p>
                    <ul>
                        <li><strong>Administrador:</strong> Acceso completo a todas las funciones del sistema.</li>
                        <li><strong>Profesor:</strong> Acceso a las secciones relacionadas con gestión académica.</li>
                        <li><strong>Padre de Familia:</strong> Acceso limitado para consultar información de los
                            estudiantes.</li>
                    </ul>
                    <p>Si selecciona el rol de <strong>Padre de Familia</strong>, se habilitará un campo adicional para
                        indicar
                        el parentesco con el estudiante (por ejemplo, Padre, Madre u otro familiar).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary"
                        onclick="openModal('#modalInstrucciones2')">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2 -->
    <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel2" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel2">Manual de Uso - Gestión de Usuarios (2/3)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Cómo editar la información de los usuarios:</strong></p>
                    <p>En la sección de "Acciones", encontrará el botón <strong>Editar</strong>. Esta opción le permite
                        modificar
                        la información de un usuario en caso de que haya un error o necesite actualizarse.</p>
                    <p>Por ejemplo, puede corregir un error en el nombre, cambiar el correo electrónico asociado, o
                        actualizar
                        el rol asignado al usuario.</p>
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

    <!-- Modal 3 -->
    <div class="modal fade" id="modalInstrucciones3" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel3" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel3">Manual de Uso - Gestión de Usuarios (3/3)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Cómo inactivar o reactivar usuarios:</strong></p>
                    <p>En la sección de "Acciones", encontrará el botón <strong>Inactivar</strong>. Este botón le
                        permite desactivar
                        la cuenta de un usuario temporalmente, impidiendo su acceso al sistema.</p>
                    <p>Si necesita reactivar al usuario, simplemente presione nuevamente el botón, que cambiará a
                        <strong>Activar</strong>.
                    </p>
                    <p>Esta funcionalidad es útil para gestionar el acceso en casos donde un usuario ya no deba usar el
                        sistema
                        temporalmente, pero podría necesitarlo en el futuro.</p>
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
    </div>
    <!-- Fin Modal de Confirmación -->
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
    </footer>

</html>

<!-- Bootstrap core JavaScript -->
<link href="https://cdn.jsdelivr.net/npm/boxicons/css/boxicons.min.css" rel="stylesheet">
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

function togglePasswordVisibility(userId) {
        var passwordField = document.getElementById('real-password-' + userId);
        var passwordText = document.getElementById('password-' + userId);
        var eyeOpenIcon = document.getElementById('eye-open-' + userId);
        var eyeClosedIcon = document.getElementById('eye-closed-' + userId);

        // Alternar la visibilidad
        if (passwordField.style.display === 'none') {
            // Mostrar la contraseña
            passwordField.style.display = 'inline';
            passwordText.style.display = 'none';
            eyeOpenIcon.style.display = 'none';
            eyeClosedIcon.style.display = 'inline';
        } else {
            // Ocultar la contraseña
            passwordField.style.display = 'none';
            passwordText.style.display = 'inline';
            eyeOpenIcon.style.display = 'inline';
            eyeClosedIcon.style.display = 'none';
        }
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

