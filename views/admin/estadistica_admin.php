<?php
// Verificar si ya se ha iniciado la sesión
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
$queryProfesores = "SELECT COUNT(*) as total FROM profesor p 
                    INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
                    WHERE u.estado = 'A'";
$queryEstudiantes = "SELECT COUNT(*) as total FROM estudiante 
                     WHERE estado = 'A'";
$queryUsuarios = "SELECT COUNT(*) as total FROM usuario 
                  WHERE estado = 'A'";

// Ejecutar consultas
$resultProfesores = $conn->query($queryProfesores);
$resultEstudiantes = $conn->query($queryEstudiantes);
$resultUsuarios = $conn->query($queryUsuarios);

// Obtener resultados
$total_profesores = $resultProfesores->fetch_assoc()['total'];
$total_estudiantes = $resultEstudiantes->fetch_assoc()['total'];
$total_usuarios = $resultUsuarios->fetch_assoc()['total'];

// Inicializar variables para mensajes
$mensaje = [];
$mensaje_tipo = '';

// Consulta para obtener los últimos 10 años lectivos
$queryEstadisticas = "
    SELECT 
        a.año, 
        a.estado,
        COUNT(DISTINCT CASE 
            WHEN p.id_profesor IS NOT NULL AND c.id_his_academico = a.id_his_academico THEN p.id_profesor 
        END) AS profesores,
        COUNT(DISTINCT e.id_estudiante) AS estudiantes
    FROM historial_academico a
    LEFT JOIN curso c ON a.id_his_academico = c.id_his_academico
    LEFT JOIN profesor p ON c.id_profesor = p.id_profesor
    LEFT JOIN estudiante e ON a.id_his_academico = e.id_his_academico
    GROUP BY a.año, a.estado
    ORDER BY a.año ASC 
    LIMIT 10"; // Ordenar los años del más antiguo al más reciente y limita para que solamente se pueda ver 10 registros nomas 

// Consulta independiente para contar usuarios activos
$queryUsuarios = "SELECT COUNT(*) as total FROM usuario WHERE estado = 'A'";

// Ejecutar ambas consultas
$resultEstadisticas = $conn->query($queryEstadisticas);
$resultUsuarios = $conn->query($queryUsuarios);

// Procesar resultados
$totalUsuarios = $resultUsuarios->fetch_assoc()['total'];

$labels = [];
$dataProfesores = [];
$dataEstudiantes = [];
$dataUsuarios = [];

// Validar que la consulta de estadísticas tenga resultados
if ($resultEstadisticas && $resultEstadisticas->num_rows > 0) {
    while ($row = $resultEstadisticas->fetch_assoc()) {
        // Mostrar solo años con estado 'A' (Activo) o 'I' (Inactivo)
        if (in_array($row['estado'], ['A', 'I'])) {
            $labels[] = $row['año'];
            $dataProfesores[] = $row['profesores'];
            $dataEstudiantes[] = $row['estudiantes'];
            $dataUsuarios[] = $totalUsuarios; // Agregar el total de usuarios activos
        }
    }

    // Validar si no se encontraron años activos
    if (empty($labels)) {
        $mensaje[] = "No hay años lectivos registrados. Por favor, registre un año lectivo para continuar.";
        $mensaje_tipo = 'advertencia';
    }
} else {
    // Si no hay datos disponibles, mostrar un mensaje de error
    $labels[] = "-";
    $dataProfesores[] = 0;
    $dataEstudiantes[] = 0;
    $dataUsuarios[] = 0;
    $mensaje[] = "No se encontraron registros de años lectivos. Por favor, registre uno nuevo.";
    $mensaje_tipo = 'error';
}

// Consulta para obtener los mejores estudiantes
$sql = "
WITH mejores_estudiantes AS (
    SELECT 
        e.nombres AS Nombre,
        e.apellidos AS Apellido,
        sn.nombre AS Subnivel,
        n.nombre AS Nivel,
        c.id_curso AS Curso,
        cal.nota_final AS NotaFinal,
        ROW_NUMBER() OVER (
            PARTITION BY sn.id_subnivel, n.id_nivel, c.id_curso
            ORDER BY cal.nota_final DESC
        ) AS posicion
    FROM 
        estudiante e
    INNER JOIN calificacion cal ON e.id_estudiante = cal.id_estudiante
    INNER JOIN curso c ON cal.id_curso = c.id_curso
    INNER JOIN subnivel sn ON c.id_subnivel = sn.id_subnivel
    INNER JOIN nivel n ON c.id_nivel = n.id_nivel
    WHERE 
        cal.nota_final BETWEEN 9 AND 10
        AND e.estado = 'A'
        AND cal.estado_calificacion = 'A'
)
SELECT * 
FROM mejores_estudiantes
WHERE posicion <= 2;
";

$result = $conn->query($sql);

// Verificación de errores en la consulta SQL
if ($result === false) {
    echo "Error en la consulta: " . $conn->error;
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas | Sistema de Gestión UEBF</title>
    <!-- Estilos y librerías externas -->
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css">
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
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(to bottom right, #f9f9fb, #e6e6f2);
        margin: 0;
        padding: 0;
        color: #333;
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
        color: #08185e;
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
        color: #08185e;
        /* Color del texto */
    }

    .border-left-primary {
        border-left: 5px solid #B90F2C;
        /* Rojo llamativo */
    }

    .border-left-success {
        border-left: 5px solid #32b54f;
        /* Azul oscuro */
    }

    .border-left-info {
        border-left: 5px solid #32b54f;
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
        background-color: #B90F2C;
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

    .container {
        width: 96%;
        max-width: 1200px;
        margin: 20px auto;
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .header {
        background: linear-gradient(to right, #B90F2C, #06a660);
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    h1 {
        margin: 0;
        font-size: 2.5rem;
    }

    p.subtitle {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 300;
    }

    .filter-bar {
        margin: 20px 0;
        display: flex;
        gap: 10px;
    }

    .filter-bar input {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        flex-grow: 1;
    }

    .filter-bar button {
        background: #06a660;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    .table-wrapper {
        overflow-y: auto;
        max-height: 400px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th,
    table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    table th {
        background-color: #B90F2C;
        color: white;
    }

    table tbody tr:nth-child(even) {
        background-color: #f8f8fa;
    }

    table tbody tr:hover {
        background-color: #fbe8eb;
    }

    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
    }

    .gold {
        background-color: #FFD700;
    }

    .silver {
        background-color: #C0C0C0;
    }

    .bronze {
        background-color: #CD7F32;
    }

    .alert {
        margin-top: 20px;
        padding: 15px 20px;
        /* Más espacio alrededor del mensaje */
        font-size: 16px;
        font-weight: bold;
        border-radius: 8px;
        /* Bordes más redondeados */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        /* Sombra sutil */
        transition: transform 0.3s ease, opacity 0.3s ease;
        /* Transición suave */
    }

    .alert-success {
        background-color: #d4edda;
        /* Verde claro para éxito */
        color: #155724;
        /* Color de texto verde oscuro */
        border-left: 5px solid #28a745;
        /* Línea izquierda verde */
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 5px solid #dc3545;
        /* Línea izquierda roja */
    }

    .alert-warning {
        background-color: #fff3cd;
        /* Amarillo claro para advertencias */
        color: #856404;
        /* Color de texto amarillo oscuro */
        border-left: 5px solid #ffc107;
        /* Línea izquierda amarilla */
    }

    /* Efecto al pasar el ratón */
    .alert:hover {
        transform: translateY(-5px);
        /* Le da un pequeño levantamiento */
        opacity: 0.9;
        /* Hace que se vea un poco más sutil al pasar el ratón */
    }

    footer {
        background-color: white;
        /* Color de fondo blanco */
        color: #737373;
        /* Color del texto en gris oscuro */
        text-align: center;
        /* Centrar el texto */
        padding: 20px 0;
        /* Espaciado interno vertical */
        width: 100%;
        /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
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

        <div class="alert-container">
            <?php if ($mensaje): ?>
            <div
                class="alert alert-<?php echo $mensaje_tipo === 'exito' ? 'success' : ($mensaje_tipo === 'error' ? 'error' : 'warning'); ?>">
                <?php echo implode('<br>', $mensaje); ?>
            </div>
            <?php endif; ?>

            <!-- Contenedor para el gráfico de área apilada -->
            <div class="chart-container">
                <canvas id="graficoAreaApilada"></canvas>
            </div>
        </div>

        <!-- Línea horizontal -->
        <hr style="margin-top: 1; margin-bottom: 20px;">

        <div class="container">
            <div class="header">
                <div>
                    <h1>Récord Académico</h1>
                    <p class="subtitle">¿Quieres conocer los mejores estudiantes de tu plantel de EBG y BTI?</p>
                </div>
                <div>
                    <i class='bx bxs-trophy' style='font-size: 3rem; color: #FFD700;'></i>
                </div>
            </div>
            <div class="filter-bar">
                <input type="text" id="search" placeholder="Buscar por nombre o nivel"
                    aria-label="Buscar por nombre o nivel">
                <button onclick="filterTable()">Buscar</button>
            </div>
            <div class="table-wrapper">
                <table id="recordTable">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Subnivel</th>
                            <th>Nivel</th>
                            <th>Curso</th>
                            <th>Nota Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                // Verificar si hay resultados
                if ($result->num_rows > 0):
                    // Agrupar los estudiantes por nivel
                    $students_by_level = [];
                    while ($row = $result->fetch_assoc()) {
                        $students_by_level[$row['Nivel']][] = $row;
                    }

                // Iterar sobre los niveles y asignar las medallas
                foreach ($students_by_level as $nivel => $students) {
                    // Ordenamos por nota en orden descendente
                    usort($students, function($a, $b) {
                        return $b['NotaFinal'] - $a['NotaFinal'];
                    });

                    // Solo mostrar los dos mejores estudiantes por nivel
                    $students = array_slice($students, 0, 2);

                    // Asignar medallas
                    $counter = 0;
                    foreach ($students as $student) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($student['Nombre']);
                        // Asignar medalla de oro al primer puesto
                        if ($counter == 0) echo " <span class='badge gold'>🥇</span>";
                        // Asignar medalla de plata al segundo puesto
                        elseif ($counter == 1) echo " <span class='badge silver'>🥈</span>";
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($student['Apellido']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['Subnivel']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['Nivel']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['Curso']) . "</td>";
                        echo "<td>" . htmlspecialchars(number_format($student['NotaFinal'], 2)) . "</td>";
                        echo "</tr>";
                        $counter++;
                    }
                }
                else: ?>
                        <tr>
                            <td colspan="6">No se encontraron registros con el criterio buscado.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Alerta de error en caso de no cumplir el criterio de búsqueda -->
            <?php if ($result->num_rows > 0 && empty($searchResults)): ?>
            <div id="alert" class="alert-error" style="display: none;">
                No se encontraron registros con el criterio buscado.
            </div>
            <?php endif; ?>

            <!-- Alerta de error cuando la base de datos está vacía -->
            <?php if ($result->num_rows == 0): ?>
            <div id="alert" class="alert-error"
                style="display: block; color: red; text-align: center; margin-top: 1rem;">
                Actualmente no hay información disponible para mostrar.
            </div>
            <?php endif; ?>
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

        // Datos para el gráfico de área apilada desde PHP
        var datosAreaApilada = {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                    label: 'Profesores',
                    backgroundColor: '#c70e24', // Color rojo llamativo
                    data: <?php echo json_encode($dataProfesores); ?>,
                    stack: 'Stack 1',
                },
                {
                    label: 'Estudiantes',
                    backgroundColor: '#147c20', // Color verde
                    data: <?php echo json_encode($dataEstudiantes); ?>,
                    stack: 'Stack 1',
                },
                {
                    label: 'Usuarios',
                    backgroundColor: '#1137c1', // Color azul oscuro
                    data: <?php echo json_encode($dataUsuarios); ?>,
                    stack: 'Stack 1',
                }
            ]
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
                        // Mantener la animación con Anime.js
                        anime({
                            targets: '#graficoAreaApilada',
                            opacity: 1,
                            duration: 1000,
                            easing: 'easeOutQuad'
                        });
                    }
                }
            }
        };

        // Inicializar el gráfico de área apilada
        var ctxAreaApilada = document.getElementById('graficoAreaApilada').getContext('2d');
        new Chart(ctxAreaApilada, configAreaApilada);
    });


    function filterTable() {
        const input = document.getElementById('search').value.toLowerCase(); // Obtener el texto del filtro
        const rows = document.querySelectorAll('#recordTable tbody tr'); // Seleccionar todas las filas de la tabla
        let matchFound = false; // Variable para verificar si se encontraron coincidencias

        // Función para normalizar el texto (eliminar tildes)
        function normalizeText(text) {
            return text.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        rows.forEach(row => {
            const cells = row.querySelectorAll('td'); // Seleccionar las celdas de la fila

            // Obtener solo las celdas relevantes: Nombre (índice 0), Apellido (índice 1), Nivel (índice 3)
            const nameCell = normalizeText(cells[0].textContent); // Nombre
            const surnameCell = normalizeText(cells[1].textContent); // Apellido
            const levelCell = normalizeText(cells[3].textContent); // Nivel

            // Verificar si el texto de búsqueda está en alguna de las celdas relevantes
            const match = nameCell.includes(input) || surnameCell.includes(input) || levelCell.includes(
                input);

            row.style.display = match ? '' : 'none'; // Mostrar o ocultar la fila según el filtro

            if (match) matchFound = true; // Si se encuentra una coincidencia, marcar matchFound como true
        });

        // Mostrar el mensaje si no se encuentran coincidencias
        const alert = document.getElementById('alert');
        if (!matchFound) {
            alert.style.display = 'block'; // Mostrar el mensaje si no se encuentran registros
        } else {
            alert.style.display = 'none'; // Ocultar el mensaje si se encuentran coincidencias
        }
    }
    </script>
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>

</html>


<?php
// Cerrar la conexión
$conn->close();
?>