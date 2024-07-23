<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta absoluta 

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador
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
    </style>
</head>

<body>
    <?php
    // Incluir el archivo navbar_admin.php una sola vez desde el mismo directorio
    include_once 'navbar_admin.php';

    

    // Datos temporales para la versión sin conexión
    $total_profesores = 30;
    $total_estudiantes = 200;
    $total_usuarios = 50;
    ?>
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

        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    Récord Académico
                </div>
                <div class="card-body">
                    <!-- Campos de búsqueda -->
                    <div class="filter-container">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="searchCedula"><i class="fas fa-id-card filter-icon"></i>Cédula</label>
                                <input type="text" class="form-control" id="searchCedula"
                                    placeholder="Buscar por cédula">
                            </div>
                            <div class="col-md-4">
                                <label for="searchParalelo"><i class="fas fa-columns filter-icon"></i>Paralelo</label>
                                <select class="form-control" id="searchParalelo">
                                    <!-- Opciones predefinidas -->
                                    <option value="">Todos</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <!-- Opciones futuras desde la base de datos -->
                                    <?php 
                                        // Aquí deberás colocar la consulta para obtener los paralelos desde la base de datos
                                        // Ejemplo:
                                        // $query = "SELECT DISTINCT paralelo FROM cursos";
                                        // $result = mysqli_query($conn, $query);
                                        // while ($row = mysqli_fetch_assoc($result)) {
                                        //     echo "<option value='{$row['paralelo']}'>{$row['paralelo']}</option>";
                                        // }
                                        ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="searchNivel"><i class="fas fa-layer-group filter-icon"></i>Nivel</label>
                                <select class="form-control" id="searchNivel">
                                    <!-- Opciones predefinidas -->
                                    <option value="">Todos</option>
                                    <option value="Octavo">Octavo</option>
                                    <option value="Noveno">Noveno</option>
                                    <option value="Décimo">Décimo</option>
                                    <option value="Primero">Primero Bachillerato</option>
                                    <option value="Segundo">Segundo Bachillerato</option>
                                    <option value="Tercero">Tercero Bachillerato</option>
                                    <!-- Opciones futuras desde la base de datos -->
                                    <?php 
                                        // Aquí deberás colocar la consulta para obtener los niveles desde la base de datos
                                        // Ejemplo:
                                        // $query = "SELECT DISTINCT nivel FROM cursos";
                                        // $result = mysqli_query($conn, $query);
                                        // while ($row = mysqli_fetch_assoc($result)) {
                                        //     echo "<option value='{$row['nivel']}'>{$row['nivel']}</option>";
                                        // }
                                        ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="searchSubnivel"><i
                                        class="fas fa-layer-group filter-icon"></i>Subnivel</label>
                                <select class="form-control" id="searchSubnivel">
                                    <!-- Opciones predefinidas -->
                                    <option value="">Todos</option>
                                    <option value="EBG">EBG</option>
                                    <option value="BTI">BTI</option>
                                    <!-- Opciones futuras desde la base de datos -->
                                    <?php 
                                        // Aquí deberás colocar la consulta para obtener los subniveles desde la base de datos
                                        // Ejemplo:
                                        // $query = "SELECT DISTINCT subnivel FROM cursos";
                                        // $result = mysqli_query($conn, $query);
                                        // while ($row = mysqli_fetch_assoc($result)) {
                                        //     echo "<option value='{$row['subnivel']}'>{$row['subnivel']}</option>";
                                        // }
                                        ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="searchNombre"><i class="fas fa-sort-alpha-down filter-icon"></i>Ordenar por
                                    Nombre</label>
                                <input type="text" class="form-control" id="searchNombre"
                                    placeholder="Buscar por nombre">
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de registros académicos -->
                    <div class="section-title">EBG (Octavo a Décimo)</div>
                    <div class="table-container">
                        <table id="tableEBG" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID Estudiante</th>
                                    <th>Nombre Estudiante</th>
                                    <th>Curso</th>
                                    <th>Materia</th>
                                    <th>Calificación</th>
                                    <th>Periodo Académico</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ejemplo de datos de récord académico para EBG -->
                                <tr>
                                    <td>001</td>
                                    <td>Juan Pérez</td>
                                    <td>Noveno A</td>
                                    <td>Matemáticas</td>
                                    <td>8.5</td>
                                    <td>2024-2025</td>
                                </tr>
                                <tr>
                                    <td>002</td>
                                    <td>María López</td>
                                    <td>Octavo B</td>
                                    <td>Lengua y Literatura</td>
                                    <td>9.2</td>
                                    <td>2024-2025</td>
                                </tr>
                                <!-- Puedes repetir este patrón para cada registro de récord académico de EBG -->
                            </tbody>
                        </table>
                    </div>

                    <div class="section-title">BTI (Primero a Tercero de Bachillerato)</div>
                    <div class="table-container">
                        <table id="tableBTI" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID Estudiante</th>
                                    <th>Nombre Estudiante</th>
                                    <th>Curso</th>
                                    <th>Materia</th>
                                    <th>Calificación</th>
                                    <th>Periodo Académico</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ejemplo de datos de récord académico para BTI -->
                                <tr>
                                    <td>003</td>
                                    <td>Carlos Gómez</td>
                                    <td>Segundo B</td>
                                    <td>Física</td>
                                    <td>7.8</td>
                                    <td>2024-2025</td>
                                </tr>
                                <tr>
                                    <td>004</td>
                                    <td>Luisa Martínez</td>
                                    <td>Tercero A</td>
                                    <td>Química</td>
                                    <td>8.9</td>
                                    <td>2024-2025</td>
                                </tr>
                                <!-- Puedes repetir este patrón para cada registro de récord académico de BTI -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Pie de Página -->
    <footer class="text-center mt-4">
        <div class="copyright-container">
            <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
                Zambrano.
                Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Scripts JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>

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

        // Datos temporales para la versión sin conexión
        var totalProfesores = <?php echo $total_profesores; ?>;
        var totalEstudiantes = <?php echo $total_estudiantes; ?>;
        var totalUsuarios = <?php echo $total_usuarios; ?>;

        // Animación de subida para los números de las tarjetas
        animateValue('totalProfesores', 0, totalProfesores, 1500);
        animateValue('totalEstudiantes', 0, totalEstudiantes, 1500);
        animateValue('totalUsuarios', 0, totalUsuarios, 1500);

        // Datos para el gráfico de área apilada
        var datosAreaApilada = {
            labels: ['2020-2021', '2021-2022', '2022-2023', '2023-2024', '2024-2025'],
            datasets: [{
                label: 'Profesores',
                backgroundColor: '#c42021', // Color rojo llamativo
                data: [12, 19, 3, 5, 2],
                stack: 'Stack 1',
            }, {
                label: 'Estudiantes',
                backgroundColor: '#ffeaae', // Color azul oscuro
                data: [3, 5, 12, 15, 8],
                stack: 'Stack 1',
            }, {
                label: 'Usuarios',
                backgroundColor: '#47a025', // Color amarillo
                data: [5, 8, 11, 6, 7],
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
        integrity="sha384-B4gt1jrGC7Jh4Ag8drPmxJ4inVEuIpc2HVVgVVUew12Q5IMve9l/sr42u2Km9GJg" crossorigin="anonymous">
    </script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <!-- Script adicional -->
    <script>
    $(document).ready(function() {
        // Inicializar DataTables para ambas tablas sin opción de búsqueda
        $('#tableEBG').DataTable({
            searching: false
        });
        $('#tableBTI').DataTable({
            searching: false
        });

        // Filtrado personalizado
        $('#searchCedula').on('keyup', function() {
            var tableEBG = $('#tableEBG').DataTable();
            var tableBTI = $('#tableBTI').DataTable();
            tableEBG.columns(0).search(this.value).draw();
            tableBTI.columns(0).search(this.value).draw();
        });

        $('#searchParalelo').on('change', function() {
            var tableEBG = $('#tableEBG').DataTable();
            var tableBTI = $('#tableBTI').DataTable();
            tableEBG.columns(2).search(this.value).draw();
            tableBTI.columns(2).search(this.value).draw();
        });

        $('#searchNivel').on('change', function() {
            var tableEBG = $('#tableEBG').DataTable();
            var tableBTI = $('#tableBTI').DataTable();
            tableEBG.columns(2).search(this.value).draw();
            tableBTI.columns(2).search(this.value).draw();
        });

        $('#searchSubnivel').on('change', function() {
            var tableEBG = $('#tableEBG').DataTable();
            var tableBTI = $('#tableBTI').DataTable();
            tableEBG.columns(2).search(this.value).draw();
            tableBTI.columns(2).search(this.value).draw();
        });

        $('#searchNombre').on('keyup', function() {
            var tableEBG = $('#tableEBG').DataTable();
            var tableBTI = $('#tableBTI').DataTable();
            tableEBG.columns(1).search(this.value).draw();
            tableBTI.columns(1).search(this.value).draw();
        });
    });
    </script>
</body>

</html>