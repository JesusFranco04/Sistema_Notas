<?php
session_start();
include '../../Crud/config.php';
date_default_timezone_set('America/Guayaquil');

// Verificar si el usuario ha iniciado sesión y si su rol es "Administrador" o "Superusuario"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Función para obtener los períodos académicos
function obtenerPeriodos($conn) {
    $sql = "SELECT id_periodo, nombre, estado FROM periodo_academico";
    return $conn->query($sql);
}

// Función para verificar el éxito de una consulta
function verificarConsulta($resultado, $conn, $mensaje) {
    if (!$resultado) {
        die($mensaje . " " . $conn->error);
    }
}

// Obtener los períodos académicos
$result_periodos = obtenerPeriodos($conn);
verificarConsulta($result_periodos, $conn, "Error en la consulta de períodos");

// Obtener los años lectivos activos
$sql_years = "SELECT id_his_academico, año FROM historial_academico WHERE estado = 'A'";
$result_years = $conn->query($sql_years);
verificarConsulta($result_years, $conn, "Error en la consulta de años lectivos");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Administración de Ciclos Académicos | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f0f0;
        color: #333;
    }

    .container {
        margin-top: 30px;
    }

    .section {
        background: #fff;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h1, h2, h3 {
        color: #d32f2f; /* Rojo intenso */
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #E62433; /* Rojo claro para encabezados */
        color: white; /* Color de texto rojo oscuro */
        text-align: center; /* Centramos el texto en el encabezado */
    }

    td {
        text-align: center; /* Centramos el texto en las celdas */
    }

    .btn-primary {
        background-color: #E62433; /* Rojo intenso */
        border-color: #d32f2f;
        margin-top: 10px;
    }

    .btn-primary:hover {
        background-color: #E62433; /* Rojo más oscuro para hover */
        border-color: #c62828;
    }

    .alert {
        margin-top: 20px;
        border-radius: 5px;
    }

    .alert-success {
        background-color: #d4edda; /* Verde claro para éxito */
        color: #155724; /* Color de texto verde oscuro */
    }

    .alert-danger {
        background-color: #f8d7da; /* Rojo claro para errores */
        color: #721c24; /* Color de texto rojo oscuro */
    }

    .table-container {
        max-height: 400px;
        overflow: auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff; /* Asegura que el fondo de la tabla sea blanco */
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-control {
        border: 1px solid #d32f2f;
    }

    .form-control:focus {
        border-color: #c62828;
        box-shadow: 0 0 0 0.2rem rgba(211, 47, 47, 0.25);
    }

    .btn-danger {
        background-color: #E62433; /* Rojo oscuro */
        border-color: #c62828;
    }

    .btn-danger:hover {
        background-color: #b71c1c; /* Rojo más oscuro para hover */
        border-color: #b71c1c;
    }

    /* Estilo para alinear los botones a la derecha */
    .text-right {
        text-align: right;
    }

    .btn-container {
        margin-top: 15px;
    }

    /* Estilo para centrar los botones en su columna respectiva */
    .btn-center {
        display: flex;
        justify-content: center;
    }

        /* Estilo para la tabla */
        table {
        width: 100%;
        border-collapse: separate; /* Permitir bordes separados para redondeo */
        border-spacing: 0; /* Eliminar el espaciado entre celdas */
        border-radius: 10px; /* Bordes redondeados en la tabla */
        overflow: hidden; /* Asegurar que los bordes redondeados se apliquen */
    }

    /* Estilo para el encabezado de la tabla */
    th {
        background-color: #E62433; /* Color de fondo del encabezado */
        color: white; /* Color del texto del encabezado */
        text-align: center; /* Alinear texto al centro */
        padding: 12px; /* Espaciado interno en celdas de encabezado */
        border: 1px solid #dee2e6; /* Borde de las celdas del encabezado */
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
    <div class="container">
        <?php
        // Mostrar mensajes de alerta si existen parámetros en la URL
        if (isset($_GET['mensaje']) && isset($_GET['tipo'])) {
            $mensaje = htmlspecialchars($_GET['mensaje']);
            $tipo = htmlspecialchars($_GET['tipo']);
            $alertClass = $tipo === 'error' ? 'alert-danger' : 'alert-success';
            echo "<div class='alert $alertClass' role='alert'>$mensaje</div>";
        }
        ?>

        <!-- Habilitación/Deshabilitación de Períodos -->
        <div class="section">
            <h2>Gestión de Períodos Académicos</h2>
            <form id="form_periodos">
                <table>
                    <tr>
                        <th>ID Período</th>
                        <th>Nombre del Período</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                    <?php while ($row = $result_periodos->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_periodo']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo $row['estado'] == '1' ? 'Activo' : 'Inactivo'; ?></td>
                        <td>
                            <?php if ($row['id_periodo'] != 3) { ?>
                            <input type="radio" name="periodo"
                                value="<?php echo htmlspecialchars($row['id_periodo']); ?>"
                                <?php echo $row['estado'] == '1' ? 'checked' : ''; ?>> Activar
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
                <div class="text-right btn-container">
                    <button type="button" class="btn btn-primary" onclick="actualizarPeriodos()">Actualizar Período</button>
                </div>
            </form>
        </div>

        <!-- Programar Cierre de Período -->
        <div class="section mt-4">
            <h3>Programación de Cierre de Año Escolar</h3>
            <form id="form_cierre" method="post" action="http://localhost/sistema_notas/Crud/admin/año_lectivo/programar_cierre.php" onsubmit="return validateDate()">
                <div class="form-group">
                    <label for="id_periodo">Año Lectivo:</label>
                    <select class="form-control" id="id_periodo" name="id_periodo" required>
                        <option value="">Selecciona Año Lectivo</option>
                        <?php
                        if ($result_years) {
                            while ($row = $result_years->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row['id_his_academico']) . '">' . htmlspecialchars($row['año']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha_cierre">Fecha y Hora de Cierre Programada:</label>
                    <input type="datetime-local" class="form-control" id="fecha_cierre" name="fecha_cierre" required>
                </div>
                <div class="text-right btn-container">
                    <button type="submit" class="btn btn-primary">Programar Cierre</button>
                </div>
            </form>
        </div>

        <!-- Mostrar lista de períodos -->
        <div class="section mt-4">
            <h3>Lista de Años Lectivos</h3>
            <div class="table-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID Año Lectivo</th>
                            <th>Año</th>
                            <th>Estado</th>
                            <th>Fecha de Cierre Programada</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT id_his_academico, año, estado, fecha_cierre_programada FROM historial_academico";
                        $result = $conn->query($sql);
                        
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['id_his_academico']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['año']) . '</td>';
                                echo '<td>' . ($row['estado'] == 'A' ? 'Activo' : 'Inactivo') . '</td>';
                                echo '<td>' . ($row['fecha_cierre_programada'] ? htmlspecialchars($row['fecha_cierre_programada']) : 'No Programada') . '</td>';
                                echo '<td class="btn-center">';
                                if ($row['fecha_cierre_programada'] !== null) {
                                    echo '<button class="btn btn-danger" onclick="cerrarAno(' . htmlspecialchars($row['id_his_academico']) . ', this)" disabled>Cerrado</button>';
                                } else {
                                    echo '<button class="btn btn-danger" onclick="cerrarAno(' . htmlspecialchars($row['id_his_academico']) . ', this)">Cerrar Año</button>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5">Error al cargar los datos.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
            Todos los derechos reservados.</p>
    </footer>

    <script>
    function validateDate() {
        const fechaCierre = new Date(document.getElementById('fecha_cierre').value);
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() + 3);
        
        if (fechaCierre > maxDate) {
            alert('La fecha de cierre no puede ser más allá de 3 años en el futuro.');
            return false;
        }
        return true;
    }

    function cerrarAno(idPeriodo, boton) {
        if (confirm('¿Estás seguro de que deseas cerrar este año lectivo?')) {
            const formData = new FormData();
            formData.append('id_periodo', idPeriodo);

            fetch('http://localhost/sistema_notas/Crud/admin/año_lectivo/cerrar_ano.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
            const existingAlert = document.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert ${data.tipo === 'error' ? 'alert-danger' : 'alert-success'}`;
                alertDiv.textContent = data.mensaje;
                document.body.prepend(alertDiv);
                if (data.tipo === 'success') {
                    boton.disabled = true;
                    boton.textContent = 'Cerrado';
                }
                setTimeout(() => {
                    alertDiv.remove();
                    location.reload();
                }, 3000);
            })
            .catch(error => console.error('Error:', error));
        }
    }

function actualizarPeriodos() {
    var formData = new FormData(document.getElementById('form_periodos'));

    fetch('http://localhost/sistema_notas/Crud/admin/año_lectivo/actualizar_periodos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        var newAlert = document.createElement('div');
        newAlert.className = 'alert alert-success';
        newAlert.textContent = 'Períodos actualizados correctamente.';
        document.body.prepend(newAlert);

        setTimeout(() => {
            newAlert.remove();
            location.reload();
        }, 3000);
    })
    .catch(error => {
        console.error('Error:', error);
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
                var newAlert = document.createElement('div');
                newAlert.className = 'alert alert-danger';
                newAlert.textContent = 'Error: ' + error.message;
                document.body.prepend(newAlert);

        setTimeout(() => {
            newAlert.remove();
        }, 3000);
    });
}
    </script>
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>

</html>

