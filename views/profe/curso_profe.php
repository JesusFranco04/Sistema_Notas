<?php
session_start();

// Incluir el archivo de conexión
include('../../Crud/config.php'); // Ruta absoluta

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Verifica si el usuario es un profesor
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
    header("Location: ../../login.php");
    exit();
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
$sql_años = "SELECT DISTINCT h.año AS año_academico
             FROM historial_academico h
             JOIN curso c ON h.id_his_academico = c.id_his_academico
             WHERE c.id_profesor = ?
             ORDER BY h.año DESC";
$stmt_años = $conn->prepare($sql_años);
$stmt_años->bind_param('i', $id_profesor);
$stmt_años->execute();
$result_años = $stmt_años->get_result();
$años_academicos = $result_años->fetch_all(MYSQLI_ASSOC);

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
    <title>Mis Cursos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            color: #333;
            padding: 20px;
            margin: 0;
        }
        .navbar {
            margin-bottom: 20px;
            width: 100%;
        }
        .card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
            min-height: 300px;
        }
        .card:hover {
            transform: translateY(-10px);
        }
        .card-header {
            background-color: #c1121f;
            color: #fff;
            font-size: 1.2em;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 15px;
        }
        .card-body {
            background-color: #fff;
            color: #333;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .course-id {
            background-color: #27ae60;
            color: #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1em;
            position: absolute;
            top: -20px;
            right: 20px;
        }
        .card-footer {
            background-color: #f5f5f5;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            text-align: center;
            padding: 15px;
        }
        .btn-view-students {
            background-color: #27ae60;
            color: #fff;
        }
        .btn-view-students:hover {
            background-color: #2ecc71;
        }
        .container h1 {
            margin-bottom: 30px;
            font-size: 2em;
            text-align: center;
            color: #333;
        }
        @media (max-width: 768px) {
            .navbar .navbar-brand {
                font-size: 1.2em;
            }
            .card-header {
                font-size: 1em;
            }
            .container h1 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema de Calificaciones</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Año Académico: <?php echo htmlspecialchars($año_academico); ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="?año_academico=<?php echo date('Y'); ?>">Año Actual (<?php echo date('Y'); ?>)</a></li>
                        <?php foreach ($años_academicos as $año): ?>
                            <li><a class="dropdown-item" href="?año_academico=<?php echo urlencode($año['año_academico']); ?>"><?php echo htmlspecialchars($año['año_academico']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Mis Cursos</h1>
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
                                <p><strong>Paralelo:</strong> <?php echo htmlspecialchars($curso['nombre_paralelo']); ?></p>
                                <p><strong>Subnivel:</strong> <?php echo htmlspecialchars($curso['nombre_subnivel']); ?></p>
                                <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($curso['nombre_especialidad']); ?></p>
                                <p><strong>Jornada:</strong> <?php echo htmlspecialchars($curso['nombre_jornada']); ?></p>
                                <p><strong>Año Académico:</strong> <?php echo htmlspecialchars($curso['año_academico']); ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="ver_estudiantes.php?id_curso=<?php echo urlencode($curso['id_curso']); ?>" class="btn btn-view-students">Ver Estudiantes</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tiene cursos asignados en el año académico seleccionado.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
