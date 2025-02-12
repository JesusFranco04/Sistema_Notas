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

include '../../Crud/config.php';
date_default_timezone_set('America/Guayaquil');

// Verificar si el usuario ha iniciado sesión y si su rol es "Administrador" o "Superusuario"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Función para obtener los períodos académicos
function obtenerPeriodos($conn) {
    $sql = "SELECT id_periodo, nombre, estado FROM periodo_academico";
    return $conn->query($sql);
}

// Función para verificar el éxito de una consulta
function verificarConsulta($resultado, $conn, $mensaje) {
    if (!$resultado) {
        die($mensaje . " " . $conn->error);
    }
}

// Obtener los períodos académicos
$result_periodos = obtenerPeriodos($conn);
verificarConsulta($result_periodos, $conn, "Error en la consulta de períodos");

// Obtener los años lectivos activos
$sql_years = "SELECT id_his_academico, año FROM historial_academico WHERE estado = 'A'";
$result_years = $conn->query($sql_years);
verificarConsulta($result_years, $conn, "Error en la consulta de años lectivos");
?>


<!DOCTYPE html>
<html>

<head>
    <title>Administración de Ciclos Académicos | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        color: #4b5563;
    }

    .container {
        margin-top: 30px;
    }

    .section {
        background: #fff;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h1,
    h2,
    h3 {
        color: #d32f2f;
        /* Rojo intenso */
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #E62433;
        /* Rojo claro para encabezados */
        color: white;
        /* Color de texto rojo oscuro */
        text-align: center;
        /* Centramos el texto en el encabezado */
    }

    td {
        text-align: center;
        /* Centramos el texto en las celdas */
    }

    .btn-primary {
        background-color: #E62433;
        /* Rojo intenso */
        border-color: #d32f2f;
        margin-top: 10px;
    }

    .btn-primary:hover {
        background-color: #E62433;
        /* Rojo más oscuro para hover */
        border-color: #c62828;
    }

    .alert {
        margin-top: 20px;
        border-radius: 5px;
    }

    .alert-success {
        background-color: #d4edda;
        /* Verde claro para éxito */
        color: #155724;
        /* Color de texto verde oscuro */
    }

    .alert-danger {
        background-color: #f8d7da;
        /* Rojo claro para errores */
        color: #721c24;
        /* Color de texto rojo oscuro */
    }

    .table-container {
        max-height: 400px;
        overflow: auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        /* Asegura que el fondo de la tabla sea blanco */
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-control {
        border: 1px solid #d32f2f;
    }

    .form-control:focus {
        border-color: #c62828;
        box-shadow: 0 0 0 0.2rem rgba(211, 47, 47, 0.25);
    }

    .btn-danger {
        background-color: #E62433;
        /* Rojo oscuro */
        border-color: #c62828;
    }

    .btn-danger:hover {
        background-color: #b71c1c;
        /* Rojo más oscuro para hover */
        border-color: #b71c1c;
    }

    /* Estilo para alinear los botones a la derecha */
    .text-right {
        text-align: right;
    }

    .btn-container {
        margin-top: 15px;
    }

    /* Estilo para centrar los botones en su columna respectiva */
    .btn-center {
        display: flex;
        justify-content: center;
    }

    /* Estilo para la tabla */
    table {
        width: 100%;
        border-collapse: separate;
        /* Permitir bordes separados para redondeo */
        border-spacing: 0;
        /* Eliminar el espaciado entre celdas */
        border-radius: 10px;
        /* Bordes redondeados en la tabla */
        overflow: hidden;
        /* Asegurar que los bordes redondeados se apliquen */
    }

    /* Estilo para el encabezado de la tabla */
    th {
        background-color: #E62433;
        /* Color de fondo del encabezado */
        color: white;
        /* Color del texto del encabezado */
        text-align: center;
        /* Alinear texto al centro */
        padding: 12px;
        /* Espaciado interno en celdas de encabezado */
        border: 1px solid #dee2e6;
        /* Borde de las celdas del encabezado */
    }

    /* Estilos generales para los modales */
    .modal-content {
        border-radius: 8px;
        border: 1px solid #ddd;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        font-family: Arial, sans-serif;
    }

    .modal-body {
        max-height: 400px;  /* Ajusta la altura según lo que necesites */
        overflow-y: auto;   /* Permite el scroll vertical */
    }

    .modal-header {
        background-color: #DE112D;
        padding: 15px;
        color: white;
        border-bottom: 2px solid #B50D22;
    }

    .modal-title {
        font-weight: bold;
        font-size: 1.25rem;
    }

    .modal-header .close {
        font-size: 1.5rem;
        color: white;
        background: none;
        border: none;
        opacity: 0.8;
        outline: none;
        transition: opacity 0.2s;
    }

    .modal-header .close:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    .modal-footer .btn {
        border: none;
        transition: background-color 0.3s ease;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
    }

    .modal-footer .btn-secondary {
        background-color: #0e2643;
    }

    .modal-footer .btn-secondary:hover {
        background-color: #0b1e36;
    }

    .modal-footer .btn-success {
        background-color: #0d5316;
    }

    .modal-footer .btn-success:hover {
        background-color: #0a4312;
    }

    .modal-footer .btn-dark {
        background-color: #3d454d;
    }

    .modal-footer .btn-dark:hover {
        background-color: #31373e;
    }

    .alert {
        margin-top: 20px;
        padding: 15px 20px;
        /* Más espacio alrededor del mensaje */
        font-size: 16px;
        font-weight: bold;
        border-radius: 8px;
        /* Bordes más redondeados */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        /* Sombra sutil */
        transition: transform 0.3s ease, opacity 0.3s ease;
        /* Transición suave */
    }

    .alert-success {
        background-color: #d4edda;
        /* Verde claro para éxito */
        color: #155724;
        /* Color de texto verde oscuro */
        border-left: 5px solid #28a745;
        /* Línea izquierda verde */
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 5px solid #dc3545;
        /* Línea izquierda roja */
    }

    .alert-warning {
        background-color: #fff3cd;
        /* Amarillo claro para advertencias */
        color: #856404;
        /* Color de texto amarillo oscuro */
        border-left: 5px solid #ffc107;
        /* Línea izquierda amarilla */
    }

    /* Efecto al pasar el ratón */
    .alert:hover {
        transform: translateY(-5px);
        /* Le da un pequeño levantamiento */
        opacity: 0.9;
        /* Hace que se vea un poco más sutil al pasar el ratón */
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
    <div class="container">

        <!-- Habilitación/Deshabilitación de Períodos -->
        <div class="section">
            <h2>Gestión de Períodos Académicos</h2>
            <?php
            function mostrarMensaje() {
                // Verificar si existen los parámetros 'mensaje' y 'tipo' en la URL
                if (isset($_GET['mensaje']) && isset($_GET['tipo'])) {
                    $mensaje = htmlspecialchars($_GET['mensaje']);
                    $mensaje_tipo = htmlspecialchars($_GET['tipo']);

                    // Asegurarse de que el mensaje y el tipo no están vacíos
                    if (!empty($mensaje) && !empty($mensaje_tipo)) {
                        // Determinar la clase de la alerta según el tipo
                        $alertClass = ($mensaje_tipo === 'error') ? 'alert-danger' : 'alert-success';

                        // Mostrar el mensaje con la clase correspondiente
                        echo "<div class='alert $alertClass' role='alert'>$mensaje</div>";
                    }
                }
            }
            ?>

            <form id="form_periodos">
                <table>
                    <tr>
                        <th>ID Período</th>
                        <th>Nombre del Período</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                    <?php if ($result_periodos->num_rows == 0) { ?>
                    <tr>
                        <td colspan="4">
                            <div class="alert alert-warning">
                                <strong>No hay periodos académicos registrados.</strong> Debes agregar uno para poder
                                visualizarlos y gestionarlos correctamente.
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if ($result_periodos->num_rows > 0) { ?>
                    <?php while ($row = $result_periodos->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_periodo']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo $row['estado'] == '1' ? 'Activo' : 'Inactivo'; ?></td>
                        <td>
                            <?php if ($row['id_periodo'] != 3) { ?>
                            <input type="radio" name="periodo"
                                value="<?php echo htmlspecialchars($row['id_periodo']); ?>"
                                <?php echo $row['estado'] == '1' ? 'checked' : ''; ?>> Activar
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                </table>
                <div class="text-right" style="margin-top: 10px;">
                    <div class="d-inline-block mr-2">
                        <!-- Botón Manual de Uso -->
                        <button type="button" data-toggle="modal" data-target="#modalInstrucciones1"
                            class="btn btn-secondary">
                            <i class='bx bx-book'></i> Manual de Uso
                        </button>
                    </div>
                    <div class="d-inline-block">
                        <!-- Botón Actualizar Período -->
                        <button type="button" class="btn btn-primary" onclick="actualizarPeriodos()">Actualizar
                            Período</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Programar Cierre de Período -->
        <div class="section mt-4">
            <h3>Programación de Cierre de Año Escolar</h3>
            <?php
            // Llamada a la función para mostrar el mensaje de alerta
            mostrarMensaje();
            ?>

            <form id="form_cierre" method="post"
                action="http://localhost/sistema_notas/Crud/admin/año_lectivo/programar_cierre.php"
                onsubmit="return validateDate()">
                <div class="form-group">
                    <label for="id_periodo">Año Lectivo:</label>
                    <select class="form-control" id="id_periodo" name="id_periodo" required>
                        <option value="">Selecciona Año Lectivo</option>
                        <?php
                        if ($result_years) {
                            while ($row = $result_years->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row['id_his_academico']) . '">' . htmlspecialchars($row['año']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha_cierre">Fecha y Hora de Cierre Programada:</label>
                    <input type="datetime-local" class="form-control" id="fecha_cierre" name="fecha_cierre" required>
                </div>
                <div class="text-right btn-container">
                    <button type="submit" class="btn btn-primary">Programar Cierre</button>
                </div>
            </form>
        </div>

        <!-- Mostrar lista de períodos -->
        <div class="section mt-4">
            <h3>Lista de Años Lectivos</h3>
            <div class="table-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID Año Lectivo</th>
                            <th>Año</th>
                            <th>Estado</th>
                            <th>Fecha de Cierre Programada</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                $sql = "SELECT id_his_academico, año, estado, fecha_cierre_programada FROM historial_academico";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id_his_academico']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['año']) . '</td>';
                        echo '<td>' . ($row['estado'] == 'A' ? 'Activo' : 'Inactivo') . '</td>';
                        echo '<td>' . ($row['fecha_cierre_programada'] ? htmlspecialchars($row['fecha_cierre_programada']) : 'No Programada') . '</td>';
                        echo '<td class="btn-center">';
                        if ($row['fecha_cierre_programada'] !== null) {
                            echo '<button class="btn btn-danger" onclick="cerrarAno(' . htmlspecialchars($row['id_his_academico']) . ', this)" disabled>Cerrado</button>';
                        } else {
                            echo '<button class="btn btn-danger" onclick="cerrarAno(' . htmlspecialchars($row['id_his_academico']) . ', this)">Cerrar Año</button>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">No hay datos disponibles.</td></tr>';
                }
                ?>
                    </tbody>
                </table>

                <?php if ($result->num_rows == 0) { ?>
                <div class="alert alert-warning mt-3">
                    <strong>No hay años lectivos que mostrar.</strong> Añada un año académico nuevo.
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
            aria-labelledby="modalInstrucciones1Label" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInstrucciones1Label">Manual de Uso - Gestión de Períodos
                            Académicos (1/1)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h6><strong>¿Qué puedo hacer aquí?</strong></h6>
                        <p>En esta sección podrás gestionar los períodos académicos, activarlos y desactivarlos según
                            corresponda. Además, puedes programar el cierre del año escolar.</p>

                        <h6><strong>¿Cómo puedo activar un período?</strong></h6>
                        <p>En la tabla de la sección <strong>'Gestión de Períodos Académicos'</strong>, encontrarás una
                            lista de
                            períodos. Si un período está 'Inactivo', puedes seleccionarlo marcando el botón de radio
                            junto
                            a 'Activar' en la columna <strong>'Acción'</strong>.</p>

                        <h6><strong>¿Qué sucede al activar un período?</strong></h6>
                        <p>Al activar un período, este se convierte en el período activo. Ten en cuenta que solo puede
                            haber un período activo a la vez.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success font-weight-bold"
                            onclick="openModal('#modalInstrucciones2')">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog"
            aria-labelledby="modalInstrucciones2Label" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInstrucciones2Label">Manual de Uso - Cierre de Año Escolar
                            (1/2)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h6><strong>¿Cómo programo el cierre de un año escolar?</strong></h6>
                        <p>En <strong>'Programación de Cierre'</strong>, selecciona el año en <strong>'Año
                                Lectivo'</strong> y elige la fecha y hora. <strong>No debe superar 3 años a partir
                                de la fecha actual.</strong></p>

                        <h6><strong>¿Qué sucede cuando programo el cierre?</strong></h6>
                        <p>El sistema lo ejecutará automáticamente en la fecha y hora elegidas y actualizará el estado
                            del año.</p>

                        <h6><strong>¿Cómo cierro el año escolar manualmente?</strong></h6>
                        <p>En la lista de años, haz clic en <strong>"Cerrar Año"</strong> si no hay una fecha
                            programada. El sistema lo cerrará al instante.</p>

                        <h6><strong>¿Qué pasa si ya está cerrado o programado?</strong></h6>
                        <p>El botón <strong>"Cerrar Año"</strong> aparecerá como <strong>"Cerrado"</strong> y estará
                            desactivado.</p>

                        <h6><strong>Importante:</strong></h6>
                        <p>Una vez cerrado, el año no se puede reabrir ni modificar. Asegúrate de elegir correctamente
                            la fecha y hora del cierre, ya que es irreversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary font-weight-bold"
                            onclick="openModal('#modalInstrucciones1')">Atrás</button>
                        <button type="button" class="btn btn-dark font-weight-bold"
                            onclick="openModal('#modalInstrucciones3')">Cerrar</button>
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

    <script>
    function validateDate() {
        const fechaCierre = new Date(document.getElementById('fecha_cierre').value);
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() + 3);

        if (fechaCierre > maxDate) {
            alert('La fecha de cierre no puede ser más allá de 3 años en el futuro.');
            return false;
        }
        return true;
    }

    function cerrarAno(idPeriodo, boton) {
        if (confirm('¿Estás seguro de que deseas cerrar este año lectivo?')) {
            const formData = new FormData();
            formData.append('id_periodo', idPeriodo);

            fetch('http://localhost/sistema_notas/Crud/admin/año_lectivo/cerrar_ano.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Redirigir al usuario con los parámetros mensaje y tipo
                    if (response.ok) {
                        // Esto es para simular la redirección
                        window.location.href =
                            'http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Año cerrado correctamente.&tipo=success';
                    } else {
                        window.location.href =
                            'http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Error al cerrar el año. Por favor, inténtelo de nuevo.&tipo=error';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href =
                        'http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Error al intentar cerrar el año.&tipo=error';
                });
        }
    }

    function actualizarPeriodos() {
        var formData = new FormData(document.getElementById('form_periodos'));

        fetch('http://localhost/sistema_notas/Crud/admin/año_lectivo/actualizar_periodos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    // Redirigir con mensaje de éxito
                    window.location.href =
                        'http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Período activado correctamente.&tipo=success';
                } else {
                    // Redirigir con mensaje de error
                    window.location.href =
                        'http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Error al activar el período. Por favor, inténtelo de nuevo.&tipo=error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.href =
                    'http://localhost/sistema_notas/views/admin/gestionar_academico.php?mensaje=Error al intentar actualizar los períodos.&tipo=error';
            });
    }
    </script>
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <script>
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