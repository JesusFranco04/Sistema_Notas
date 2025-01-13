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
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Estudiantes por Curso | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons/css/boxicons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    body {
        background-color: #f8f9fa;
        margin-bottom: 80px;
        /* Asegura que el contenido no quede oculto detrás del footer fijo */
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

    .bg-red {
        background-color: #E62433;
    }

    .bg-blue {
        background-color: #003366;
    }

    .table-responsive {
        max-height: 400px;
        overflow: auto;
        /* Permite el desplazamiento tanto vertical como horizontal */
    }

    .table {
        border-radius: 15px;
        /* Bordes redondeados en la tabla */
        overflow: hidden;
        /* Asegura que el redondeo de bordes funcione correctamente */
    }

    .table th,
    .table td {
        text-align: center;
        vertical-align: middle;
    }

    footer {
        border-top: 3px solid #003366;
        /* Borde en la parte superior */
        background-color: #E62433;
        color: white;
        text-align: center;
        /* Centrar el texto */
        padding: 20px 0;
        /* Espaciado interno vertical */
        width: 100%;
        /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        /* Sombra más pronunciada */
        position: fixed;
        bottom: 0;
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }

    .container {
        padding-bottom: 60px;
        /* Espacio para el footer */
    }

    .btn-custom {
        display: flex;
        align-items: center;
        transition: background-color 0.3s, color 0.3s;
        /* Transición suave para los cambios de color */
    }

    .btn-custom i {
        margin-right: 8px;
    }

    .btn-exportar {
        background-color: #4CAF50;
        color: white;
    }

    .btn-exportar:hover,
    .btn-exportar:focus,
    .btn-exportar:active {
        background-color: #45a049;
        /* Color de fondo al pasar el mouse y al hacer clic */
        color: white;
    }

    .btn-buscar {
        background-color: #4CAF50;
        color: white;
    }

    .btn-buscar:hover,
    .btn-buscar:focus,
    .btn-buscar:active {
        background-color: #45a049;
        /* Color de fondo al pasar el mouse y al hacer clic */
        color: white;
    }

    .btn-regresar,
    .btn-calificar {
        background-color: #003366;
        color: white;
    }

    .btn-regresar:hover,
    .btn-regresar:focus,
    .btn-regresar:active,
    .btn-calificar:hover,
    .btn-calificar:focus,
    .btn-calificar:active {
        background-color: #002d72;
        /* Color de fondo al pasar el mouse y al hacer clic */
        color: white;
    }

    .btn-regresar i,
    .btn-calificar i,
    .btn-exportar i {
        color: inherit;
    }
    </style>
</head>

<body>
    <!-- Banner -->
    <div class="banner">
        Sistema de Gestión UEBF
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        <h2 class="text-center">Lista de Estudiantes</h2>
        <form id="searchForm" class="mb-3">
            <div class="input-group">
                <input type="text" id="searchQuery" class="form-control"
                    placeholder="Buscar por cédula, apellido o nombre">
                <button type="submit" class="btn btn-buscar btn-custom">
                    <i class='bx bx-search'></i> Buscar
                </button>
            </div>
        </form>
        <div id="resultado" class="table-responsive">
            <!-- Aquí se mostrará la lista de estudiantes -->
        </div>
        <canvas id="chartEstudiantes" width="400" height="200"></canvas>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button id="btn-regresar" class="btn btn-regresar btn-custom">
                <i class='bx bx-arrow-back'></i> Regresar
            </button>
            <button id="btn-calificar" class="btn btn-calificar btn-custom">
                <i class='bx bx-pencil'></i> Calificar
            </button>
            <button id="btn-exportar" class="btn btn-exportar btn-custom">
                <i class='bx bx-export'></i> Exportar a CSV
            </button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-red text-white text-center py-2 mt-4">
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

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