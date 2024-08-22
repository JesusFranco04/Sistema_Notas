<?php
session_start();

// Verificar el rol del usuario
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Padre') {
    $_SESSION['mensaje'] = "Acceso denegado. Debe ser padre para acceder a esta página.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../../login.php");
    exit();
}

// Incluir el archivo de conexión
include('../../Crud/config.php'); // Ruta absoluta

// Obtener el id_padre desde la sesión
if (isset($_SESSION['id_padre'])) {
    $id_padre = $_SESSION['id_padre'];
} else {
    die("ID del padre no encontrado en la sesión.");
}

// Consulta para verificar si el padre existe en la base de datos
$query_padre = "SELECT id_padre FROM padre WHERE id_padre = ?";
$stmt_padre = $conn->prepare($query_padre);
if ($stmt_padre === false) {
    die('Error en la preparación de la consulta del padre: ' . $conn->error);
}
$stmt_padre->bind_param("i", $id_padre);
$stmt_padre->execute();
$result_padre = $stmt_padre->get_result();

if ($result_padre->num_rows === 0) {
    die('El ID del padre no se encuentra en la base de datos.');
}

// Consultar la información de los estudiantes asociados con el padre
$query_estudiantes = "
    SELECT e.id_estudiante, e.nombres, e.apellidos, n.nombre AS nombre_nivel, p.nombre AS nombre_paralelo, s.nombre AS nombre_subnivel, esp.nombre AS nombre_especialidad, j.nombre AS nombre_jornada, h.año
    FROM estudiante e
    JOIN padre_x_estudiante pxe ON e.id_estudiante = pxe.id_estudiante
    JOIN nivel n ON e.id_nivel = n.id_nivel
    JOIN paralelo p ON e.id_paralelo = p.id_paralelo
    JOIN subnivel s ON e.id_subnivel = s.id_subnivel
    JOIN especialidad esp ON e.id_especialidad = esp.id_especialidad
    JOIN jornada j ON e.id_jornada = j.id_jornada
    JOIN historial_academico h ON e.id_his_academico = h.id_his_academico
    WHERE pxe.id_padre = ?
";

$stmt_estudiantes = $conn->prepare($query_estudiantes);
if ($stmt_estudiantes === false) {
    die('Error en la preparación de la consulta de los estudiantes: ' . $conn->error);
}

$stmt_estudiantes->bind_param("i", $id_padre);
$stmt_estudiantes->execute();
$result_estudiantes = $stmt_estudiantes->get_result();

if ($result_estudiantes === false) {
    die('Error al ejecutar la consulta: ' . $stmt_estudiantes->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Hijos | Perfil del Padre</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            align-items: center;
        }

        .header {
            background-color: #E62433;
            color: #ffffff;
            padding: 20px 50px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #003366;
            width: 100%;
        }

        .footer {
            text-align: center;
            padding: 15px 10px;
            background-color: #E62433;
            color: #ffffff;
            border-top: 4px solid #003366;
            width: 100%;
            margin-top: auto;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            margin: 40px auto;
            display: flex;
            flex-direction: column;
            gap: 30px;
            padding: 0 20px;
            flex-grow: 1;
        }

        .student-card {
            background-color: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            flex-wrap: wrap;
        }

        .student-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .student-info {
            max-width: 70%;
            flex: 1;
            min-width: 250px;
        }

        .student-info h2 {
            color: #E62433;
            margin-bottom: 15px;
            font-size: 1.6rem;
        }

        .student-info p {
            margin: 5px 0;
            font-size: 1rem;
            color: #2c3e50;
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            min-width: 150px;
        }

        .button {
            background-color: #4CAF50;
            color: #ffff;
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .button:hover {
            background-color: #45a049;
        }

        @media (max-width: 768px) {
            .student-card {
            background-color: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease, color 0.3s ease; /* Añadido color en la transición */
            flex-wrap: wrap;
        }

        .student-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            color: #1a1a1a; /* Color de texto más oscuro al pasar el mouse */
        }

        .student-info {
            max-width: 70%;
            flex: 1;
            min-width: 250px;
        }

        .student-info h2 {
            color: #E62433;
            margin-bottom: 15px;
            font-size: 1.6rem;
            transition: color 0.3s ease; /* Transición para el color del texto */
        }

        .student-info p {
            margin: 5px 0;
            font-size: 1rem;
            color: #2c3e50;
            transition: color 0.3s ease; /* Transición para el color del texto */
        }

        .student-card:hover .student-info h2,
        .student-card:hover .student-info p {
            color: #1a1a1a; /* Color de texto más oscuro al pasar el mouse */
        }


            .actions {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema de Gestión UEBF</h1>
    </div>

    <div class="container">
        <?php if ($result_estudiantes->num_rows > 0): ?>
            <?php while ($row = $result_estudiantes->fetch_assoc()): ?>
            <div class="student-card">
                <div class="student-info">
                    <h2><?php echo htmlspecialchars($row['nombres'] . ' ' . $row['apellidos']); ?></h2>
                    <p><strong>Nivel:</strong> <?php echo htmlspecialchars($row['nombre_nivel']); ?></p>
                    <p><strong>Paralelo:</strong> <?php echo htmlspecialchars($row['nombre_paralelo']); ?></p>
                    <p><strong>Subnivel:</strong> <?php echo htmlspecialchars($row['nombre_subnivel']); ?></p>
                    <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($row['nombre_especialidad']); ?></p>
                    <p><strong>Jornada:</strong> <?php echo htmlspecialchars($row['nombre_jornada']); ?></p>
                    <p><strong>Año Lectivo:</strong> <?php echo htmlspecialchars($row['año']); ?></p>
                </div>
                <div class="actions">
                    <a href="libreta.php?id_estudiante=<?php echo htmlspecialchars($row['id_estudiante']); ?>" class="button">Ver Libreta</a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No tienes hijos registrados en el sistema.</p>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        &copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.
    </div>
</body>
</html>
