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

// Asegúrate de que id_curso esté definido en la URL
if (!isset($_GET['id_curso'])) {
    echo "ID de curso no definido.";
    exit();
}

$id_curso = intval($_GET['id_curso']);

// Obtener los detalles del curso
$sql_curso = "SELECT c.id_curso, h.año AS año_academico
              FROM curso c
              JOIN historial_academico h ON c.id_his_academico = h.id_his_academico
              WHERE c.id_curso = ?";
$stmt_curso = $conn->prepare($sql_curso);
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
$curso = $result_curso->fetch_assoc();
$stmt_curso->close();

if (!$curso) {
    echo "Curso no encontrado.";
    exit();
}

$año_academico = $curso['año_academico'];

// Verificar el estado del año académico
$sql_año = "SELECT estado FROM historial_academico WHERE año = ?";
$stmt_año = $conn->prepare($sql_año);
$stmt_año->bind_param("s", $año_academico);
$stmt_año->execute();
$result_año = $stmt_año->get_result();
$estado_año = $result_año->fetch_assoc()['estado']; // 'A' o 'I'
$stmt_año->close();

// Convertir estado a valores legibles
if ($estado_año === 'A') {
    $estado_año = 'activo';
} elseif ($estado_año === 'I') {
    $estado_año = 'inactivo';
} else {
    echo "Estado del año académico no reconocido.";
    exit();
}

// Obtener la cantidad de estudiantes en el curso
$sql_estudiantes = "SELECT COUNT(*) AS total_estudiantes
                    FROM estudiante e
                    JOIN curso c ON e.id_nivel = c.id_nivel
                                AND e.id_subnivel = c.id_subnivel
                                AND e.id_especialidad = c.id_especialidad
                                AND e.id_paralelo = c.id_paralelo
                                AND e.id_jornada = c.id_jornada
                                AND e.id_his_academico = c.id_his_academico
                    WHERE c.id_curso = ? AND e.estado = 'A'";  // Asegúrate de contar solo estudiantes activos

$stmt_estudiantes = $conn->prepare($sql_estudiantes);
$stmt_estudiantes->bind_param("i", $id_curso);
$stmt_estudiantes->execute();
$result_estudiantes = $stmt_estudiantes->get_result();
$cantidad_estudiantes = $result_estudiantes->fetch_assoc()['total_estudiantes'];
$stmt_estudiantes->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons/css/boxicons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    /* Reset de estilos para una base limpia */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Body y fondo general */
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f4f6f9;
        color: #333;
        line-height: 1.6;
    }

    /* Estilos del Header */
    header .banner {
        background-color: #3A8DFF;
        color: white;
        text-align: center;
        padding: 25px 0;
        font-size: 2.5rem;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Contenedor principal */
    .container {
        max-width: 1100px;
        margin: 50px auto;
        padding: 25px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    /* Títulos */
    h2 {
        font-size: 2.5rem;
        color: #3A8DFF;
        font-weight: bold;
        margin-bottom: 35px;
        text-align: center;
    }

    /* Contenedor del formulario */
    form {
        display: flex;
        flex-wrap: nowrap;
        /* Evita que los elementos se apilen en pantallas grandes */
        gap: 10px;
        align-items: center;
        justify-content: center;
        max-width: 800px;
        /* Expansión sin perder control */
        margin: auto;
    }

    /* Campo de entrada */
    form .form-control {
        flex-grow: 1;
        min-width: 300px;
        max-width: 100%;
        padding: 14px;
        border: 2px solid #B71C1C;
        border-radius: 10px;
        font-size: 1.1rem;
        height: 50px;
        /* Mismo alto que el botón */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        vertical-align: middle;
    }

    form .form-control:focus {
        border-color: #3A8DFF;
        box-shadow: 0 0 12px rgba(58, 141, 255, 0.3);
        outline: none;
    }

    /* Botón */
    form button {
        background-color: #3A8DFF;
        /* Color de fondo azul */
        color: white;
        /* Texto en color blanco */
        border: none;
        /* Elimina el borde del botón */
        padding: 14px 30px;
        /* Espaciado interno: 14px arriba/abajo, 30px izquierda/derecha */
        border-radius: 10px;
        /* Bordes redondeados para una apariencia moderna */
        /* Más cuadrado para coincidir con el campo de entrada */
        font-size: 1.2rem;
        /* Tamaño del texto del botón */
        font-weight: bold;
        /* Hace que el texto sea más grueso */
        cursor: pointer;
        /* Cambia el cursor a una mano al pasar sobre el botón */
        height: 60px;
        /* Ajusta la altura del botón para que coincida con la caja de texto */
        /* Efectos de transición para suavizar cambios al interactuar con el botón */
        transition: background-color 0.3s ease, transform 0.3s ease;
        /* Sombra para dar un efecto elevado al botón */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        vertical-align: middle;
        /* Asegura que el botón esté alineado verticalmente con la caja de texto */
    }


    form button:hover {
        background-color: #287BCC;
        transform: translateY(-3px);
    }



    /* Tabla estilizada */
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    th,
    td {
        padding: 18px;
        text-align: left;
        border-bottom: 2px solid #ddd;
        font-size: 1.1rem;
    }

    th {
        background: #3A8DFF;
        color: white;
        font-weight: bold;
        text-align: center;
    }

    tr:hover {
        background: #f1f1f1;
    }

    /* Estilo de los botones en general */
    .btn-custom {
        background-color: #ffffff;
        color: #3A8DFF;
        border: 2px solid #3A8DFF;
        padding: 16px 32px;
        border-radius: 35px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        margin-top: 15px;
    }

    .btn-custom i {
        margin-right: 10px;
    }

    .btn-custom:hover {
        background-color: #3A8DFF;
        color: white;
        transform: translateY(-4px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Footer */
    footer {
        background: #333;
        color: white;
        text-align: center;
        padding: 25px;
        margin-top: 60px;
        font-size: 1.1rem;
        border-top: 5px solid #E62433;
    }
    </style>
</head>

<body>
    <!-- Header con el Banner -->
    <header>
        <div class="banner">
            Sistema de Gestión UEBF
        </div>
    </header>

    <!-- Contenedor principal del sitio -->
    <div class="container">

        <!-- Contenedor principal con los elementos -->
        <div class="main-container">

            <!-- Sección con el título principal -->
            <section class="text-center my-3">
                <h2>Panel de Seguimiento de Alumnos</h2>
            </section>

            <!-- Formulario de búsqueda -->
            <form id="searchForm" class="mb-3 mt-3" role="search">
                <div class="input-group">
                    <label for="searchQuery" class="visually-hidden">Buscar por cédula, apellido o nombre</label>
                    <input type="text" id="searchQuery" class="form-control"
                        placeholder="Buscar por cédula, apellido o nombre" aria-label="Buscar estudiantes">
                    <button type="submit" class="btn btn-buscar btn-custom" aria-label="Buscar">
                        <i class='bx bx-search'></i> Buscar
                    </button>
                </div>
            </form>

            <!-- Botón para ver lista de asistencia -->
            <a href="asistencia_estudiantes.php?id_curso=<?php echo $id_curso; ?>" class="btn btn-primary btn-custom">
                <i class='bx bx-list-check'></i> Lista de Asistencia
            </a>

            <!-- Verificar si hay mensajes de error -->
            <?php
            if (isset($_GET['error'])) {
                $error = urldecode($_GET['error']); // Decodifica el mensaje de error de la URL
                echo "<div class='error-message'>$error</div>"; // Muestra el mensaje en la página
            }
            ?>

            <!-- Contenedor para resultados y gráfica -->
            <section id="resultado-grafica" class="d-flex flex-column align-items-center mt-4">
                <!-- Contenedor de los resultados (tabla) -->
                <div id="resultado" class="table-responsive mb-4">
                    <!-- Aquí se mostrará la lista de estudiantes -->
                </div>

                <!-- Contenedor para la gráfica -->
                <div id="grafica" class="w-100 d-flex justify-content-center">
                    <canvas id="chartEstudiantes" width="600" height="300"></canvas>
                </div>
            </section>

            <!-- Botones de acción -->
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button id="btn-regresar" class="btn btn-regresar btn-custom">
                    <i class='bx bx-arrow-back'></i> Regresar
                </button>
                <button id="btn-calificar" class="btn btn-calificar btn-custom" data-estado="<?php echo $estado_año; ?>"
                    data-id-curso="<?php echo $id_curso; ?>">
                    <i class='bx bx-pencil'></i> Calificar
                </button>
                <a href="nomina_estudiantes.php?id_curso=<?php echo $id_curso; ?>" class="btn btn-success btn-custom">
                    <i class='bx bx-file'></i> Reporte
                </a>
                <button id="btn-exportar" class="btn btn-exportar btn-custom">
                    <i class='bx bx-export'></i> Exportar a CSV
                </button>
            </div>
        </div>
    </div>
</body>

<!-- Footer -->
<footer class="bg-footer text-white text-center py-2 mt-4">
    <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los
        derechos reservados.</p>
</footer>


<script>
// Obtiene el estado del año académico y la cantidad de estudiantes desde PHP
const estado = "<?php echo $estado_año; ?>";
const cantidadEstudiantes =
    <?php echo $cantidad_estudiantes; ?>; // Se espera que $cantidad_estudiantes venga de PHP como un número

// Verifica las condiciones para deshabilitar el botón
if (estado === 'inactivo' || cantidadEstudiantes === 0) {
    document.getElementById('btn-asistencia').disabled = true;
}
</script>

<script>
$(document).ready(function() {
    // Llamada AJAX para obtener la lista de estudiantes
    function loadEstudiantes(query = '') {
        $.ajax({
            url: 'get_estudiantes.php',
            type: 'POST',
            data: {
                id_curso: <?php echo json_encode($id_curso); ?>,
                año: '<?php echo $año_academico; ?>',
                query: query
            },
            success: function(response) {
                $('#resultado').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX para estudiantes:', status, error);
            }
        });
    }

    // Cargar la lista de estudiantes al cargar la página
    loadEstudiantes();

    // Manejo de la búsqueda
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        var query = $('#searchQuery').val();
        loadEstudiantes(query);
    });

    // Manejo del botón regresar
    $('#btn-regresar').click(function() {
        window.location.href = 'curso_profe.php'; // Cambia esta URL si es necesario
    });

    // Manejo del botón calificar
    $('#btn-calificar').click(function() {
        // Obtener el estado del año lectivo y el id del curso desde los atributos del botón
        const estadoAño = $(this).data('estado'); // "activo" o "inactivo"
        const idCurso = $(this).data('id-curso');

        // Redirigir según el estado del año lectivo
        if (estadoAño === 'activo') {
            // Año lectivo activo: enviar a registro_calificaciones.php
            window.location.href = `registro_calificaciones.php?id_curso=${idCurso}`;
        } else if (estadoAño === 'inactivo') {
            // Año lectivo inactivo: enviar a cursos_inactivos.php
            window.location.href = `cursos_inactivos.php?id_curso=${idCurso}`;
        } else {
            alert('Error: Estado del año lectivo no reconocido.');
        }
    });

    // Manejo del botón exportar
    $('#btn-exportar').click(function() {
        window.location.href =
            'exportar_estudiantes.php?id_curso=<?php echo $id_curso; ?>&año=<?php echo $año_academico; ?>';
    });

    // Llamada AJAX para obtener estadísticas
    $.ajax({
        url: 'get_estadisticas.php',
        type: 'POST',
        data: {
            id_curso: <?php echo json_encode($id_curso); ?>,
            año: '<?php echo $año_academico; ?>'
        },
        success: function(response) {
            var data = JSON.parse(response);

            var ctx = document.getElementById('chartEstudiantes').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(data.edades), // Etiquetas para el gráfico
                    datasets: [{
                        label: 'Número de Estudiantes por Edad',
                        data: Object.values(data
                            .edades), // Datos para el gráfico
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error en la solicitud AJAX:', status, error);
        }
    });
});
</script>
</body>

</html>