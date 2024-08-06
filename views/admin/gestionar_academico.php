<?php
session_start();
include '../../Crud/config.php';
date_default_timezone_set('America/Guayaquil');

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
    <title>Gestión Académica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }

    .container {
        margin-top: 50px;
    }

    .section {
        background: #fff;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1, h2 {
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f8f8f8;
    }

    .btn-primary {
        margin-top: 10px;
    }

    .alert {
        margin-top: 20px;
    }

    .table-container {
        max-height: 400px;
        overflow: auto;
        border: 1px solid #ddd;
    }

    .form-group {
        margin-bottom: 15px;
    }
    </style>
</head>

<body>
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
            <h2>Habilitación/Deshabilitación de Períodos</h2>
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
                <button type="button" class="btn btn-primary" onclick="actualizarPeriodos()">Actualizar Períodos</button>
            </form>
        </div>

        <!-- Programar Cierre de Período -->
        <div class="section mt-4">
            <h3>Programar Cierre de Período</h3>
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
                <button type="submit" class="btn btn-primary">Programar Cierre</button>
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
                                if ($row['fecha_cierre_programada'] !== null) {
                                    echo '<td><button class="btn btn-danger" onclick="cerrarAno(' . htmlspecialchars($row['id_his_academico']) . ', this)" disabled>Cerrado</button></td>';
                                } else {
                                    echo '<td><button class="btn btn-danger" onclick="cerrarAno(' . htmlspecialchars($row['id_his_academico']) . ', this)">Cerrar Año</button></td>';
                                }
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
</body>

</html>

