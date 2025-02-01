<?php 
session_start();

// Incluir el archivo de conexión
include('../../Crud/config.php'); // Ruta absoluta

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Verificar si el usuario ha iniciado sesión y si su rol es "Profesor"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Profesor'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Asegúrate de que id_profesor esté definido en la sesión
if (!isset($_SESSION['id_profesor'])) {
    echo "ID de profesor no definido en la sesión.";
    exit();
}

$id_profesor = $_SESSION['id_profesor'];

// Obtener el año académico seleccionado de la URL, o usar el actual si no está especificado
$año_academico = isset($_GET['año_academico']) ? intval($_GET['año_academico']) : date('Y');

// Consulta para obtener los años académicos disponibles
$sql_años = "SELECT DISTINCT h.id_his_academico, h.año AS año_academico, h.estado, h.fecha_ingreso
             FROM historial_academico h
             JOIN curso c ON h.id_his_academico = c.id_his_academico
             WHERE c.id_profesor = ?
             ORDER BY h.año DESC";
$stmt_años = $conn->prepare($sql_años);
$stmt_años->bind_param('i', $id_profesor);
$stmt_años->execute();
$result_años = $stmt_años->get_result();
$años_academicos = $result_años->fetch_all(MYSQLI_ASSOC);

// Ordenar los años académicos según las prioridades especificadas
usort($años_academicos, function ($a, $b) {
    // Prioridad: el año actual primero
    $a_actual = date('Y');
    if ($a['año_academico'] == $a_actual) return -1;
    if ($b['año_academico'] == $a_actual) return 1;

    // Prioridad por estado: "A" primero, luego "I"
    if ($a['estado'] === 'A' && $b['estado'] === 'I') {
        return -1;
    } elseif ($a['estado'] === 'I' && $b['estado'] === 'A') {
        return 1;
    }

    // Si ambos tienen el mismo estado, ordenar por fecha_ingreso de forma descendente
    return strtotime($b['fecha_ingreso']) <=> strtotime($a['fecha_ingreso']);
});

// Consulta para obtener los cursos del profesor en el año académico seleccionado
$sql_cursos = "SELECT 
                    c.id_curso, 
                    m.nombre AS nombre_materia, 
                    n.nombre AS nombre_nivel, 
                    p.nombre AS nombre_paralelo, 
                    s.nombre AS nombre_subnivel, 
                    e.nombre AS nombre_especialidad, 
                    j.nombre AS nombre_jornada, 
                    h.año AS año_academico
                FROM curso c
                JOIN materia m ON c.id_materia = m.id_materia
                JOIN nivel n ON c.id_nivel = n.id_nivel
                JOIN paralelo p ON c.id_paralelo = p.id_paralelo
                JOIN subnivel s ON c.id_subnivel = s.id_subnivel
                JOIN especialidad e ON c.id_especialidad = e.id_especialidad
                JOIN jornada j ON c.id_jornada = j.id_jornada
                JOIN historial_academico h ON c.id_his_academico = h.id_his_academico
                WHERE c.id_profesor = ? AND h.año = ?
                ORDER BY c.id_curso ASC"; // Ordenar por id_curso
$stmt_cursos = $conn->prepare($sql_cursos);
$stmt_cursos->bind_param('ii', $id_profesor, $año_academico);
$stmt_cursos->execute();
$result_cursos = $stmt_cursos->get_result();
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);

$stmt_años->close();
$stmt_cursos->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos Asignados | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    /* Reset básico */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        line-height: 1.6;
        color: #333;
        background-color: #f9f9f9;
        /* Fondo más claro */
    }

    .main-container {
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 3rem;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Centra el contenido horizontalmente */
        justify-content: center;
        /* Asegura buen espaciado */
    }


    /* Fondo para la página para dar contraste */
    body {
        background: linear-gradient(145deg, #ffffff, #f0f0f0);
        /* Fondo sutil con gradiente */
    }

    .banner {
        background-color: #c61e1e;
        color: white;
        padding: 2rem;
        text-align: center;
        font-size: 1.8rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 2px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid #0052aa;
        margin-bottom: 3rem;
        /* Agrega el mismo espacio que tiene el footer */
    }


    /* Título principal */
    .text-center {
        text-align: center;
        margin-bottom: 2rem;
    }

    .text-center h2 {
        color: #e53935;
        /* Rojo vibrante */
        font-size: 2rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        display: inline-block;
        padding-bottom: 10px;
    }

    .text-center h2::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50%;
        height: 3px;
        background: linear-gradient(90deg, #1976d2, #388e3c);
        /* Línea decorativa más elegante */
        transform: translateX(50%);
        /* Centrado perfecto */
    }

    /* Dropdown de Años Lectivos */
    .dropdown .btn-primary {
        background: #1976d2;
        /* Azul para el botón de Año Actual */
        border: none;
        color: #fff;
        padding: 0.9rem 1.5rem;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: background 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .dropdown .btn-primary:hover {
        background: #1565c0;
        transform: translateY(-5px);
    }

    .dropdown-menu {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #ddd;
    }

    .dropdown-menu .dropdown-item {
        padding: 1rem;
        font-size: 1rem;
        color: #333;
        border-bottom: 1px solid #eee;
        transition: background-color 0.3s ease, padding-left 0.3s ease;
        display: flex;
        align-items: center;
    }

    .dropdown-menu .dropdown-item i {
        margin-right: 0.8rem;
        /* Espacio entre el icono y el texto */
    }

    .dropdown-menu .dropdown-item:hover {
        background-color: #f5f5f5;
        padding-left: 1.5rem;
    }

    /* Año seleccionado en el dropdown */
    .dropdown-menu .active,
    .dropdown-menu .current-year {
        background-color: #1976d2 !important;
        /* Azul */
        color: #fff !important;
        /* Blanco */
    }

    .dropdown-menu .active:hover,
    .dropdown-menu .current-year:hover {
        background-color: #388e3c !important;
    }

    /* Botón regresar */
    .btn-regresar {
        background-color: #388e3c;
        /* Verde */
        color: #fff;
        padding: 1rem 2rem;
        border: none;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2rem;
        display: inline-block;
        text-decoration: none;
        transition: background-color 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-regresar:hover {
        background-color: #2e7d32;
        transform: translateY(-5px);
        color: #fff;
    }

    /* Tarjetas */
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: #fff;
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        background-color: #e53935;
        /* Rojo más profundo */
        color: #fff;
        padding: 1rem;
        font-size: 1rem;
        /* Tamaño ligeramente reducido */
        font-weight: bold;
        position: relative;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .card-header .course-id {
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        background-color: #fff;
        color: #e53935;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        padding: 1.5rem;
        font-size: 1rem;
        color: #666;
    }

    .card-body p {
        margin-bottom: 1rem;
    }

    .card-footer {
        text-align: center;
        padding: 1.5rem;
        background-color: #f5f5f5;
        border-top: 1px solid #ddd;
    }

    .card-footer .btn-view-students {
        background: linear-gradient(135deg, #1976d2, #1e88e5);
        color: #fff;
        padding: 1rem 2rem;
        border: none;
        border-radius: 30px;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: background 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .card-footer .btn-view-students:hover {
        background: linear-gradient(135deg, #1565c0, #1e88e5);
        transform: translateY(-5px);
    }

    /* Contenedor de cada tarjeta para que estén centradas */
    .card-container {
        width: 85%;
        max-width: 900px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Tarjetas alineadas correctamente */
    .card {
        width: 100%;
        margin-bottom: 1.5rem;
    }

    .alert {
        padding: 15px;
        border: 1px solid transparent;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert-error {
        color: #d32f2f;
        /* Rojo más moderno */
        background-color: #fdecea;
        /* Fondo rojo claro */
        border-color: #d32f2f;
    }

    .alert-success {
        color: #388e3c;
        /* Verde más moderno */
        background-color: #e8f5e9;
        /* Fondo verde claro */
        border-color: #388e3c;
    }

    .alert-warning {
        color: #f57c00;
        /* Naranja más vibrante */
        background-color: #fff4e5;
        /* Fondo naranja claro */
        border-color: #f57c00;
    }

    .alert {
        display: none;
        /* Ocultar alerta por defecto */
    }

    .alert.show {
        display: block;
        /* Mostrar alerta cuando sea necesario */
    }

    /* Footer */
    footer {
        border-top: 3px solid #073b73;
        /* Borde en la parte superior */
        background-color: #ad0f0f;
        color: white;
        text-align: center;
        /* Centrar el texto */
        padding: 20px 0;
        margin-top: 3rem;
        font-size: 1rem;
        /* Ancho completo */
        letter-spacing: 1px;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
        /* Sombra más pronunciada */
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }
    </style>
</head>

<body>
    <!-- Banner -->
    <div class="banner">Sistema de Gestión UEBF</div>
    <div class="container">
        <div class="main-container">
            <!-- Texto centrado -->
            <div class="text-center my-3">
                <h2>Gestión de Cursos</h2>
            </div>

            <div class="dropdown">
                <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="navbarDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Años Lectivos:
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <!-- Año Actual -->
                    <li>
                        <a class="dropdown-item current-year <?php echo !isset($_GET['año_academico']) || $_GET['año_academico'] == date('Y') ? 'active' : ''; ?>"
                            href="?año_academico=<?php echo date('Y'); ?>">
                            <i class="fas fa-calendar-day"></i>Año Actual (<?php echo date('Y'); ?>)
                        </a>
                    </li>

                    <!-- Años Académicos Disponibles -->
                    <?php foreach ($años_academicos as $año): ?>
                    <li>
                        <a class="dropdown-item <?php echo isset($_GET['año_academico']) && $_GET['año_academico'] == $año['año_academico'] ? 'active' : ''; ?>"
                            href="?año_academico=<?php echo urlencode($año['año_academico']); ?>">
                            <i class="fas fa-calendar-alt"></i><?php echo htmlspecialchars($año['año_academico']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="http://localhost/sistema_notas/views/profe/index_profe.php" class="btn btn-regresar">Regresar</a>


            <div class="container mt-5">
                <div class="row g-4">
                    <?php if ($cursos): ?>
                    <?php foreach ($cursos as $curso): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header position-relative">
                                <?php echo htmlspecialchars($curso['nombre_materia']); ?>
                                <div class="course-id"><?php echo htmlspecialchars($curso['id_curso']); ?></div>
                            </div>
                            <div class="card-body">
                                <p><strong>Nivel:</strong> <?php echo htmlspecialchars($curso['nombre_nivel']); ?></p>
                                <p><strong>Paralelo:</strong> <?php echo htmlspecialchars($curso['nombre_paralelo']); ?>
                                </p>
                                <p><strong>Subnivel:</strong> <?php echo htmlspecialchars($curso['nombre_subnivel']); ?>
                                </p>
                                <p><strong>Especialidad:</strong>
                                    <?php echo htmlspecialchars($curso['nombre_especialidad']); ?>
                                </p>
                                <p><strong>Jornada:</strong> <?php echo htmlspecialchars($curso['nombre_jornada']); ?>
                                </p>
                                <p><strong>Año Académico:</strong>
                                    <?php echo htmlspecialchars($curso['año_academico']); ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="ver_estudiantes.php?id_curso=<?php echo urlencode($curso['id_curso']); ?>"
                                    class="btn btn-view-students">Ver Estudiantes</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="alert alert-error show">
                        No hay cursos disponibles para el año académico seleccionado.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>