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

// Consulta para obtener los cursos del profesor con información detallada
$sql = "SELECT 
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
        WHERE c.id_profesor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_profesor);
$stmt->execute();
$result = $stmt->get_result();
$cursos = $result->fetch_all(MYSQLI_ASSOC);
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
            background-color: #c1121f; /* Rojo bonito */
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
            background-color: #27ae60; /* Verde bonito */
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
    </style>
</head>
<body>
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
                                <p>Nivel: <?php echo htmlspecialchars($curso['nombre_nivel']); ?></p>
                                <p>Paralelo: <?php echo htmlspecialchars($curso['nombre_paralelo']); ?></p>
                                <p>Subnivel: <?php echo htmlspecialchars($curso['nombre_subnivel']); ?></p>
                                <p>Especialidad: <?php echo htmlspecialchars($curso['nombre_especialidad']); ?></p>
                                <p>Jornada: <?php echo htmlspecialchars($curso['nombre_jornada']); ?></p>
                                <p>Año Académico: <?php echo htmlspecialchars($curso['año_academico']); ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="ver_estudiantes.php?id_curso=<?php echo urlencode($curso['id_curso']); ?>" class="btn btn-view-students">Ver Estudiantes</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tiene cursos asignados.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
