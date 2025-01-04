<?php
session_start();
include('../../Crud/config.php'); // Ruta absoluta

// Verificar si el usuario ha iniciado sesión y si su rol es "Administrador" o "Superusuario"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Consultas para obtener estadísticas
$queryProfesores = "SELECT COUNT(*) as total FROM profesor";
$queryEstudiantes = "SELECT COUNT(*) as total FROM estudiante";
$queryUsuarios = "SELECT COUNT(*) as total FROM usuario";

// Ejecutar consultas
$resultProfesores = $conn->query($queryProfesores);
$resultEstudiantes = $conn->query($queryEstudiantes);
$resultUsuarios = $conn->query($queryUsuarios);

// Obtener resultados
$total_profesores = $resultProfesores->fetch_assoc()['total'];
$total_estudiantes = $resultEstudiantes->fetch_assoc()['total'];
$total_usuarios = $resultUsuarios->fetch_assoc()['total'];

// Consultas para obtener datos para el gráfico
$queryEstadisticas = "SELECT a.año, 
                            SUM(p.id_profesor) as profesores, 
                            SUM(e.id_estudiante) as estudiantes, 
                            COUNT(u.id_usuario) as usuarios
                      FROM historial_academico a
                      LEFT JOIN profesor p ON a.id_his_academico = p.id_usuario
                      LEFT JOIN estudiante e ON a.id_his_academico = e.id_his_academico
                      LEFT JOIN usuario u ON a.id_his_academico = u.id_rol
                      GROUP BY a.año";

$resultEstadisticas = $conn->query($queryEstadisticas);
$datosEstadisticas = [];

while ($row = $resultEstadisticas->fetch_assoc()) {
    $datosEstadisticas[] = $row;
}

// Procesar datos para el gráfico
$labels = [];
$dataProfesores = [];
$dataEstudiantes = [];
$dataUsuarios = [];

foreach ($datosEstadisticas as $data) {
    $labels[] = $data['año'];
    $dataProfesores[] = $data['profesores'];
    $dataEstudiantes[] = $data['estudiantes'];
    $dataUsuarios[] = $data['usuarios'];
}

// Cerrar la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas | Sistema de Gestión UEBF</title>
    <!-- Estilos y librerías externas -->
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/css/boxicons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
        integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
    /* Estilos personalizados */
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f0f0;
    }

    .container-fluid {
        padding: 20px;
    }

    .card-statistic {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        height: 150px;
        /* Altura reducida de las tarjetas */
    }

    .card-statistic .card-body {
        padding: 20px;
        position: relative;
    }

    .card-statistic h5 {
        font-size: 1.2rem;
        font-weight: bold;
        color: #031d44;
        /* Color del título */
        display: flex;
        align-items: center;
    }

    .card-statistic h5 i {
        font-size: 1.5rem;
        margin-left: 10px;
        /* Espacio entre el icono y el texto */
    }

    .card-statistic p {
        font-size: 2rem;
        font-weight: bold;
        color: #305B7A;
        /* Color del texto */
    }

    .border-left-primary {
        border-left: 5px solid #c42021;
        /* Rojo llamativo */
    }

    .border-left-success {
        border-left: 5px solid #ffeaae;
        /* Azul oscuro */
    }

    .border-left-info {
        border-left: 5px solid #47a025;
        /* Amarillo */
    }

    .chart-container {
        margin-top: 20px;
        background-color: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .table-container {
        max-width: 100%;
        /* Ancho máximo ajustable */
        overflow-x: auto;
        /* Scroll horizontal si hay desbordamiento */
        overflow-y: auto;
        /* Scroll vertical si hay desbordamiento */
    }

    .table thead th {
        background-color: #dc3545;
        color: white;
        text-align: center;
    }

    .table tbody td {
        text-align: center;
    }

    .section-title {
        font-size: 1.2rem;
        font-weight: bold;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    .filter-icon {
        margin-right: 5px;
    }

    .filter-container {
        margin-bottom: 1rem;
    }

    footer {
        background-color: white; /* Color de fondo blanco */
        color: #737373; /* Color del texto en gris oscuro */
        text-align: center; /* Centrar el texto */
        padding: 20px 0; /* Espaciado interno vertical */
        width: 100%; /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Sombra más pronunciada */
    }

    footer p {
        margin: 0; /* Eliminar el margen de los párrafos */
    }
    </style>
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>

    <div class="container-fluid">
        <!-- Encabezado de página -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Estadísticas</h1>
        </div>

        <!-- Línea horizontal -->
        <hr style="margin-top: 1; margin-bottom: 20px;">

        <div class="row justify-content-center">
            <!-- Tarjeta de Total de Profesores -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow card-statistic">
                    <div class="card-body">
                        <h5 class="card-title">Total de Profesores <i class='bx bxs-user-voice'></i></h5>
                        <p class="card-text" id="totalProfesores"><?php echo $total_profesores; ?></p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Total de Estudiantes -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow card-statistic">
                    <div class="card-body">
                        <h5 class="card-title">Total de Estudiantes <i class='bx bxs-graduation'></i></h5>
                        <p class="card-text" id="totalEstudiantes"><?php echo $total_estudiantes; ?></p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Total de Usuarios -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow card-statistic">
                    <div class="card-body">
                        <h5 class="card-title">Total de Usuarios <i class='bx bxs-group'></i></h5>
                        <p class="card-text" id="totalUsuarios"><?php echo $total_usuarios; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Línea horizontal después de las tarjetas -->
        <hr style="margin-top: 20px; margin-bottom: 20px;">

        <!-- Contenedor para el gráfico de área apilada -->
        <div class="chart-container">
            <canvas id="graficoAreaApilada"></canvas>
        </div>
    </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
    </footer>

    <!-- Scripts JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <!-- Bootstrap core JavaScript -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- SB Admin 2 JS -->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <!-- DataTables JS -->



    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Función para animar el número con Anime.js
        function animateValue(id, start, end, duration) {
            var obj = document.getElementById(id);
            var range = end - start;
            var current = start;
            var increment = end > start ? 1 : -1;
            var stepTime = Math.abs(Math.floor(duration / range));
            var timer = setInterval(function() {
                current += increment;
                obj.innerHTML = current;
                if (current == end) {
                    clearInterval(timer);
                }
            }, stepTime);
        }

        // Animar los valores
        animateValue('totalProfesores', 0, <?php echo $total_profesores; ?>, 1000);
        animateValue('totalEstudiantes', 0, <?php echo $total_estudiantes; ?>, 1000);
        animateValue('totalUsuarios', 0, <?php echo $total_usuarios; ?>, 1000);

        // Datos para el gráfico de área apilada
        var datosAreaApilada = {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Profesores',
                backgroundColor: '#c42021', // Color rojo llamativo
                data: <?php echo json_encode($dataProfesores); ?>,
                stack: 'Stack 1',
            }, {
                label: 'Estudiantes',
                backgroundColor: '#326d1e', // Color azul oscuro
                data: <?php echo json_encode($dataEstudiantes); ?>,
                stack: 'Stack 1',
            }, {
                label: 'Usuarios',
                backgroundColor: '#003366', // Color amarillo
                data: <?php echo json_encode($dataUsuarios); ?>,
                stack: 'Stack 1',
            }]
        };

        // Configuración del gráfico de área apilada
        var configAreaApilada = {
            type: 'bar', // Tipo de gráfico de barras
            data: datosAreaApilada,
            options: {
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    },
                },
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                    },
                },
                animation: {
                    onComplete: function(animation) {
                        // Animación con Anime.js para mostrar el gráfico
                        anime({
                            targets: '#graficoAreaApilada',
                            opacity: 1,
                            duration: 1000,
                            easing: 'easeOutQuad'
                        });
                    }
                }
            },
        };

        // Inicializar el gráfico de área apilada
        var ctxAreaApilada = document.getElementById('graficoAreaApilada').getContext('2d');
        new Chart(ctxAreaApilada, configAreaApilada);
    });
    </script>
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>

</html>
