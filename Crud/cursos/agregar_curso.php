<?php
// Define tus variables de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "sistema_gestion"; // Cambia al nombre de tu base de datos real

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} 

// Inicializar variables para almacenar los resultados de las consultas
$resultado_profesor = $resultado_materia = $resultado_nivel = $resultado_paralelo = $resultado_subnivel = $resultado_especialidad = $resultado_jornada = $resultado_periodo = null;

// Consultas SQL para obtener opciones de los selectores
$query_profesor = "SELECT id, nombres FROM profesores";  // Selecciona el ID y el nombre del profesor
$resultado_profesor = mysqli_query($conn, $query_profesor);

$query_materia = "SELECT id, nombre FROM materias";  // Selecciona el ID y el nombre de la materia
$resultado_materia = mysqli_query($conn, $query_materia);

$query_nivel = "SELECT id, nombre FROM niveles";  // Selecciona el ID y el nombre del nivel
$resultado_nivel = mysqli_query($conn, $query_nivel);

$query_paralelo = "SELECT id, nombre FROM paralelos";  // Selecciona el ID y el nombre del paralelo
$resultado_paralelo = mysqli_query($conn, $query_paralelo);

$query_subnivel = "SELECT id, nombre FROM subniveles";  // Selecciona el ID y el nombre del subnivel
$resultado_subnivel = mysqli_query($conn, $query_subnivel);

$query_especialidad = "SELECT id, nombre FROM especialidades";  // Selecciona el ID y el nombre de la especialidad
$resultado_especialidad = mysqli_query($conn, $query_especialidad);

$query_jornada = "SELECT id, nombre FROM jornada";  // Selecciona el ID y el nombre de la jornada
$resultado_jornada = mysqli_query($conn, $query_jornada);

$query_periodo = "SELECT id, ano FROM periodo";  // Selecciona el ID y el año del periodo
$resultado_periodo = mysqli_query($conn, $query_periodo);

if (!$resultado_profesor || !$resultado_materia || !$resultado_nivel || !$resultado_paralelo || !$resultado_subnivel || !$resultado_especialidad || !$resultado_jornada || !$resultado_periodo) {
    die("Error al obtener datos: " . mysqli_error($conn));
}

// Procesamiento del formulario cuando se envía por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $profesor_id = isset($_POST['profesor_id']) ? $_POST['profesor_id'] : null;
    $materia_id = isset($_POST['materia_id']) ? $_POST['materia_id'] : null;
    $nivel_id = isset($_POST['nivel_id']) ? $_POST['nivel_id'] : null;
    $paralelo_id = isset($_POST['paralelo_id']) ? $_POST['paralelo_id'] : null;
    $subnivel_id = isset($_POST['subnivel_id']) ? $_POST['subnivel_id'] : null;
    $especialidad_id = isset($_POST['especialidad_id']) ? $_POST['especialidad_id'] : null;
    $jornada_id = isset($_POST['jornada_id']) ? $_POST['jornada_id'] : null;
    $periodo_id = isset($_POST['periodo_id']) ? $_POST['periodo_id'] : null;
    $fecha_ingreso = isset($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : null;

    // Consulta SQL para insertar datos en la tabla curso
    $query_insert = "INSERT INTO curso (profesor_id, materia_id, nivel_id, paralelo_id, subnivel_id, especialidad_id, jornada_id, periodo_id, fecha_ingreso)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query_insert);
    mysqli_stmt_bind_param($stmt, 'iiiiissss', $profesor_id, $materia_id, $nivel_id, $paralelo_id, $subnivel_id, $especialidad_id, $jornada_id, $periodo_id, $fecha_ingreso);

    if (mysqli_stmt_execute($stmt)) {
        echo "Curso agregado correctamente.";
    } else {
        echo "Error al agregar el curso: " . mysqli_stmt_error($stmt);
    }

    // Cerrar la declaración
    mysqli_stmt_close($stmt);
}

// Cerrar conexión
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Curso</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Tu CSS personalizado -->
    <style>
    .container {
        max-width: 600px;
    }

    .card {
        margin-top: 50px;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Agregar Curso</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="profesor_id">Profesor:</label>
                        <select class="form-control" id="profesor_id" name="profesor_id" required>
                            <?php while ($fila_profesor = mysqli_fetch_assoc($resultado_profesor)): ?>
                            <option value="<?php echo $fila_profesor['id']; ?>"><?php echo $fila_profesor['nombres']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="materia_id">Materia:</label>
                        <select class="form-control" id="materia_id" name="materia_id" required>
                            <?php while ($fila_materia = mysqli_fetch_assoc($resultado_materia)): ?>
                            <option value="<?php echo $fila_materia['id']; ?>"><?php echo $fila_materia['nombre']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nivel_id">Nivel:</label>
                        <select class="form-control" id="nivel_id" name="nivel_id" required>
                            <?php while ($fila_nivel = mysqli_fetch_assoc($resultado_nivel)): ?>
                            <option value="<?php echo $fila_nivel['id']; ?>"><?php echo $fila_nivel['nombre']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="paralelo_id">Paralelo:</label>
                        <select class="form-control" id="paralelo_id" name="paralelo_id" required>
                            <?php while ($fila_paralelo = mysqli_fetch_assoc($resultado_paralelo)): ?>
                            <option value="<?php echo $fila_paralelo['id']; ?>"><?php echo $fila_paralelo['nombre']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subnivel_id">Subnivel:</label>
                        <select class="form-control" id="subnivel_id" name="subnivel_id" required>
                            <?php while ($fila_subnivel = mysqli_fetch_assoc($resultado_subnivel)): ?>
                            <option value="<?php echo $fila_subnivel['id']; ?>"><?php echo $fila_subnivel['nombre']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="especialidad_id">Especialidad:</label>
                        <select class="form-control" id="especialidad_id" name="especialidad_id" required>
                            <?php while ($fila_especialidad = mysqli_fetch_assoc($resultado_especialidad)): ?>
                            <option value="<?php echo $fila_especialidad['id']; ?>">
                                <?php echo $fila_especialidad['nombre']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jornada_id">Jornada:</label>
                        <select class="form-control" id="jornada_id" name="jornada_id" required>
                            <?php while ($fila_jornada = mysqli_fetch_assoc($resultado_jornada)): ?>
                            <option value="<?php echo $fila_jornada['id']; ?>"><?php echo $fila_jornada['nombre']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="periodo_id">Periodo:</label>
                        <select class="form-control" id="periodo_id" name="periodo_id" required>
                            <?php while ($fila_periodo = mysqli_fetch_assoc($resultado_periodo)): ?>
                            <option value="<?php echo $fila_periodo['id']; ?>"><?php echo $fila_periodo['ano']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha_ingreso">Fecha de Ingreso:</label>
                        <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                    <a href="../../views/admin/curso_admin.php" class="btn btn-secondary">Regresar</a>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>