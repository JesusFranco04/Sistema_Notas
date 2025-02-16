<?php 
session_start();

// Establecer el tiempo de expiración de la sesión en segundos (45 minutos)
$tiempo_expiracion = 2700;

// Verificar si la sesión ha expirado por inactividad
if (!empty($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $tiempo_expiracion) {
    // Destruir la sesión y redirigir al login
    session_unset();
    session_destroy();
    header("Location: ../../login.php");
    exit();
}

// Actualizar el último acceso
$_SESSION['ultimo_acceso'] = time();

// Incluir el archivo de conexión a la base de datos
require_once("../../config.php");

// Verificar si el usuario ha iniciado sesión y tiene el rol adecuado
if (empty($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit();
}

// Inicializar variables para mensajes de respuesta
$mensaje = '';
$mensaje_tipo = '';

// Procesar la vinculación de estudiantes y padres
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_estudiante = $_POST['id_estudiante'] ?? null;
    $id_padre = $_POST['id_padre'] ?? null;

    if ($id_estudiante && $id_padre) {
        // Verificar coincidencia de apellidos entre estudiante y padre
        $query_estudiante = "SELECT apellidos FROM estudiante WHERE id_estudiante = ?";
        $stmt_estudiante = $conn->prepare($query_estudiante);
        $stmt_estudiante->bind_param("i", $id_estudiante);
        $stmt_estudiante->execute();
        $apellido_estudiante = $stmt_estudiante->get_result()->fetch_assoc()['apellidos'];

        $query_padre = "SELECT apellidos FROM padre WHERE id_padre = ?";
        $stmt_padre = $conn->prepare($query_padre);
        $stmt_padre->bind_param("i", $id_padre);
        $stmt_padre->execute();
        $apellido_padre = $stmt_padre->get_result()->fetch_assoc()['apellidos'];

        // Separar apellidos en partes para comparar coincidencias
        $partes_apellido_estudiante = explode(' ', $apellido_estudiante);
        $partes_apellido_padre = explode(' ', $apellido_padre);

        $coinciden = false;
        foreach ($partes_apellido_padre as $parte_padre) {
            if (in_array($parte_padre, $partes_apellido_estudiante)) {
                $coinciden = true;
                break;
            }
        }

        // Si los apellidos no coinciden, mostrar mensaje de error
        if (!$coinciden) {
            $mensaje = "Los apellidos del estudiante y el padre no coinciden.";
            $mensaje_tipo = 'error';
        } else {
            // Verificar si la relación ya existe en la base de datos
            $query_verificar = "SELECT * FROM padre_x_estudiante WHERE id_estudiante = ? AND id_padre = ?";
            $stmt_verificar = $conn->prepare($query_verificar);
            $stmt_verificar->bind_param("ii", $id_estudiante, $id_padre);
            $stmt_verificar->execute();
            $result_verificar = $stmt_verificar->get_result();

            if ($result_verificar->num_rows > 0) {
                $mensaje = "Esta relación ya existe.";
                $mensaje_tipo = 'error';
            } else {
                // Insertar la relación
                $query_insertar = "INSERT INTO padre_x_estudiante (id_estudiante, id_padre) VALUES (?, ?)";
                $stmt_insertar = $conn->prepare($query_insertar);
                $stmt_insertar->bind_param("ii", $id_estudiante, $id_padre);
                if ($stmt_insertar->execute()) {
                    $mensaje = "Relación creada exitosamente.";
                    $mensaje_tipo = 'exito';
                } else {
                    $mensaje = "Error al crear la relación: " . $stmt_insertar->error;
                    $mensaje_tipo = 'error';
                }
            }
        }
    } else {
        $mensaje = "Debes seleccionar un estudiante y un padre.";
        $mensaje_tipo = 'error';
    }
}

// Obtener la lista de niveles académicos activos
$query_niveles = "SELECT id_nivel, nombre FROM nivel WHERE estado = 'A' ORDER BY nombre";
$result_niveles = $conn->query($query_niveles);

// Orden personalizado para los niveles académicos
$orden_niveles = [
    'octavo' => 1,
    'noveno' => 2,
    'décimo' => 3,
    'primer' => 4,
    'segundo' => 5,
    'tercer' => 6
];

$niveles = [];
while ($row = $result_niveles->fetch_assoc()) {
    $nombre = trim(mb_strtolower($row['nombre']));
    $orden = 999;
    
    foreach ($orden_niveles as $clave => $posicion) {
        if (preg_match("/\b$clave\b/", $nombre)) { 
            $orden = $posicion;
            break;
        }
    }

    $niveles[] = [
        'id_nivel' => $row['id_nivel'],
        'nombre' => $row['nombre'],
        'orden' => $orden
    ];
}

// Ordenar los niveles según el orden definido
usort($niveles, fn($a, $b) => $a['orden'] <=> $b['orden']);

// Obtener la lista de paralelos activos
$query_paralelos = "SELECT id_paralelo, nombre FROM paralelo WHERE estado = 'A' ORDER BY nombre";
$result_paralelos = $conn->query($query_paralelos);

$paralelos = [];
while ($row = $result_paralelos->fetch_assoc()) {
    $row['nombre'] = trim($row['nombre']);
    $paralelos[] = $row;
}

// Ordenar los paralelos alfabéticamente
usort($paralelos, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));

// Obtener la lista de jornadas académicas activas
$query_jornadas = "SELECT id_jornada, nombre 
                   FROM jornada 
                   WHERE estado = 'A' 
                   ORDER BY fecha_ingreso ASC";
$result_jornadas = $conn->query($query_jornadas);

// Obtener la lista de historiales académicos activos
$query_historiales = "SELECT id_his_academico, año 
                      FROM historial_academico 
                      WHERE estado = 'A' 
                      AND fecha_cierre_programada IS NULL 
                      ORDER BY fecha_ingreso DESC 
                      LIMIT 1";
$result_historiales = $conn->query($query_historiales);

$filters = [];
$filter_query = '';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['nivel']) && $_GET['nivel'] != '') {
        $filters['e.id_nivel'] = $_GET['nivel'];
    }
    if (isset($_GET['paralelo']) && $_GET['paralelo'] != '') {
        $filters['e.id_paralelo'] = $_GET['paralelo'];
    }
    if (isset($_GET['jornada']) && $_GET['jornada'] != '') {
        $filters['e.id_jornada'] = $_GET['jornada'];
    }
if (isset($_GET['historial_academico']) && $_GET['historial_academico'] != '') {
        $filters['e.id_his_academico'] = $_GET['historial_academico'];
    }

    // Si hay filtros aplicados, generamos la cláusula WHERE
    if (!empty($filters)) {
        $filter_clauses = [];
        foreach ($filters as $key => $value) {
            $filter_clauses[] = "$key = '$value'";
        }
        $filter_query = "WHERE " . implode(' AND ', $filter_clauses);
    }
}

// Obtener la lista de estudiantes con padres según los filtros aplicados
$query_estudiantes = "SELECT e.id_estudiante, e.nombres, e.apellidos, e.cedula, p.nombres AS nombre_padre, p.apellidos AS apellido_padre, p.cedula AS cedula_padre
                      FROM estudiante e
                      LEFT JOIN padre_x_estudiante pxe ON e.id_estudiante = pxe.id_estudiante
                      LEFT JOIN padre p ON pxe.id_padre = p.id_padre";

if (!empty($filter_query)) {
    $query_estudiantes .= " $filter_query"; // Agregar filtros si existen
}

$query_estudiantes .= " ORDER BY e.apellidos"; // Ordenar siempre
$result_estudiantes = $conn->query($query_estudiantes);

// Nueva consulta para estudiantes sin padre asociado o con solo un padre asociado
$query_estudiantes_sin_padre = "SELECT e.id_estudiante, e.nombres, e.apellidos, e.cedula, e.id_nivel, e.id_paralelo, e.id_jornada, e.id_his_academico
                                 FROM estudiante e
                                 LEFT JOIN padre_x_estudiante pxe ON e.id_estudiante = pxe.id_estudiante
                                 GROUP BY e.id_estudiante, e.nombres, e.apellidos, e.cedula, e.id_nivel, e.id_paralelo, e.id_jornada, e.id_his_academico
                                 HAVING COUNT(pxe.id_padre) < 2";

if (!empty($filter_query)) {
    $query_estudiantes_sin_padre .= " AND " . str_replace("WHERE ", "", $filter_query); // Filtros más condición
}

$query_estudiantes_sin_padre .= " ORDER BY e.apellidos"; // Ordenar siempre
$result_estudiantes_sin_padre = $conn->query($query_estudiantes_sin_padre);

// Obtener la lista de padres
$query_padres = "SELECT p.id_padre, p.nombres, p.apellidos, COALESCE(px.total_vinculos, 0) AS total_vinculos,
                 SUBSTRING_INDEX(p.apellidos, ' ', 1) AS primer_apellido
FROM padre p
INNER JOIN usuario u ON p.id_usuario = u.id_usuario
LEFT JOIN (
    SELECT id_padre, COUNT(*) AS total_vinculos
    FROM padre_x_estudiante
    GROUP BY id_padre
) px ON p.id_padre = px.id_padre
WHERE u.estado = 'A' -- El usuario relacionado está activo
ORDER BY px.total_vinculos ASC, primer_apellido ASC, p.apellidos ASC";
$result_padres = $conn->query($query_padres);

// Procesar el formulario de enlace
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_estudiante = $_POST['id_estudiante'];
    $id_padre = $_POST['id_padre'];
    $forzar_registro = isset($_POST['forzar_registro']); // Verifica si el checkbox está marcado

    // Verificar si ya existe una relación
    $query_verificar = "SELECT * FROM padre_x_estudiante WHERE id_estudiante = ? AND id_padre = ?";
    $stmt_verificar = $conn->prepare($query_verificar);
    $stmt_verificar->bind_param("ii", $id_estudiante, $id_padre);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();

    if ($result_verificar->num_rows > 0) {
        $mensaje = "Esta relación ya existe.";
        $mensaje_tipo = 'error';
    } else {
        // Obtener apellidos del estudiante
        $query_estudiante = "SELECT apellidos FROM estudiante WHERE id_estudiante = ?";
        $stmt_estudiante = $conn->prepare($query_estudiante);
        $stmt_estudiante->bind_param("i", $id_estudiante);
        $stmt_estudiante->execute();
        $apellido_estudiante = $stmt_estudiante->get_result()->fetch_assoc()['apellidos'];

        // Obtener apellidos del padre
        $query_padre = "SELECT apellidos FROM padre WHERE id_padre = ?";
        $stmt_padre = $conn->prepare($query_padre);
        $stmt_padre->bind_param("i", $id_padre);
        $stmt_padre->execute();
        $apellido_padre = $stmt_padre->get_result()->fetch_assoc()['apellidos'];

        // Dividir los apellidos en partes
        $partes_apellido_estudiante = explode(' ', $apellido_estudiante);
        $partes_apellido_padre = explode(' ', $apellido_padre);

        // Verificar si al menos una parte del apellido del padre coincide con alguna parte del apellido del estudiante
        $coinciden = false;
        foreach ($partes_apellido_padre as $parte_padre) {
            if (in_array($parte_padre, $partes_apellido_estudiante)) {
                $coinciden = true;
                break;
            }
        }
        
        // Verificar si los apellidos coinciden, a menos que el checkbox haya sido marcado
        if (!$forzar_registro && !$coinciden) {
            $mensaje = "Los apellidos del estudiante y el padre no coinciden.";
            $mensaje_tipo = 'error';
        } else {
            // Insertar la relación
            $query_insertar = "INSERT INTO padre_x_estudiante (id_estudiante, id_padre) VALUES (?, ?)";
            $stmt_insertar = $conn->prepare($query_insertar);
            $stmt_insertar->bind_param("ii", $id_estudiante, $id_padre);
            if ($stmt_insertar->execute()) {
                $mensaje = "Relación creada exitosamente.";
                $mensaje_tipo = 'exito';
            } else {
                $mensaje = "Error al crear la relación: " . $stmt_insertar->error;
                $mensaje_tipo = 'error';
            }
        }
    }
}

// Obtener las relaciones existentes
$query_relaciones = "SELECT pxe.id_padre, pxe.id_estudiante, e.nombres AS nombre_estudiante, e.apellidos AS apellido_estudiante, e.cedula AS cedula_estudiante, p.nombres AS nombre_padre, p.apellidos AS apellido_padre, p.cedula AS cedula_padre
                     FROM padre_x_estudiante pxe
                     JOIN estudiante e ON pxe.id_estudiante = e.id_estudiante
                     JOIN padre p ON pxe.id_padre = p.id_padre
                     ORDER BY e.apellidos, p.apellidos";
$result_relaciones = $conn->query($query_relaciones);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinculación Familiar Académica | Sistema de Gestión UEBF</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
    /* Estilo general del cuerpo */
    body {
        font-family: 'Roboto', sans-serif;
        background-color: white;
        margin: 0;
        padding: 0;
    }

    .container {
        background: #ffffff;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        /* Espacio entre el contenedor y el footer */
    }

    .btn-custom {
        background-color: #E62433;
        color: #ffffff;
    }

    .btn-custom:hover {
        background-color: #f83b4a;
        color: #ffffff;
    }

    .form-label {
        font-weight: bold;
    }

    .icon {
        font-size: 1.5rem;
        vertical-align: middle;
        margin-right: 0.5rem;
    }

    .table-container {
        overflow: auto;
        max-height: 400px;
    }

    .table th,
    .table td {
        text-align: center;
    }

    .table-striped tbody tr:nth-child(odd) {
        background-color: #f2f2f2;
    }

    .alert {
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

    .modal-body {
        max-height: 400px;
        /* Ajusta la altura según lo que necesites */
        overflow-y: auto;
        /* Permite el scroll vertical */
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        /* Espaciado uniforme debajo de las etiquetas */
    }

    .form-select {
        height: auto;
        /* Ajusta la altura si hay diferencias entre los selectores */
    }

    .user-name {
        font-weight: bold;
        color: #6d6d6d;
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
        color: #6d6d6d;
        /* Coincide con el nombre */
        position: relative;
        top: 3px;
        /* Baja ligeramente el ícono */
    }

    /* Estilo general para el contenedor */
    .col-md-3 {
        margin-bottom: 20px;
    }

    /* Estilo del label */
    .form-label {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }

    /* Estilo del select */
    .form-select {
        width: 100%;
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 1rem;
        background-color: #f9f9f9;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Efecto al pasar el cursor */
    .form-select:hover {
        border-color: #c1f1cb;
        /* Verde claro */
        background-color: #d4edda;
        /* Verde suave */
    }

    /* Efecto de foco */
    .form-select:focus {
        border-color: #218838;
        /* Verde más oscuro */
        background-color: #fff;
        box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
        /* Verde brillante */
        outline: none;
    }

    /* Estilo para las opciones seleccionadas */
    .form-select option {
        font-size: 1rem;
        padding: 10px;
    }

    /* Ajustes de accesibilidad para cuando el select esté deshabilitado */
    .form-select:disabled {
        background-color: #f0f0f0;
        border-color: #ddd;
        cursor: not-allowed;
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

    .card {
        margin-bottom: 2rem;
    }

    .card-header {
        background-color: #E62433;
        /* Fondo rojo oscuro para el encabezado de la tarjeta */
        color: #fff;
        /* Color del texto del encabezado */
        border-bottom: 1px solid #fff;
        /* Línea divisoria blanca */
        border-radius: 8px 8px 0 0;
        /* Redondeo solo en las esquinas superiores */
    }
    </style>
</head>

<body>
    <?php include_once('C:/xampp/htdocs/Sistema_Notas/views/admin/navbar_admin.php'); ?>

    <div class="container-fluid">
        <div class="container">
            <div class="card-header">
                <h5 class="mb-0">Vinculación Familiar Académica</h5>
            </div>
            <div class="card-body">
                <h2 class="mb-4">
                    <i class='bx bx-link icon'></i> Enlazar Estudiantes con Padres
                </h2>

                <form class="mb-4" method="GET" action="">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="nivel" class="form-label mb-2">Nivel:</label>
                            <select id="nivel" name="nivel" class="form-select">
                                <option value="">Selecciona un nivel</option>
                                <?php foreach ($niveles as $row): ?>
                                <option value="<?php echo $row['id_nivel']; ?>"
                                    <?php echo (isset($_GET['nivel']) && $_GET['nivel'] == $row['id_nivel']) ? 'selected' : ''; ?>>
                                    <?php echo $row['nombre']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="paralelo" class="form-label mb-2">Paralelo:</label>
                            <select id="paralelo" name="paralelo" class="form-select">
                                <option value="">Selecciona un paralelo</option>
                                <?php foreach ($paralelos as $row): ?>
                                <option value="<?php echo $row['id_paralelo']; ?>"
                                    <?php echo (isset($_GET['paralelo']) && $_GET['paralelo'] == $row['id_paralelo']) ? 'selected' : ''; ?>>
                                    <?php echo $row['nombre']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="jornada" class="form-label mb-2">Jornada:</label>
                            <select id="jornada" name="jornada" class="form-select">
                                <option value="">Selecciona una jornada</option>
                                <?php while ($row = $result_jornadas->fetch_assoc()): ?>
                                <option value="<?php echo $row['id_jornada']; ?>"
                                    <?php echo (isset($_GET['jornada']) && $_GET['jornada'] == $row['id_jornada']) ? 'selected' : ''; ?>>
                                    <?php echo $row['nombre']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="historial_academico" class="form-label mb-2">Historial Académico:</label>
                            <select id="historial_academico" name="historial_academico" class="form-select">
                                <option value="">Selecciona un año lectivo</option>
                                <?php while ($row = $result_historiales->fetch_assoc()): ?>
                                <option value="<?php echo $row['id_his_academico']; ?>"
                                    <?php echo (isset($_GET['historial_academico']) && $_GET['historial_academico'] == $row['id_his_academico']) ? 'selected' : ''; ?>>
                                    <?php echo $row['año']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-start">
                        <!-- Botón para aplicar filtros -->
                        <button type="submit" id="btn-aplicar" class="btn btn-primary mr-2">Aplicar filtros</button>

                        <!-- Botón para ver manual de uso -->
                        <div class="col-auto mr-2">
                            <button type="button" class="btn btn-secondary" data-toggle="modal"
                                data-target="#modalInstrucciones1">
                                Ver Manual de Uso
                            </button>
                        </div>

                        <!-- Botón para descargar reporte -->
                        <div class="col-auto">
                            <a href="http://localhost/sistema_notas/views/admin/reporte_padre_estudiante.php"
                                class="btn btn-success">
                                Descargar Reporte
                            </a>
                        </div>
                    </div>
                </form>

                <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $mensaje_tipo === 'exito' ? 'success' : 'danger'; ?>">
                    <?php echo $mensaje; ?>
                </div>
                <?php endif; ?>

                <h3 class="mb-4"><i class='bx bx-list-check icon'></i> Lista de Estudiantes</h3>
                <?php if ($result_estudiantes->num_rows > 0): ?>
                <div class="table-container">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Cédula Estudiante</th>
                                <th>Padre</th>
                                <th>Cédula Padre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result_estudiantes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></td>
                                <td><?php echo $row['cedula']; ?></td>
                                <td>
                                    <?php 
                            if (!empty($row['nombre_padre']) && !empty($row['apellido_padre'])) {
                                echo $row['nombre_padre'] . ' ' . $row['apellido_padre'];
                            } else {
                                echo "Sin Padre Asociado";
                            }
                            ?>
                                </td>
                                <td>
                                    <?php echo !empty($row['cedula_padre']) ? $row['cedula_padre'] : "N/A"; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p>No se encontraron estudiantes con los criterios seleccionados.</p>
                <?php endif; ?>

                <h3 class="mb-4">
                    <i class='bx bx-male-female icon'></i> Relaciones Estudiante-Padre
                </h3>
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="id_estudiante" class="form-label">Estudiante:</label>
                            <select id="id_estudiante" name="id_estudiante" class="form-select" required>
                                <option value="">Selecciona un estudiante</option>
                                <?php while ($row = $result_estudiantes_sin_padre->fetch_assoc()): ?>
                                <option value="<?php echo $row['id_estudiante']; ?>">
                                    <?php echo $row['apellidos'] . ' ' . $row['nombres']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="id_padre" class="form-label">Padre:</label>
                            <select id="id_padre" name="id_padre" class="form-select" required>
                                <option value="">Selecciona un padre</option>
                                <?php while ($row = $result_padres->fetch_assoc()): ?>
                                <option value="<?php echo $row['id_padre']; ?>">
                                    <?php echo $row['apellidos'] . ' ' . $row['nombres']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Opcional: Confirmación manual para registros forzados -->
                    <div class="form-check mb-3">
                        <label class="form-check-label">
                            <input type="checkbox" name="forzar_registro" class="form-check-input">
                            Confirmo que esta relación es válida aunque los apellidos no coincidan.
                        </label>
                    </div>
                    <button type="submit" class="btn btn-custom">
                        <i class='bx bx-save icon'></i> Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal 1 -->
    <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel1">Manual de Uso - Gestión de
                        Vinculación Familiar (1/3)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Bienvenido a la <strong>herramienta de gestión de vinculaciones familiares</strong>.
                        Este módulo permite crear, editar y gestionar relaciones entre estudiantes y sus
                        padres o representantes. A continuación, se detalla cómo usar esta funcionalidad
                        paso a paso:</p>
                    <p><strong>Paso 1: Configurar los filtros iniciales</strong></p>
                    <p>Antes de realizar cualquier acción, es importante filtrar los datos para trabajar
                        únicamente con la información necesaria. Configura los siguientes criterios:</p>
                    <ul>
                        <li><strong>Nivel:</strong> Selecciona el grado académico del estudiante.</li>
                        <li><strong>Paralelo:</strong> Filtra por la sección o grupo asignado.</li>
                        <li><strong>Jornada:</strong> Indica si el estudiante pertenece a la jornada
                            matutina, vespertina, etc.</li>
                        <li><strong>Historial Académico:</strong> Marca esta opción si necesitas consultar
                            años lectivos previos.</li>
                    </ul>
                    <p>Estos filtros actualizan la lista de estudiantes visibles en la tabla principal.
                        Asegúrate de verificarlos antes de proceder.</p>
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
                    <h5 class="modal-title" id="modalInstruccionesLabel2">Manual de Uso - Gestión de
                        Vinculación Familiar (2/3)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Paso 2: Crear o editar vinculaciones</strong></p>
                    <p>Después de filtrar los datos, puedes proceder a vincular estudiantes con padres o
                        representantes. Sigue estos pasos:</p>
                    <ul>
                        <li><strong>Selecciona al estudiante:</strong> En la tabla principal, identifica al
                            estudiante que deseas vincular.</li>
                        <li><strong>Selecciona al padre o representante:</strong> En el formulario inferior,
                            elige la persona adecuada de la lista.</li>
                        <li><strong>Parentesco:</strong> Indica la relación entre el estudiante y el
                            representante (por ejemplo, padre, madre, tutor legal, etc.).</li>
                    </ul>
                    <p>Finalmente, presiona el botón <strong>Guardar</strong>. El sistema confirmará si la
                        vinculación fue exitosa o si ya existe una relación previamente registrada.</p>
                    <p>En caso de errores, puedes corregir la información antes de guardar.</p>
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
                    <h5 class="modal-title" id="modalInstruccionesLabel3">Manual de Uso - Gestión de
                        Vinculación Familiar (3/3)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Paso 3: Revisar y gestionar las vinculaciones</strong></p>
                    <p>Recuerda que todas las acciones quedan registradas en el sistema para garantizar la
                        trazabilidad de los cambios.</p>
                    <p>Con estas funciones, puedes administrar las relaciones familiares de manera eficiente
                        y mantener la base de datos siempre actualizada.</p>
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
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano.
            Todos los derechos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
    <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- SB Admin 2 JS -->
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