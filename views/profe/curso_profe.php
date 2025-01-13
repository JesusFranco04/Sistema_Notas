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
    <title>Gestión de Cursos Asignados | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilo general del cuerpo */
        body {
            background-color: #f8f9fa;
            margin-bottom: 80px; /* Asegura que el contenido no quede oculto detrás del footer fijo */
        }
        .banner {
            background-color: #E62433;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #003366;
        }
        /* Ajusta el contenedor principal para evitar que el banner lo cubra */
        .container {
            margin-top: 120px; /* Añade margen superior para el contenido debajo del banner */
        }


        /* Estilo del botón regresar */
        .btn-regresar {
            background-color: #f83b4a; /* Color de fondo rojo para el botón */
            color: #ffffff; /* Color de texto blanco */
            border-radius: 5px; /* Bordes redondeados */
            padding: 10px 20px; /* Espaciado interior del botón */
            font-size: 1em; /* Tamaño de fuente del botón */
            text-decoration: none; /* Sin subrayado */
            display: inline-block; /* Mostrar en línea como un bloque */
            margin-left: 20px; /* Espacio a la izquierda del botón */
        }

        .btn-regresar:hover {
            background-color: #E62433; /* Color de fondo más oscuro al pasar el ratón */
            color: #ffffff; /* Color de texto blanco */
        }

        /* Estilo para el dropdown */
        .dropdown {
            flex: 1; /* Permitir que el dropdown ocupe el espacio disponible */
        }


        /* Estilo de las tarjetas */
        .card {
            border: none; /* Sin borde para las tarjetas */
            border-radius: 15px; /* Bordes redondeados */
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* Transición suave para efectos */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); /* Sombra sutil */
            margin-bottom: 20px; /* Espacio debajo de las tarjetas */
            position: relative; /* Posición relativa para el contenido absoluto */
            min-height: 300px; /* Altura mínima */
        }
        .card:hover {
            transform: translateY(-10px); /* Desplazar la tarjeta al pasar el ratón */
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2); /* Sombra más pronunciada */
        }
        .card-header {
            background-color: #E62433; /* Color de fondo rojo para el encabezado */
            color: #ffffff; /* Color de texto blanco en el encabezado */
            font-size: 1.2em; /* Tamaño de fuente del encabezado */
            border-top-left-radius: 15px; /* Bordes redondeados superiores */
            border-top-right-radius: 15px; /* Bordes redondeados superiores */
            padding: 15px; /* Espaciado interior del encabezado */
        }
        .card-body {
            background-color: #ffffff; /* Color de fondo blanco para el cuerpo de la tarjeta */
            color: #333; /* Color de texto oscuro */
            padding: 20px; /* Espaciado interior del cuerpo */
            display: flex; /* Usar flexbox para el diseño */
            flex-direction: column; /* Dirección vertical del contenido */
            justify-content: space-between; /* Espaciado equidistante */
        }
        .course-id {
            background-color: #003366; /* Color de fondo azul oscuro para el ID del curso */
            color: #ffffff; /* Color de texto blanco */
            border-radius: 50%; /* Forma circular */
            width: 50px; /* Ancho fijo */
            height: 50px; /* Alto fijo */
            display: flex; /* Usar flexbox para centrar el texto */
            align-items: center; /* Centrar verticalmente */
            justify-content: center; /* Centrar horizontalmente */
            font-size: 1.2em; /* Tamaño de fuente del ID */
            position: absolute; /* Posicionamiento absoluto dentro de la tarjeta */
            top: -20px; /* Posicionar arriba de la tarjeta */
            right: 20px; /* Posicionar a la derecha */
        }
        .card-footer {
            background-color: #f0f0f0; /* Color de fondo claro para el pie de la tarjeta */
            border-bottom-left-radius: 15px; /* Bordes redondeados inferiores */
            border-bottom-right-radius: 15px; /* Bordes redondeados inferiores */
            text-align: center; /* Alinear texto al centro */
            padding: 15px; /* Espaciado interior del pie */
        }
        .btn-view-students {
            background-color: #004a94; /* Color de fondo azul oscuro para el botón */
            color: #ffffff; /* Color de texto blanco en el botón */
            border-radius: 5px; /* Bordes redondeados */
            padding: 10px 20px; /* Espaciado interior del botón */
            font-size: 1em; /* Tamaño de fuente del botón */
            text-decoration: none; /* Sin subrayado */
            display: inline-block; /* Mostrar en línea como un bloque */
        }
        .btn-view-students:hover {
            background-color: #003366; /* Color de fondo más oscuro al pasar el ratón */
            color: #ffffff; /* Color de texto blanco en el botón */
        }

        /* Estilo del contenedor principal */
        .container h1 {
            margin-bottom: 30px; /* Espacio debajo del encabezado */
            font-size: 2em; /* Tamaño de fuente del encabezado */
            text-align: center; /* Alinear el texto al centro */
            color: #003366; /* Color azul oscuro para el encabezado */
        }

        /* Estilos responsivos para pantallas pequeñas */
        @media (max-width: 768px) {
            .navbar .navbar-brand {
                font-size: 1.2em; /* Tamaño de fuente reducido en pantallas pequeñas */
            }
            .card-header {
                font-size: 1em; /* Tamaño de fuente reducido en pantallas pequeñas */
            }
            .container h1 {
                font-size: 1.5em; /* Tamaño de fuente reducido en pantallas pequeñas */
            }
        }

        /* Estilo del menú desplegable */
        .dropdown-menu {
            min-width: 200px; /* Ancho mínimo del menú desplegable */
        }
        .dropdown-item {
            display: flex; /* Usar flexbox para alinear elementos */
            align-items: center; /* Centrar verticalmente */
            padding: 10px 15px; /* Espaciado interior de los elementos del menú */
            transition: background-color 0.3s, color 0.3s; /* Transición suave para efectos */
        }
        .dropdown-item:hover {
            background-color: #003366; /* Color de fondo al pasar el ratón */
            color: #ffffff; /* Color de texto blanco al pasar el ratón */
        }
        .dropdown-item i {
            margin-right: 10px; /* Espacio a la derecha del ícono */
        }
        .current-year {
            background-color: #28a745; /* Color de fondo verde para el año actual */
            color: #ffffff; /* Color de texto blanco */
        }
        .current-year:hover {
            background-color: #218838; /* Color de fondo verde oscuro al pasar el ratón */
        }
        footer {
            border-top: 3px solid #003366; /* Borde en la parte superior */
            background-color: #E62433;
            color: white; 
            text-align: center; /* Centrar el texto */
            padding: 20px 0; /* Espaciado interno vertical */
            width: 100%; /* Ancho completo */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Sombra más pronunciada */
            position: fixed;
            bottom: 0;
        }
        footer p {
            margin: 0; /* Eliminar el margen de los párrafos */
        }
    </style>
</head>
<body>
    <!-- Banner -->
    <div class="banner">Cursos Académicos</div>
    <div class="container">
        <div class="dropdown">
            <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Años Lectivos:
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <!-- Año Actual -->
                <li>
                    <a class="dropdown-item current-year" <?php echo !isset($_GET['año_academico']) || $_GET['año_academico'] == date('Y') ? 'active' : ''; ?>" href="?año_academico=<?php echo date('Y'); ?>">
                        <i class="fas fa-calendar-day"></i>Año Actual (<?php echo date('Y'); ?>)
                    </a>
                </li>
                
                <!-- Años Académicos Disponibles -->
                <?php foreach ($años_academicos as $año): ?>
                    <li>
                        <a class="dropdown-item <?php echo isset($_GET['año_academico']) && $_GET['año_academico'] == $año['año_academico'] ? 'active' : ''; ?>" href="?año_academico=<?php echo urlencode($año['año_academico']); ?>">
                            <i class="fas fa-calendar-alt"></i><?php echo htmlspecialchars($año['año_academico']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="http://localhost/sistema_notas/views/profe/index_profe.php" class="btn btn-regresar">Regresar</a>
    </div>

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
                <p>No hay cursos disponibles para el año académico seleccionado.</p>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.</p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>