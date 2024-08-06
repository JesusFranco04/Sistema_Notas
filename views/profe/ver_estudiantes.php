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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Estudiantes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        // Redirigir a la página de calificación masiva
        window.location.href = 'registro_calificaciones.php?id_curso=<?php echo $id_curso; ?>';
    });

    // Manejo del botón exportar
    $('#btn-exportar').click(function() {
        window.location.href = 'exportar_estudiantes.php?id_curso=<?php echo $id_curso; ?>&año=<?php echo $año_academico; ?>';
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
                        data: Object.values(data.edades), // Datos para el gráfico
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
<style>
    .table-wrapper {
        max-height: 400px; /* Ajusta según el diseño deseado */
        overflow-y: auto;
        overflow-x: hidden; /* Evita el scrollbar horizontal */
    }

    table {
        width: 100%; /* Asegura que la tabla use todo el ancho del contenedor */
        border-collapse: collapse; /* Elimina el espacio entre celdas */
    }

    /* Estilo para el gráfico */
    #chartEstudiantes {
        max-width: 100%;
        margin-top: 20px;
    }
</style>


</head>
<body>
<div class="container mt-5">
    <form id="searchForm" class="mb-3">
        <div class="input-group">
            <input type="text" id="searchQuery" class="form-control" placeholder="Buscar por cédula, apellido o nombre">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>
        <div id="resultado">
            <!-- Aquí se mostrará la lista de estudiantes -->
        </div>
    <canvas id="chartEstudiantes" width="400" height="200"></canvas>
    <button id="btn-regresar" class="btn btn-secondary">Regresar</button>
    <button id="btn-calificar" class="btn btn-primary">Calificar</button>
    <button id="btn-exportar" class="btn btn-success">Exportar a CSV</button>
</div>
</body>
</html>

