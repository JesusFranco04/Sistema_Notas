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

// Consulta SQL para obtener los cursos con nombres de las tablas relacionadas
$sql = "
SELECT
    c.id_curso,
    CONCAT(p.nombres, ' ', p.apellidos) AS nombre_profesor,
    m.nombre AS nombre_materia,
    n.nombre AS nombre_nivel,
    pa.nombre AS nombre_paralelo,
    s.nombre AS nombre_subnivel,
    e.nombre AS nombre_especialidad,
    j.nombre AS nombre_jornada,
    ha.año AS año_his_academico,
    c.estado
FROM
    curso c
    LEFT JOIN profesor p ON c.id_profesor = p.id_profesor
    LEFT JOIN materia m ON c.id_materia = m.id_materia
    LEFT JOIN nivel n ON c.id_nivel = n.id_nivel
    LEFT JOIN paralelo pa ON c.id_paralelo = pa.id_paralelo
    LEFT JOIN subnivel s ON c.id_subnivel = s.id_subnivel
    LEFT JOIN especialidad e ON c.id_especialidad = e.id_especialidad
    LEFT JOIN jornada j ON c.id_jornada = j.id_jornada
    LEFT JOIN historial_academico ha ON c.id_his_academico = ha.id_his_academico
WHERE 1=1"; // Base de la consulta

// Agregar filtros a la consulta
if (!empty($fecha)) {
    // Asegurarse de escapar el valor de fecha para evitar inyección SQL
    $fecha = mysqli_real_escape_string($conn, $fecha);
    $sql .= " AND DATE(c.fecha_ingreso) = '$fecha'";
}

if (!empty($estado)) {
    $estadoFiltro = $estado == 'activo' ? 'A' : 'I';
    // Asegurarse de escapar el valor del estado para evitar inyección SQL
    $estadoFiltro = mysqli_real_escape_string($conn, $estadoFiltro);
    $sql .= " AND c.estado = '$estadoFiltro'";
}

// Ejecutar la consulta
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
    <title>Cursos | Sistema de Gestión UEBF</title>
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
        font-family: 'Roboto', sans-serif;
        background-color: #f7f9fc;
        margin: 0;
        padding: 0;
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
                <h5 class="mb-0">Tabla de Cursos</h5>
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
                            <!-- Botón para agregar curso -->
                            <div class="col-auto">
                                <a href="http://localhost/sistema_notas/Crud/admin/curso/agregar_curso.php"
                                    class="btn btn-primary">Agregar
                                    Curso</a>
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
                                <a href="http://localhost/sistema_notas/views/admin/reporte_curso.php"
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
                                <th>Profesor</th>
                                <th>Materia</th>
                                <th>Nivel</th>
                                <th>Paralelo</th>
                                <th>Subnivel</th>
                                <th>Especialidad</th>
                                <th>Jornada</th>
                                <th>Historial Académico</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado->num_rows > 0): ?>
                            <!-- Si hay registros en la base de datos -->
                            <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['id_curso']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_profesor']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_materia']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_nivel']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_paralelo']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_subnivel']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_especialidad']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_jornada']); ?></td>
                                <td><?php echo htmlspecialchars($fila['año_his_academico']); ?></td>
                                <td><?php echo htmlspecialchars($fila['estado'] == 'A' ? 'Activo' : 'Inactivo'); ?></td>
                                <td>
                                    <a href="http://localhost/sistema_notas/Crud/admin/curso/editar_curso.php?id=<?php echo urlencode($fila['id_curso']); ?>"
                                        class="btn btn-sm btn-primary">Editar</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <!-- Si no hay registros disponibles -->
                                <td colspan="11" class="text-center">No se encontraron registros disponibles en este
                                    momento.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
                        aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalInstruccionesLabel1">Manual de Uso - Gestión de
                                        Cursos (1/3)</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Cómo agregar un nuevo curso:</strong></p>
                                    <p>En esta sección, puedes agregar un nuevo curso al sistema. Para hacerlo, sigue
                                        estos pasos:</p>
                                    <ol>
                                        <li>Haz clic en el botón <strong>"Agregar Curso"</strong>, que se encuentra en
                                            la parte superior izquierda, justo debajo de los filtros.</li>
                                        <li>Se abrirá un formulario donde deberás completar los campos obligatorios,
                                            como el nombre del profesor, materias, nivel, paralelo y otros detalles.
                                        </li>
                                        <li>Cuando hayas terminado de llenar el formulario, haz clic en el botón
                                            <strong>"Crear Curso"</strong> para guardar el curso en el sistema.
                                        </li>
                                    </ol>
                                    <p>Recuerda que todos los campos marcados como obligatorios deben ser llenados
                                        correctamente.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary"
                                        onclick="openModal('#modalInstrucciones2')">Siguiente</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog"
                        aria-labelledby="modalInstruccionesLabel2" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalInstruccionesLabel2">Manual de Uso - Gestión de
                                        Cursos (2/3)</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Cómo buscar y filtrar cursos:</strong></p>
                                    <p>En la parte superior de la tabla, encontrarás dos herramientas de filtro:</p>
                                    <ul>
                                        <li><strong>Fecha de Creación:</strong> Utiliza este filtro para mostrar cursos
                                            creados en una fecha específica. Solo selecciona una fecha en el calendario.
                                        </li>
                                        <li><strong>Estado:</strong> Aquí puedes elegir entre "Todos", "Activos" o
                                            "Inactivos" para mostrar cursos según su estado actual.</li>
                                    </ul>
                                    <p>Después de seleccionar los filtros que desees, haz clic en el botón
                                        <strong>"Filtrar"</strong>, ubicado a la derecha, para ver los resultados en la
                                        tabla.
                                    </p>
                                    <p>Si no hay registros que coincidan con los filtros seleccionados, la tabla
                                        mostrará un mensaje indicando que no hay resultados disponibles.</p>
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


                    <div class="modal fade" id="modalInstrucciones3" tabindex="-1" role="dialog"
                        aria-labelledby="modalInstruccionesLabel3" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalInstruccionesLabel3">Manual de Uso - Gestión de
                                        Cursos (3/3)</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Cómo editar o cambiar el estado de un curso (activar/inactivar):</strong>
                                    </p>
                                    <ol>
                                        <li><strong>Ve a la tabla de cursos</strong> y haz clic en
                                            <strong>Editar</strong> en la columna "Acciones" del curso que deseas
                                            modificar.
                                        </li>
                                        <li><strong>En el formulario de edición</strong>, busca el campo llamado
                                            <strong>"Estado"</strong>.
                                        </li>
                                        <li><strong>Cambia el estado:</strong> selecciona "Activo" para activarlo o
                                            "Inactivo" para desactivarlo.</li>
                                        <li><strong>Haz clic en el botón rojo</strong> que dice
                                            <strong>"Actualizar"</strong> para guardar los cambios en el sistema.
                                        </li>
                                    </ol>
                                    <p class="text-danger"><strong>Importante:</strong> No se elimina el curso, solo se
                                        cambia su estado entre activo e inactivo.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        onclick="openModal('#modalInstrucciones2')">Atrás</button>
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
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

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- SB Admin 2 JS-->
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