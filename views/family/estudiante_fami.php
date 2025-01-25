<?php
session_start();

// Verificar si el usuario ha iniciado sesión y si su rol es "Padre"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Padre'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
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
    <title>Detalles del Estudiante | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
    /* Estilos básicos de la página */
    body {
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
        color: #163f6b;
        /* Azul */
        background-color: #ffffff;
        /* Blanco */
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        /* Asegura que el cuerpo tenga al menos la altura de la ventana de visualización */
        overflow-x: hidden;
    }

    /* Reset global */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .header {
        background-color: #a20e14;
        /* Rojo oscuro */
        color: #ffffff;
        /* Blanco */
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h1 {
        margin: 0;
        font-size: 2em;
        /* Tamaño de fuente escalable */
    }

    /* Contenedor Principal */
    .container {
        max-width: 1200px;
        width: 90%;
        margin: 40px auto;
        display: flex;
        flex-direction: column;
        gap: 30px;
        padding: 0 20px;
        flex-grow: 1;
    }

    /* Tarjeta de Estudiante */
    .student-card {
        background-color: #ffffff;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        width: 100%;
        max-width: 900px;
        margin: auto;
    }

    .student-card:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    /* Columna izquierda (información del estudiante) */
    .student-info {
        flex: 2;
        margin-right: 20px;
    }

    .student-info h2 {
        color: #233240;
        margin-bottom: 10px;
        font-size: 1.6rem;
    }

    .student-info p {
        margin: 5px 0;
        font-size: 1rem;
        color: #34495e;
    }

    /* Columna derecha (botón) */
    .actions {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .button {
        background-color: #163f6b;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 50px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
        text-align: center;
    }

    .button:hover {
        background-color: #233240;
        transform: translateY(-2px);
    }

    .alert {
        display: flex;
        align-items: center;
        background-color: #f8d7da;
        /* Fondo rosado elegante */
        color: #70070a;
        /* Texto rojo oscuro y sofisticado */
        border: 1px solid #f5c6cb;
        /* Borde rosado claro */
        border-radius: 10px;
        /* Bordes suaves y redondeados */
        padding: 20px;
        /* Espaciado amplio para dar aire */
        margin-top: 20px;
        /* Separación superior */
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        /* Sombra sutil y moderna */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        /* Animaciones sutiles */
    }

    .alert:hover {
        transform: scale(1.02);
        /* Efecto de aumento al pasar el mouse */
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2);
        /* Sombra más prominente */
    }

    .alert i {
        font-size: 28px;
        /* Tamaño del ícono más grande */
        color: #70070a;
        /* Color del ícono vibrante */
        margin-right: 15px;
        /* Separación del texto */
    }

    .alert p {
        margin: 0;
        /* Sin márgenes extra */
        font-size: 16px;
        /* Tamaño del texto legible */
        font-weight: bold;
        /* Resaltamos el mensaje */
        color: #721c24;
        /* Color del texto más intenso */
    }

    /* Footer */
    footer {
        background-color: #a20e14;
        color: white;
        text-align: center;
        padding: 20px;
        margin-top: auto;
        width: 100%;
        box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.2);
        font-size: 1rem;
    }

    footer p {
        margin: 0;
        line-height: 1.5;
    }

    /* Responsividad */
    @media (max-width: 1024px) {
        .header {
            font-size: 28px;
            padding: 8px 0;
            /* Reduce espacio en pantallas más pequeñas */
        }

        .container {
            gap: 20px;
        }

        .student-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-info {
            margin-bottom: 15px;
            width: 100%;
        }

        .actions {
            width: 100%;
        }

        .button {
            width: 100%;
        }

        footer {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 768px) {
        .student-card {
            padding: 15px;
        }

        .student-info h2 {
            font-size: 1.4rem;
        }

        .student-info p {
            font-size: 1rem;
        }

        .button {
            font-size: 0.9rem;
            padding: 10px 20px;
        }

        footer {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .header {
            font-size: 24px;
            padding: 8px 0;
            /* Ajuste adicional */
        }

        .container {
            gap: 15px;
        }

        .student-card {
            padding: 10px;
        }

        .student-info h2 {
            font-size: 1.3rem;
        }

        .student-info p {
            font-size: 0.9rem;
        }

        .button {
            font-size: 0.8rem;
            padding: 8px 10px;
        }

        footer {
            font-size: 0.7rem;
        }
    }
    </style>

</head>

<body>
    <!-- Encabezado Principal -->
    <header class="header">
        <h1>Sistema de Gestión UEBF</h1>
    </header>

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
                <a href="libreta.php?id_estudiante=<?php echo htmlspecialchars($row['id_estudiante']); ?>"
                    class="button">Ver Libreta</a>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <div class="alert">
            <i class='bx bx-error-alt'></i>
            <p>No tienes estudiantes registrados en el sistema. Si se trata de un hijo o varios, por favor, comunícate
                con un administrador para que te ayude a registrarlos.</p>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>
</body>

</html>