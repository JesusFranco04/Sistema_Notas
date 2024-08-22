<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta a tu archivo de configuración

// Obtener valores de filtros
$materia = isset($_GET['materia']) ? $_GET['materia'] : '';
$nivel = isset($_GET['nivel']) ? $_GET['nivel'] : '';
$anioLectivo = isset($_GET['anioLectivo']) ? $_GET['anioLectivo'] : '';
$cedula = isset($_GET['cedula']) ? $_GET['cedula'] : '';
$curso = isset($_GET['curso']) ? $_GET['curso'] : ''; // Definición de $curso

// Consultar las materias
$materiasQuery = "SELECT nombre FROM materia WHERE estado = 'A'";
$materiasResult = $conn->query($materiasQuery);

// Consultar los niveles
$nivelesQuery = "SELECT nombre FROM nivel WHERE estado = 'A'";
$nivelesResult = $conn->query($nivelesQuery);

// Obtener años lectivos para el filtro
$anioLectivoQuery = "SELECT DISTINCT año FROM historial_academico WHERE estado = 'A'";
$anioLectivoResult = $conn->query($anioLectivoQuery);

// Consultar los cursos
$cursosQuery = "SELECT id_curso FROM curso WHERE estado = 'A'";
$cursosResult = $conn->query($cursosQuery);

// Inicializar variables para la consulta de resultados
$result = null;

// Construir la consulta SQL para los resultados solo si se han aplicado filtros
if ($materia || $nivel || $anioLectivo || $curso || $cedula) {
$sql = "SELECT c.id_curso, r.id_estudiante, e.nombres AS estudiante_nombre, e.apellidos AS estudiante_apellido,
        r.nota1_primer_parcial, r.nota2_primer_parcial, r.examen_primer_parcial,
        r.nota1_segundo_parcial, r.nota2_segundo_parcial, r.examen_segundo_parcial,
        cal.promedio_primer_quimestre, cal.promedio_segundo_quimestre, cal.nota_final, cal.estado_calificacion
        FROM registro_nota r
        INNER JOIN calificacion cal ON r.id_estudiante = cal.id_estudiante AND r.id_curso = cal.id_curso 
            AND r.id_materia = cal.id_materia AND r.id_his_academico = cal.id_his_academico
        INNER JOIN curso c ON r.id_curso = c.id_curso
        INNER JOIN estudiante e ON r.id_estudiante = e.id_estudiante
        WHERE c.estado = 'A'";

// Aplicar los filtros
if ($materia) {
    $sql .= " AND c.id_materia = (SELECT id_materia FROM materia WHERE nombre = '$materia' AND estado = 'A')";
}
if ($nivel) {
    $sql .= " AND c.id_nivel = (SELECT id_nivel FROM nivel WHERE nombre = '$nivel' AND estado = 'A')";
}
if ($anioLectivo) {
    $sql .= " AND c.id_his_academico = (SELECT id_his_academico FROM historial_academico WHERE año = '$anioLectivo' AND estado = 'A')";
}
if ($curso) {
    $sql .= " AND c.id_curso = '$curso'";
}
if ($cedula) {
    $sql .= " AND e.cedula = '$cedula'";
}

// Ejecutar consulta
$result = $conn->query($sql);

// Verificar si la consulta tuvo éxito
if (!$result) {
    die("Error en la consulta: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Calificaciones | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
    html, body {
        height: 100%;
        margin: 0;
        display: flex;
        flex-direction: column;
    }
    body {
        background-color: #f0f4f7;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        flex-direction: column;
    }
    .container-fluid {
        flex: 1;
        margin-top: 40px;
        padding: 20px;
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .card-header {
        background-color: #E62433;
        color: #fff;
        padding: 10px;
        border-radius: 60px 60px 60 60;
        margin-bottom: 20px;
    }
    h2 {
        margin: 0;
        font-weight: 600;
        font-size: 1.75rem;
    }
    .filters-container {
        display: flex;
        flex-wrap: nowrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    .filters-container .form-group {
        flex: 1;
        min-width: 150px;
        margin-bottom: 0;
    }
    .filters-container .form-control {
        width: 100%;
    }
    .input-group .form-control {
        border-radius: 6px 0 0 6px;
    }
    .input-group-append .btn {
        border-radius: 0 6px 6px 0;
    }
    .search-bar-container {
        margin-bottom: 20px;
    }
    .table-container {
        max-height: 600px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid #dcdcdc;
        border-radius: 5px;
    }
    table {
        width: 100%;
        min-width: 1200px;
        border-collapse: collapse;
    }
    th, td {
        text-align: center;
        vertical-align: middle;
        padding: 12px;
        border: 1px solid #dcdcdc;
    }
    th {
        background-color: #E62433;
        color: #fff;
        font-weight: 600;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:nth-child(odd) {
        background-color: #ffffff;
    }
    footer {
        background-color: white;
        color: #737373;
        text-align: center;
        padding: 20px 0;
        width: 100%;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }
    footer p {
        margin: 0;
    }
</style>    
</head>
<body>
    <?php include_once 'navbar_admin.php'; ?>
    <div class="container-fluid">
        <div class="card-header">
            <h2>Gestión de Calificaciones</h2>
        </div>
        <form method="GET" action="">
            <div class="filters-container">
                <div class="form-group">
                    <label for="materia">Materia:</label>
                    <select id="materia" name="materia" class="form-control">
                        <option value="">Selecciona Materia</option>
                        <?php
                        while ($row = $materiasResult->fetch_assoc()) {
                            echo "<option value=\"" . htmlspecialchars($row['nombre']) . "\" " . ($materia == $row['nombre'] ? 'selected' : '') . ">" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nivel">Nivel:</label>
                    <select id="nivel" name="nivel" class="form-control">
                        <option value="">Selecciona Nivel</option>
                        <?php
                        while ($row = $nivelesResult->fetch_assoc()) {
                            echo "<option value=\"" . htmlspecialchars($row['nombre']) . "\" " . ($nivel == $row['nombre'] ? 'selected' : '') . ">" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="curso">Curso:</label>
                    <select id="curso" name="curso" class="form-control">
                        <option value="">Selecciona Curso</option>
                        <?php
                        while ($row = $cursosResult->fetch_assoc()) {
                            echo "<option value=\"" . htmlspecialchars($row['id_curso']) . "\" " . ($curso == $row['id_curso'] ? 'selected' : '') . ">" . htmlspecialchars($row['id_curso']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="anioLectivo">Año Lectivo:</label>
                    <select id="anioLectivo" name="anioLectivo" class="form-control">
                        <option value="">Selecciona Año Lectivo</option>
                        <?php
                        while ($row = $anioLectivoResult->fetch_assoc()) {
                            echo "<option value=\"" . htmlspecialchars($row['año']) . "\" " . ($anioLectivo == $row['año'] ? 'selected' : '') . ">" . htmlspecialchars($row['año']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="search-bar-container">
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" id="cedula" name="cedula" class="form-control" value="<?php echo htmlspecialchars($cedula); ?>" placeholder="Cédula Estudiante">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Estudiante</th>
                        <th>Nota 1er Parcial</th>
                        <th>Nota 2do Parcial</th>
                        <th>Examen 1er Parcial</th>
                        <th>Nota 1er Parcial</th>
                        <th>Nota 2do Parcial</th>
                        <th>Examen 2do Parcial</th>
                        <th>Promedio 1er Quimestre</th>
                        <th>Promedio 2do Quimestre</th>
                        <th>Nota Final</th>
                        <th>Estado Calificación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id_curso']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['estudiante_nombre']) . " " . htmlspecialchars($row['estudiante_apellido']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota1_primer_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota2_primer_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['examen_primer_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota1_segundo_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota2_segundo_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['examen_segundo_parcial']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['promedio_primer_quimestre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['promedio_segundo_quimestre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nota_final']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['estado_calificacion']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php elseif ($result): ?>
        <p>No se encontraron registros con los filtros aplicados.</p>
        <?php endif; ?>
    </div>
</div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
    </footer>

    <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- SB Admin 2 JS -->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>
</html>
