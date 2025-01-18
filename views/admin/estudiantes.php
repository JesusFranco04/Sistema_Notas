<?php
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta absoluta a tu archivo de configuración de base de datos

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Inicializar las variables de filtro
$cedula = isset($_GET['cedula']) ? trim($_GET['cedula']) : ''; // Filtro por cédula
$fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';   // Filtro por fecha de ingreso
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : ''; // Filtro por estado (Activo/Inactivo)

// Construir la consulta SQL para la tabla estudiante con filtros dinámicos
$sql = "SELECT e.id_estudiante, e.nombres, e.apellidos, e.cedula, e.telefono, e.correo_electronico, e.direccion, 
               e.fecha_nacimiento, e.genero, e.discapacidad, e.estado_calificacion, e.estado, n.nombre AS nivel, 
               p.nombre AS subnivel, s.nombre AS especialidad, es.nombre AS paralelo, j.nombre AS jornada, 
               h.año AS historial_academico, e.fecha_ingreso
        FROM estudiante e
        LEFT JOIN nivel n ON e.id_nivel = n.id_nivel
        LEFT JOIN subnivel s ON e.id_subnivel = s.id_subnivel
        LEFT JOIN especialidad es ON e.id_especialidad = es.id_especialidad
        LEFT JOIN paralelo p ON e.id_paralelo = p.id_paralelo
        LEFT JOIN jornada j ON e.id_jornada = j.id_jornada
        LEFT JOIN historial_academico h ON e.id_his_academico = h.id_his_academico
        WHERE 1=1"; // Condición inicial siempre verdadera para concatenar filtros

// Aplicar filtro por cédula si está definido
if (!empty($cedula)) {
    $sql .= " AND e.cedula LIKE '%" . $conn->real_escape_string($cedula) . "%'";
}

// Aplicar filtro por fecha de ingreso si está definido
if (!empty($fecha)) {
    $sql .= " AND DATE(e.fecha_ingreso) = '" . $conn->real_escape_string($fecha) . "'";
}

// Aplicar filtro por estado si está definido
if (!empty($estado)) {
    $estadoFiltro = ($estado === 'activo') ? 'A' : 'I'; // Convertir 'activo' o 'inactivo' a 'A' o 'I'
    $sql .= " AND e.estado = '" . $conn->real_escape_string($estadoFiltro) . "'";
}

// Ejecutar la consulta
$resultado = $conn->query($sql);

// Verificar errores en la consulta
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
    <title>Estudiantes | Sistema de Gestión UEBF</title>
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
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tabla de Estudiantes</h5>
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
                                <a href="http://localhost/sistema_notas/Crud/admin/estudiante/agregar_estudiante.php"
                                    class="btn btn-primary">Agregar Estudiante</a>
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
                                <a href="http://localhost/sistema_notas/views/admin/reporte_estudiantes.php"
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
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Cédula</th>
                                <th>Teléfono</th>
                                <th>Correo Electrónico</th>
                                <th>Dirección</th>
                                <th>Fecha de Nacimiento</th>
                                <th>Género</th>
                                <th>Discapacidad</th>
                                <th>Estado de la Calificación</th>
                                <th>Estado</th>
                                <th>Nivel</th>
                                <th>Paralelo</th>
                                <th>Especialidad</th>
                                <th>Subnivel</th>
                                <th>Jornada</th>
                                <th>Historial Académico</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado->num_rows > 0) { ?>
                            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $fila['id_estudiante']; ?></td>
                                <td><?php echo $fila['nombres']; ?></td>
                                <td><?php echo $fila['apellidos']; ?></td>
                                <td><?php echo $fila['cedula']; ?></td>
                                <td><?php echo $fila['telefono']; ?></td>
                                <td><?php echo $fila['correo_electronico']; ?></td>
                                <td><?php echo $fila['direccion']; ?></td>
                                <td><?php echo $fila['fecha_nacimiento']; ?></td>
                                <td><?php echo $fila['genero']; ?></td>
                                <td><?php echo $fila['discapacidad']; ?></td>
                                <td><?php echo $fila['estado_calificacion']; ?></td>
                                <td><?php echo $fila['estado']; ?></td>
                                <td><?php echo $fila['nivel']; ?></td>
                                <td><?php echo $fila['subnivel']; ?></td>
                                <td><?php echo $fila['especialidad']; ?></td>
                                <td><?php echo $fila['paralelo']; ?></td>
                                <td><?php echo $fila['jornada']; ?></td>
                                <td><?php echo $fila['historial_academico']; ?></td>
                                <td><?php echo $fila['fecha_ingreso']; ?></td>
                                <td>
                                    <a href="http://localhost/sistema_notas/Crud/admin/estudiante/editar_estudiantes.php?cedula=<?php echo $fila['cedula']; ?>"
                                        class="btn btn-warning btn-action">Editar</a>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php } else { ?>
                            <tr>
                                <td colspan="20" class="text-center">No se encontraron registros que coincidan con los
                                    criterios de búsqueda.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Instrucciones para la Página de Estudiantes -->
    <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel1">Instrucciones para la Gestión de Estudiantes
                        (1/3)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Cómo agregar estudiantes:</strong></p>
                    <p>Para agregar un estudiante, haga clic en el botón <strong>"Agregar Estudiante"</strong> que se
                        encuentra en la parte superior. Luego, deberá completar un formulario con los datos del
                        estudiante, como su nombre, apellido, fecha de nacimiento, dirección, entre otros.</p>
                    <p>Recuerde que algunos campos son obligatorios, como la cédula, el nombre y la fecha de nacimiento.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary"
                        onclick="openModal('#modalInstrucciones2')">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Explicación sobre cómo editar un estudiante -->
    <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel2" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel2">Instrucciones para la Gestión de Estudiantes
                        (2/3)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Cómo editar la información de un estudiante:</strong></p>
                    <p>En la tabla de estudiantes, encontrará la columna <strong>Acciones</strong> donde está el botón
                        <strong>"Editar"</strong>. Al hacer clic en este botón, podrá modificar los datos de un
                        estudiante, por ejemplo, actualizar su nombre, dirección o fecha de nacimiento.
                    </p>
                    <p>Recuerde que solo se pueden editar los campos que permiten cambios, como la dirección o el número
                        de teléfono.</p>
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
    <!-- Modal 3: Explicación sobre la descarga de reportes -->
    <div class="modal fade" id="modalInstrucciones3" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel3" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel3">Instrucciones para la Gestión de Estudiantes
                        (3/3)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Cómo descargar el reporte de estudiantes:</strong></p>
                    <p>En la página de gestión de estudiantes, encontrará un botón llamado <strong>"Descargar
                            Reporte"</strong> en la parte superior de la tabla, junto a otras acciones.</p>
                    <p>Al hacer clic en este botón, se generará un archivo PDF con el listado de los estudiantes
                        activos, incluyendo sus cursos y otra información relevante. Este reporte es útil para tener un
                        resumen claro y detallado de los estudiantes registrados en el sistema.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="openModal('#modalInstrucciones2')">Atrás</button>
                    <button type="button" class="btn btn-dark" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    </div>


    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
    </footer>

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
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [
                [0, "desc"]
            ]
        });

        // Filtro por cédula
        $('#searchCedula').on('keyup', function() {
            $('#dataTable').DataTable().column(3).search(this.value).draw();
        });

        // Filtro por fecha de creación
        $('#searchFecha').on('change', function() {
            $('#dataTable').DataTable().column(9).search(this.value).draw();
        });
    });

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