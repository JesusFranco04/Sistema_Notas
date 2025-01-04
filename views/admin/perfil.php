<?php
// Iniciar sesión
session_start();

// Incluir archivo de conexión a la base de datos
include('../../Crud/config.php'); // Ruta de conexión a la base de datos

// Verificar si el usuario está autenticado y tiene el rol adecuado
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    // Si no está autenticado o no tiene el rol adecuado, redirigir a login
    header("Location: ../../login.php");
    exit(); // Evitar que el código siga ejecutándose
}

// Obtener la cédula del usuario autenticado
$cedula_usuario = $_SESSION['cedula']; 
$rol_usuario = $_SESSION['rol']; // Obtener el rol del usuario autenticado

// Inicializar los valores de los filtros. Si no están definidos en $_GET, se asigna un valor vacío.
$tabla_filtro = isset($_GET['tabla']) ? $_GET['tabla'] : '';
$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$id_usuario_filtro = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : '';

// Definir la consulta básica
$query = "SELECT * FROM historial_log";

// Consulta para obtener los valores únicos de la columna 'tabla'
$sql = "SELECT DISTINCT tabla FROM historial_log WHERE tabla IS NOT NULL AND tabla != ''";
$result = $conn->query($sql);

// Si hay resultados, almacenamos las tablas en un array
$tablas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tablas[] = $row['tabla'];
    }
}

// Construir condiciones de filtro
$conditions = [];
$params = [];
$types = "";

// Filtro por tabla
if ($tabla_filtro != '') {
    $conditions[] = "tabla = ?";
    $params[] = $tabla_filtro;
    $types .= "s"; // Tipo string
}

// Filtro por fecha
if ($fecha_filtro != '') {
    $conditions[] = "DATE(fecha_actividad) = ?";
    $params[] = $fecha_filtro;
    $types .= "s"; // Tipo string
}

// Filtro por id_usuario según el rol
if ($rol_usuario === 'Superusuario') {
    if ($id_usuario_filtro != '') {
        $conditions[] = "id_usuario = (SELECT id_usuario FROM usuario WHERE cedula = ?)";
        $params[] = $id_usuario_filtro;
        $types .= "s"; // Tipo string
    }
} else if ($rol_usuario === 'Administrador') {
    // Administrador solo puede ver sus propios registros
    $conditions[] = "id_usuario = (SELECT id_usuario FROM usuario WHERE cedula = ?)";
    $params[] = $cedula_usuario;  // Usamos la cédula del administrador
    $types .= "s"; // Tipo string
}

// Si hay filtros, añadirlos a la consulta
if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Paginación
$resultados_por_pagina = 10; // Cantidad de registros por página
$pagina_actual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1; // Página actual desde el GET
$inicio = ($pagina_actual - 1) * $resultados_por_pagina; // Calcular el inicio de los registros

// Consulta para contar el total de registros con los filtros aplicados
$count_query = "SELECT COUNT(*) AS total FROM historial_log";
if (count($conditions) > 0) {
    $count_query .= " WHERE " . implode(" AND ", $conditions);
}
$count_stmt = $conn->prepare($count_query);

// Vincular los parámetros para contar
if ($types != "") {
    $count_stmt->bind_param($types, ...$params);  // Pasamos los parámetros necesarios
} else {
    // Si no hay tipos, no vinculamos parámetros
    $count_stmt->execute();
}

$count_stmt->execute();
$total_registros = $count_stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $resultados_por_pagina); // Calcular el total de páginas

// Añadir límite a la consulta principal
$query .= " ORDER BY fecha_actividad DESC LIMIT ?, ?";

// Añadir parámetros para la paginación
$params[] = $inicio;
$params[] = $resultados_por_pagina;
$types .= "ii"; // Tipo entero para el inicio y los resultados por página

// Preparar la consulta
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Vincular los parámetros a la consulta
$stmt->bind_param($types, ...$params); // Usar el operador "..." para expandir $params

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

// Verificar si hay resultados
$historial_vacio = ($result->num_rows === 0); // Si no hay registros, marcar como vacío

// Cerrar conexión
$stmt->close();
$count_stmt->close();
$conn->close();
?>






<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet"> <!-- Boxicons -->
    <style>
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f7f9fc;
        margin: 0;
        padding: 0;
    }

    header {
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 40px;
        background-color: #DE112D;
        color: #fff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        border-bottom: 4px solid #B90F2C;
        transition: all 0.3s ease-in-out;
    }

    header:hover {
        background-color: #B90F2C;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    header h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }


    header .btn {
        background-color: #06a660;
        color: #fff;
        padding: 10px 25px;
        font-size: 16px;
        font-weight: 600;
        text-transform: uppercase;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    header .btn:hover {
        background-color: #fff;
        color: #06a660;
        transform: scale(1.05);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    header::before {
        content: '';
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(to right, #DE112D, #B90F2C);
        transform-origin: left;
        transform: scaleX(0);
        transition: transform 0.6s ease-in-out, background 0.6s ease-in-out;
    }

    header:hover::before {
        transform: scaleX(1);
        background: linear-gradient(to right, #DE112D, #B90F2C);
    }

    @media (max-width: 768px) {
        header {
            padding: 15px 25px;
        }

        header h1 {
            font-size: 24px;
        }

        header .btn {
            padding: 8px 20px;
            font-size: 14px;
        }
    }

    @media (max-width: 480px) {
        header {
            padding: 10px 15px;
        }

        header h1 {
            font-size: 20px;
        }

        header .btn {
            padding: 6px 16px;
            font-size: 12px;
        }
    }

    .container {
        width: 85%;
        margin: 30px auto;
    }

    .content {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    h2 {
        font-size: 28px;
        margin-bottom: 20px;
        color: #333;
    }

    .filters form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    .filters .filter-group {
        display: flex;
        flex-direction: column;
        min-width: 150px;
        flex: 1;
    }

    .filters label {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .filters select,
    .filters input,
    .filters button {
        padding: 10px;
        font-size: 14px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    .filters select,
    .filters input {
        width: 200px;
    }

    .filters button {
        background-color: #DE112D;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .filters button:hover {
        background-color: #a10f26;
    }

    .alert {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }

    th,
    td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #DE112D;
        color: #fff;
    }

    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
    }

    .input-group {
        width: 250px;
    }

    .input-group-text {
        background-color: #DE112D;
        color: white;
        border: none;
        cursor: pointer;
    }

    .filters {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .badge-success {
        background-color: #32b54f;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .badge-warning {
        background-color: #ffc107;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 30px;
        gap: 10px;
    }

    .page-item {
        list-style: none;
    }

    .page-link {
        color: #333;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid #ddd;
        padding: 10px 15px;
        border-radius: 6px;
        transition: all 0.3s ease-in-out;
        background-color: #f9f9f9;
        box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .page-link:hover {
        background-color: #DE112D;
        color: #fff;
        border-color: #DE112D;
        box-shadow: 0px 4px 8px rgba(222, 17, 45, 0.3);
        transform: scale(1.05);
    }

    .page-item.active .page-link {
        background-color: #DE112D;
        color: #fff;
        border-color: #DE112D;
        box-shadow: 0px 4px 8px rgba(222, 17, 45, 0.3);
        font-weight: bold;
    }

    .page-item.disabled .page-link {
        background-color: #e9ecef;
        color: #6c757d;
        border-color: #dee2e6;
        pointer-events: none;
    }

    .pagination .dots {
        font-size: 18px;
        color: #333;
        padding: 5px 10px;
        font-weight: 600;
        pointer-events: none;
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
        /* Sombra más pronunciada */
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }
    </style>
</head>

<body>
    <header>
        <h1>Panel Administrativo</h1>
        <a href="http://localhost/sistema_notas/views/admin/index_admin.php" class="btn btn-regresar">Regresar</a>
    </header>

    <div class="container">
        <div class="content">
            <h2>Resumen de Actividades</h2>

            <div class="filters">
                <form method="GET" action="" id="filter-form">
                    <div class="filter-group">
                        <label for="tabla"><i class="bx bx-table"></i> Tabla:</label>
                        <select name="tabla" id="tabla" onchange="this.form.submit()">
                            <option value="" disabled selected>Seleccionar tabla</option>
                            <?php 
                            // Mostrar las tablas obtenidas de la base de datos en el filtro
                            foreach ($tablas as $tabla): 
                            ?>
                            <option value="<?php echo htmlspecialchars($tabla); ?>"
                                <?php echo ($tabla_filtro == $tabla ? 'selected' : ''); ?>>
                                <?php echo htmlspecialchars($tabla); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="fecha"><i class="bx bx-calendar"></i> Fecha de Actividad:</label>
                        <input type="date" name="fecha" id="fecha" value="<?php echo $fecha_filtro; ?>"
                            max="<?= date('Y-m-d') ?>" onchange="this.form.submit()">
                    </div>

                    <?php if ($rol_usuario === 'Superusuario'): ?>
                    <div class="filter-group">
                        <label for="id_usuario"><i class="bx bx-id-card"></i> ID Usuario (Cédula):</label>
                        <div class="input-group">
                            <input type="text" name="id_usuario" id="id_usuario" class="form-control"
                                placeholder="Cédula del usuario" value="<?php echo $id_usuario_filtro; ?>">
                            <button type="submit" class="input-group-text" id="search-btn"><i
                                    class="bx bx-search"></i></button>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Tabla</th>
                            <th>ID Registro</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                            <th>Fecha de Actividad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($historial_vacio): ?>
                        <tr>
                            <td colspan="5">
                                <div class="alert">
                                    No tienes historial de actividades registrado.
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['tabla']); ?></td>
                            <td><?php echo htmlspecialchars($row['id_registro']); ?></td>
                            <td>
                                <?php if ($row['accion'] == 'Creación'): ?>
                                <span class="badge badge-success">Creación</span>
                                <?php elseif ($row['accion'] == 'Modificación'): ?>
                                <span class="badge badge-warning">Modificación</span>
                                <?php else: ?>
                                <span
                                    class="badge badge-secondary"><?php echo htmlspecialchars($row['accion']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_actividad']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center mt-4">
                    <?php if ($pagina_actual > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>
    <script>
    // Hacemos que el formulario se envíe automáticamente al cambiar cualquier filtro
    document.getElementById("tabla").addEventListener("change", function() {
        document.getElementById("filter-form").submit();
    });
    document.getElementById("fecha").addEventListener("change", function() {
        document.getElementById("filter-form").submit();
    });
    // El formulario se enviará solo cuando el usuario presione el botón de búsqueda para el campo de cédula
    document.getElementById("search-btn").addEventListener("click", function(e) {
        e.preventDefault(); // Evitar que el formulario se envíe automáticamente
        document.getElementById("filter-form").submit(); // Enviar el formulario manualmente
    });
    </script>
</body>

</html>