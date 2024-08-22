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
    <title>Consulta de Calificaciones</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            flex: 1;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #E62433; /* Rojo para el fondo del encabezado */
            color: #ffffff; /* Blanco para el texto */
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #003366; /* Azul marino para el borde inferior */
        }

        .header h1 {
            color: #ffffff; /* Blanco para el texto en el encabezado */
        }

        .container h2 {
            color: #E62433; /* Rojo para el texto del encabezado de calificaciones */
        }

        .footer {
            text-align: center;
            padding: 15px 10px;
            background-color: #E62433;
            color: #ffffff;
            border-top: 4px solid #003366;
            width: 100%;
        }
        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .header-text {
            color: #E62433; /* Color rojo */
        }

        form {
            display: flex;
            flex-direction: column;
        }

        select {
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        hr {
            margin: 20px 0;
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
            <!-- Mostrar el formulario para seleccionar el estudiante -->
            <h1>Seleccionar Estudiante para Consultar Calificaciones</h1>
            <form action="http://localhost/sistema_notas/views/family/libreta.php" method="GET">
                <select name="id_estudiante" required>
                    <option value="">Seleccione un estudiante</option>
                    <?php while ($row = $result_estudiantes->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($row['id_estudiante']); ?>">
                            <?php echo htmlspecialchars($row['nombres']) . " " . htmlspecialchars($row['apellidos']); ?>
                        </option>
                    <?php } ?>
                </select>
                <button type="submit">Ver Calificaciones</button>
            </form>
        <?php } ?>
    </div>


    <div class="footer">
        &copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.
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
