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
    SELECT e.id_estudiante, e.nombres, e.apellidos
    FROM estudiante e
    JOIN padre_x_estudiante pxe ON e.id_estudiante = pxe.id_estudiante
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

// Verificar si se ha seleccionado un estudiante para ver calificaciones
$id_estudiante = isset($_GET['id_estudiante']) ? $_GET['id_estudiante'] : '';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Estudiantes | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
    /* Estilos básicos de la página */
    body {
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
        color: #163f6b;
        /* Gris oscuro elegante */
        background-color: #f5f7fa;
        /* Fondo claro y relajante */
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        overflow-x: hidden;
        line-height: 1.7;
        /* Más espacio entre líneas para mejor legibilidad */
    }

    .container {
        flex: 1;
        max-width: 1000px;
        /* Más espacio horizontal */
        margin: 50px auto;
        padding: 40px;
        background-color: #ffffff;
        /* Fondo blanco para contraste */
        border-radius: 16px;
        /* Bordes redondeados suaves */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        /* Sombra ligera para elegancia */
        display: flex;
        flex-direction: column;
        gap: 30px;
        /* Espacio uniforme entre elementos */
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

    .header h1 {
        margin: 0;
        font-size: 2em;
        /* Tamaño de fuente escalable */
    }

    .container h2 {
        color: #7e090d;
        /* Rojo oscuro */
        font-size: 2rem;
        margin-bottom: 10px;
        border-bottom: 2px solid #ffd4d3;
        /* Línea decorativa */
        padding-bottom: 10px;
    }

    p,
    label {
        font-size: 1.2rem;
        /* Texto más grande */
        color: #555555;
        /* Gris suave para comodidad visual */
        margin-bottom: 10px;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 20px;
        /* Espacio uniforme entre campos */
    }

    select,
    input[type="text"],
    input[type="email"],
    textarea {
        padding: 15px;
        font-size: 1.1rem;
        border: 1px solid #ddd;
        border-radius: 12px;
        background-color: #f8f8f8;
        /* Fondo claro para los campos */
        box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    select:focus,
    input:focus,
    textarea:focus {
        border-color: #a20e14;
        /* Enfoque con rojo elegante */
        box-shadow: 0 0 8px rgba(162, 14, 20, 0.2);
        outline: none;
    }

    button {
        padding: 15px 30px;
        font-size: 1.1rem;
        color: #ffffff;
        background-color: #a20e14;
        /* Rojo oscuro */
        border: none;
        border-radius: 12px;
        /* Bordes redondeados para elegancia */
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    button:hover {
        background-color: #600404;
        /* Más claro al pasar el ratón */
        transform: translateY(-3px);
        /* Efecto de elevación */
    }

    hr {
        border: 0;
        height: 2px;
        background: #ddd;
        margin: 30px 0;
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
        /* Espaciado amplio */
        margin-top: 20px;
        /* Separación superior */
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        /* Sombra moderna */
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
        /* Tamaño del ícono */
        color: #70070a;
        /* Color del ícono */
        margin-right: 15px;
        /* Separación del texto */
    }

    .alert p {
        margin: 0;
        /* Sin márgenes */
        font-size: 16px;
        /* Tamaño del texto */
        font-weight: bold;
        /* Texto resaltado */
        color: #721c24;
        /* Color intenso del texto */
    }

    /* Footer */
    .footer {
        background-color: #a20e14;
        color: white;
        text-align: center;
        padding: 20px;
        margin-top: auto;

        width: 100%;
        box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.2);
        font-size: 1rem;
    }



    /* Ajuste responsivo */
    @media (max-width: 768px) {
        .container {
            padding: 20px;
        }

        .header h1 {
            font-size: 1.8rem;
        }

        .container h2 {
            font-size: 1.6rem;
        }

        button {
            font-size: 1rem;
            padding: 12px 20px;
        }
    }

    @media (max-width: 480px) {
        .header h1 {
            font-size: 1.5rem;
        }

        .container h2 {
            font-size: 1.4rem;
        }

        p,
        label {
            font-size: 1rem;
        }
    }
    </style>

</head>

<body>
    <div class="header">
        <h1>Sistema de Gestión UEBF</h1>
    </div>

    <div class="container">
        <?php if ($id_estudiante) { ?>
        <!-- Mostrar las calificaciones del estudiante -->
        <h2>Calificaciones del Estudiante</h2>
        <?php
            // Preparar y ejecutar la consulta para obtener las calificaciones del estudiante
            $sql = "SELECT * FROM calificaciones WHERE id_estudiante = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id_estudiante);
            $stmt->execute();
            $result = $stmt->get_result();

            // Mostrar las calificaciones
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "Asignatura: " . htmlspecialchars($row['asignatura']) . "<br>";
                    echo "Nota: " . htmlspecialchars($row['nota']) . "<br>";
                    echo "<hr>";
                }
            } else {
                echo "No se encontraron calificaciones para el estudiante seleccionado.";
            }
            $stmt->close();
            ?>
        <?php } else { ?>
        <!-- Mostrar siempre el encabezado -->
        <h1>Selecciona un Estudiante para Consultar sus Calificaciones</h1>
        <?php if ($result_estudiantes->num_rows > 0) { ?>
        <!-- Mostrar el formulario para seleccionar el estudiante -->
        <form action="http://localhost/sistema_notas/views/family/libreta.php" method="GET">
            <select name="id_estudiante" required>
                <option value="">Seleccione un estudiante de la lista desplegable...</option>
                <?php while ($row = $result_estudiantes->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($row['id_estudiante']); ?>">
                    <?php echo htmlspecialchars($row['nombres']) . " " . htmlspecialchars($row['apellidos']); ?>
                </option>
                <?php } ?>
            </select>
            <button type="submit">Ver Calificaciones</button>
        </form>
        <?php } else { ?>
        <!-- Mostrar alerta si no hay estudiantes registrados -->
        <div class="alert">
            <i class='bx bx-error-alt'></i>
            <p>No tienes hijos registrados en el sistema.</p>
        </div>
        <?php } ?>
        <?php } ?>
    </div>

    <!-- Pie de página (footer) -->
    <div class="footer">
        &copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
        los derechos reservados.
    </div>

    <!-- JavaScript (opcional) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ejemplo: Mensaje al seleccionar un estudiante (esto es opcional)
        const selectElement = document.querySelector('select[name="id_estudiante"]');

        selectElement.addEventListener('change', function() {
            const selectedValue = this.value;
            if (selectedValue) {
                console.log(`Estudiante seleccionado: ${selectedValue}`);
            }
        });
    });
    </script>
</body>

</html>

<?php
// Cerrar la conexión
$stmt_padre->close();
$stmt_estudiantes->close();
$conn->close();
?>