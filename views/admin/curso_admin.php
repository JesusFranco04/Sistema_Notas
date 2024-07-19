<?php
session_start();
// Incluir el archivo de conexión y verificar la conexión
include('../../Crud/config.php'); // Ruta absoluta 

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

// Consulta SQL para obtener los cursos con nombres de las tablas relacionadas
$sql = "
SELECT
    c.id_curso,
    CONCAT(p.nombres, ' ', p.apellidos) AS nombre_profesor,
    m.nombre AS nombre_materia,
    n.nombre AS nombre_nivel,
    pa.nombre AS nombre_paralelo,
    s.nombre AS nombre_subnivel,
    e.nombre AS nombre_especialidad,
    j.nombre AS nombre_jornada,
    ha.año AS año_his_academico,
    c.estado
FROM
    curso c
    LEFT JOIN profesor p ON c.id_profesor = p.id_profesor
    LEFT JOIN materia m ON c.id_materia = m.id_materia
    LEFT JOIN nivel n ON c.id_nivel = n.id_nivel
    LEFT JOIN paralelo pa ON c.id_paralelo = pa.id_paralelo
    LEFT JOIN subnivel s ON c.id_subnivel = s.id_subnivel
    LEFT JOIN especialidad e ON c.id_especialidad = e.id_especialidad
    LEFT JOIN jornada j ON c.id_jornada = j.id_jornada
    LEFT JOIN historial_academico ha ON c.id_his_academico = ha.id_his_academico
";


$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Cursos | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Estilos personalizados -->
    <style>
    /* Estilo para el contenedor de la tabla */
    .table-container {
        max-height: 500px;
        overflow-y: auto;
    }

    /* Estilo para separar los botones de acciones */
    .action-buttons .btn {
        margin-right: 20px;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f0f0f0;
    }

    .container-fluid {
        padding: 20px;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #c42021;
        /* Color de fondo rojo */
        color: white;
        /* Color del texto */
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 15px;
        /* Espacio interno alrededor del contenido del encabezado */
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

    .table tbody .btn-action {
        margin-bottom: 10px;
        display: inline-block;
    }

    .filter-container {
        margin-bottom: 1rem;
    }
    </style>
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>


    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tabla de Cursos</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="searchFecha"><i class="fas fa-calendar-alt filter-icon"></i>Fecha de
                                Creación</label>
                            <input type="date" class="form-control" id="searchFecha" name="fecha"
                                value="<?php echo $fecha; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="searchEstado"><i class="fas fa-filter filter-icon"></i>Estado</label>
                            <select class="form-control" id="searchEstado" name="estado">
                                <option value="">Todos</option>
                                <option value="activo" <?php echo $estado == 'activo' ? 'selected' : ''; ?>>Activos
                                </option>
                                <option value="inactivo" <?php echo $estado == 'inactivo' ? 'selected' : ''; ?>>
                                    Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                    <div class="mb-4 mt-3">
                        <div class="row justify-content-start action-buttons">
                            <div class="col-auto">
                                <a href="http://localhost/sistema_notas/Crud/admin/curso/agregar_curso.php"
                                    class="btn btn-primary">Agregar
                                    Curso</a>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-info" data-toggle="modal"
                                    data-target="#modalInstrucciones1">Ver Manual de Uso</button>
                            </div>
                            <div class="col-auto">
                                <a href="reporte_usuario.php" class="btn btn-success">Generar reportes</a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive table-container">
                    <table class="table table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profesor</th>
                                <th>Materia</th>
                                <th>Nivel</th>
                                <th>Paralelo</th>
                                <th>Subnivel</th>
                                <th>Especialidad</th>
                                <th>Jornada</th>
                                <th>Historial Académico</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($fila = mysqli_fetch_assoc($resultado)) {
                                ?>
                            <tr>
                                <td><?php echo $fila['id_curso']; ?></td>
                                <td><?php echo $fila['nombre_profesor']; ?></td>
                                <td><?php echo $fila['nombre_materia']; ?></td>
                                <td><?php echo $fila['nombre_nivel']; ?></td>
                                <td><?php echo $fila['nombre_paralelo']; ?></td>
                                <td><?php echo $fila['nombre_subnivel']; ?></td>
                                <td><?php echo $fila['nombre_especialidad']; ?></td>
                                <td><?php echo $fila['nombre_jornada']; ?></td>
                                <td><?php echo $fila['año_his_academico']; ?></td>
                                <td><?php echo $fila['estado']; ?></td>
                                <td>
                                    <a href="http://localhost/sistema_notas/Crud/admin/curso/editar_curso.php?id=<?php echo $fila['id_curso']; ?>"
                                        class="btn btn-sm btn-primary">Editar</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- SB Admin 2 JS-->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>

</html>
