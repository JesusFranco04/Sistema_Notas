<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include("../../config.php");

// Inicializar el mensaje
$mensaje = '';
$mensaje_tipo = '';

// Obtener los datos necesarios para los filtros
$query_niveles = "SELECT id_nivel, nombre FROM nivel WHERE estado = 'A'";
$result_niveles = $conn->query($query_niveles);

$query_paralelos = "SELECT id_paralelo, nombre FROM paralelo WHERE estado = 'A'";
$result_paralelos = $conn->query($query_paralelos);

$query_jornadas = "SELECT id_jornada, nombre FROM jornada WHERE estado = 'A'";
$result_jornadas = $conn->query($query_jornadas);

$query_historiales = "SELECT id_his_academico, año FROM historial_academico WHERE estado = 'A'";
$result_historiales = $conn->query($query_historiales);

// Procesar el formulario de filtro
$filters = [];
$filter_query = '';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['nivel']) && $_GET['nivel'] != '') {
        $filters['e.id_nivel'] = $_GET['nivel'];
    }
    if (isset($_GET['paralelo']) && $_GET['paralelo'] != '') {
        $filters['e.id_paralelo'] = $_GET['paralelo'];
    }
    if (isset($_GET['jornada']) && $_GET['jornada'] != '') {
        $filters['e.id_jornada'] = $_GET['jornada'];
    }
    if (isset($_GET['historial_academico']) && $_GET['historial_academico'] != '') {
        $filters['e.id_his_academico'] = $_GET['historial_academico'];
    }

    if (!empty($filters)) {
        $filter_clauses = [];
        foreach ($filters as $key => $value) {
            $filter_clauses[] = "$key = '$value'";
        }
        $filter_query = "WHERE " . implode(' AND ', $filter_clauses);
    }
}

// Obtener la lista de estudiantes según los filtros aplicados
$query_estudiantes = "SELECT e.id_estudiante, e.nombres, e.apellidos, e.cedula, p.nombres AS nombre_padre, p.apellidos AS apellido_padre, p.cedula AS cedula_padre
                      FROM estudiante e
                      LEFT JOIN padre_x_estudiante pxe ON e.id_estudiante = pxe.id_estudiante
                      LEFT JOIN padre p ON pxe.id_padre = p.id_padre
                      $filter_query
                      ORDER BY e.apellidos";
$result_estudiantes = $conn->query($query_estudiantes);

// Obtener la lista de padres
$query_padres = "SELECT id_padre, nombres, apellidos FROM padre ORDER BY apellidos";
$result_padres = $conn->query($query_padres);

// Procesar el formulario de enlace
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_estudiante = $_POST['id_estudiante'];
    $id_padre = $_POST['id_padre'];

    // Verificar si ya existe una relación
    $query_verificar = "SELECT * FROM padre_x_estudiante WHERE id_estudiante = ? AND id_padre = ?";
    $stmt_verificar = $conn->prepare($query_verificar);
    $stmt_verificar->bind_param("ii", $id_estudiante, $id_padre);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();

    if ($result_verificar->num_rows > 0) {
        $mensaje = "Esta relación ya existe.";
        $mensaje_tipo = 'error';
    } else {
        // Insertar la relación
        $query_insertar = "INSERT INTO padre_x_estudiante (id_estudiante, id_padre) VALUES (?, ?)";
        $stmt_insertar = $conn->prepare($query_insertar);
        $stmt_insertar->bind_param("ii", $id_estudiante, $id_padre);
        if ($stmt_insertar->execute()) {
            $mensaje = "Relación creada exitosamente.";
            $mensaje_tipo = 'exito';
        } else {
            $mensaje = "Error al crear la relación: " . $stmt_insertar->error;
            $mensaje_tipo = 'error';
        }
    }
}

// Obtener las relaciones existentes
$query_relaciones = "SELECT pxe.id_padre, pxe.id_estudiante, e.nombres AS nombre_estudiante, e.apellidos AS apellido_estudiante, e.cedula AS cedula_estudiante, p.nombres AS nombre_padre, p.apellidos AS apellido_padre, p.cedula AS cedula_padre
                     FROM padre_x_estudiante pxe
                     JOIN estudiante e ON pxe.id_estudiante = e.id_estudiante
                     JOIN padre p ON pxe.id_padre = p.id_padre
                     ORDER BY e.apellidos, p.apellidos";
$result_relaciones = $conn->query($query_relaciones);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinculación Familiar Académica | Sistema de Gestión UEBF</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background: #ffffff;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            background-color: #E62433;
            color: #ffffff;
        }
        .btn-custom:hover {
            background-color: #f83b4a;
            color: #ffffff;
        }
        .form-label {
            font-weight: bold;
        }
        .icon {
            font-size: 1.5rem;
            vertical-align: middle;
            margin-right: 0.5rem;
        }
        .table-container {
            overflow: auto;
            max-height: 400px;
        }
        .table th, .table td {
            text-align: center;
        }
        .table-striped tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }
        .alert {
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
        .card {
            margin-bottom: 2rem;
        }
        .card-header {
            background-color: #E62433; /* Fondo rojo oscuro para el encabezado de la tarjeta */
            color: #fff; /* Color del texto del encabezado */
            border-bottom: 1px solid #fff; /* Línea divisoria blanca */
            border-radius: 8px 8px 0 0; /* Redondeo solo en las esquinas superiores */
        }
    </style>
</head>

<body>
    <?php include_once('C:/xampp/htdocs/Sistema_Notas/views/admin/navbar_admin.php'); ?>
    
    <div class="container-fluid">
        <div class="container">
                <div class="card-header">
                    <h5 class="mb-0">Vinculación Familiar Académica</h5>
                </div>
                <div class="card-body">
                    <h2 class="mb-4">
                        <i class='bx bx-link icon'></i> Enlazar Estudiantes con Padres
                    </h2>

        <form class="mb-4" method="GET" action="">
            <div class="row mb-3">
            	<div class="col-md-3">
                    <label for="nivel" class="form-label">Nivel</label>
                    <select id="nivel" name="nivel" class="form-select">
                        <option value="">Selecciona un nivel</option>
                        <?php while ($row = $result_niveles->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_nivel']; ?>"
                            <?php echo (isset($_GET['nivel']) && $_GET['nivel'] == $row['id_nivel']) ? 'selected' : ''; ?>>
                            <?php echo $row['nombre']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="paralelo" class="form-label">Paralelo</label>
                    <select id="paralelo" name="paralelo" class="form-select">
                        <option value="">Selecciona un paralelo</option>
                        <?php while ($row = $result_paralelos->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_paralelo']; ?>"
                            <?php echo (isset($_GET['paralelo']) && $_GET['paralelo'] == $row['id_paralelo']) ? 'selected' : ''; ?>>
                            <?php echo $row['nombre']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="jornada" class="form-label">Jornada</label>
                    <select id="jornada" name="jornada" class="form-select">
                        <option value="">Selecciona una jornada</option>
                        <?php while ($row = $result_jornadas->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_jornada']; ?>"
                            <?php echo (isset($_GET['jornada']) && $_GET['jornada'] == $row['id_jornada']) ? 'selected' : ''; ?>>
                            <?php echo $row['nombre']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="historial_academico" class="form-label">Historial Académico</label>
                    <select id="historial_academico" name="historial_academico" class="form-select">
                        <option value="">Selecciona un año académico</option>
                        <?php while ($row = $result_historiales->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_his_academico']; ?>"
                            <?php echo (isset($_GET['historial_academico']) && $_GET['historial_academico'] == $row['id_his_academico']) ? 'selected' : ''; ?>>
                            <?php echo $row['año']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-custom">
                <i class='bx bx-filter icon'></i> Aplicar Filtros
            </button>
        </form>

        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $mensaje_tipo == 'exito' ? 'success' : 'danger'; ?>">
            <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>

        <h3 class="mb-4">
        	<i class='bx bx-list-check icon'></i> Lista de Estudiantes
        </h3>
        <div class="table-container">
            <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Cédula Estudiante</th>
                    <th>Padre</th>
                    <th>Cédula Padre</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_relaciones->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nombre_estudiante'] . ' ' . $row['apellido_estudiante']; ?></td>
                    <td><?php echo $row['cedula_estudiante']; ?></td>
                    <td><?php echo $row['nombre_padre'] . ' ' . $row['apellido_padre']; ?></td>
                    <td><?php echo $row['cedula_padre']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

        <h3 class="mb-4">
        	<i class='bx bx-male-female icon'></i> Relaciones Estudiante-Padre
        </h3>
        <form method="POST" action="">
            <div class="row mb-3">
            	<div class="col-md-6">
                    <label for="id_estudiante" class="form-label">Estudiante</label>
                    <select id="id_estudiante" name="id_estudiante" class="form-select" required>
                        <option value="">Selecciona un estudiante</option>
                        <?php while ($row = $result_estudiantes->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_estudiante']; ?>">
                                <?php echo $row['apellidos'] . ' ' . $row['nombres']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="id_padre" class="form-label">Padre</label>
                    <select id="id_padre" name="id_padre" class="form-select" required>
                        <option value="">Selecciona un padre</option>
                        <?php while ($row = $result_padres->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_padre']; ?>">
                            	<?php echo $row['apellidos'] . ' ' . $row['nombres']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-custom">
            	<i class='bx bx-save icon'></i> Guardar
            </button>
        </form>
    </div>
</div>
</div>
</div>

<footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
</footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
        <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- SB Admin 2 JS -->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>

</html>