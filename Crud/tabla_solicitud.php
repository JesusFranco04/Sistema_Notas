<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $cedula = $_POST['cedula'];
    $telefono = $_POST['telefono'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $correo_electronico = $_POST['correo_electronico'];
    $rol = $_POST['rol'];

    include 'config.php';

    $sql = "INSERT INTO solicitudes (cedula, telefono, nombres, apellidos, correo_electronico, rol, date_creation)
            VALUES ('$cedula', '$telefono', '$nombres', '$apellidos', '$correo_electronico', '$rol', NOW())";


    if ($conn->query($sql) === TRUE) {
        echo '<script>alert("Solicitud enviada exitosamente.");</script>';
        echo '<script>window.location.replace("http://localhost/Sistema_Notas/Enviar_soli.php");</script>';
        exit;
    } else {
        echo "Error al insertar el registro: " . $conn->error;
    }

    $conn->close();
}
?>

<?php
include 'config.php';

// Realizar la consulta SQL para obtener los datos de la tabla
$sql = "SELECT * FROM solicitudes";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<div style="overflow-x: auto; overflow-y: auto; max-height: 370px;">'; // Ajusta la altura máxima según tus necesidades
    echo '<table class="table" style="width: 1047px;">'; // Ajusta el ancho de la tabla según tus necesidades
    echo '<thead>';
    echo '<tr>';
    echo '<th>Id</th>';
    echo '<th>Cédula</th>';
    echo '<th>Teléfono</th>';
    echo '<th>Nombres</th>';
    echo '<th>Apellidos</th>';
    echo '<th>Correo Electrónico</th>';
    echo '<th>Rol</th>';
    echo '<th>Fecha de Creación</th>';
    echo '<th>Acciones</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Iterar sobre los resultados de la consulta
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . $row['cedula'] . '</td>';
        echo '<td>' . $row['telefono'] . '</td>';
        echo '<td>' . $row['nombres'] . '</td>';
        echo '<td>' . $row['apellidos'] . '</td>';
        echo '<td>' . $row['correo_electronico'] . '</td>';
        echo '<td>' . $row['rol'] . '</td>';
        echo '<td>' . $row['date_creation'] . '</td>';
        echo '<td>';
        echo '<form id="formEliminar_' . $row['cedula'] . '" method="POST" action="http://localhost/sistema_notas/Crud/delete_solicitud.php">';
        echo '<input type="hidden" name="cedula" value="' . $row['cedula'] . '">';
        echo '<button type="button" class="btn btn-danger" onclick="eliminarSolicitud(' . $row['cedula'] . ')"><i class="bx bxs-trash"></i></button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

} else {
    echo 'No se encontraron registros.';
}

// Cerrar la conexión
$conn->close();
?>

<script>
    function eliminarSolicitud(cedula) {
        if (confirm("¿Estás seguro de que deseas eliminar esta solicitud?")) {
            var formId = 'formEliminar_' + cedula;
            var form = document.getElementById(formId);

            // Crear una nueva solicitud AJAX
            var xhr = new XMLHttpRequest();

            // URL del archivo PHP que procesará el formulario
            var url = form.getAttribute('action');

            // Datos a enviar en la solicitud POST (la cédula de la solicitud)
            var params = 'cedula=' + cedula;


            // Configurar la solicitud
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

            // Manejar la respuesta de la solicitud
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Mostrar la respuesta en la consola
                    console.log(xhr.responseText);

                    // Actualizar la página o realizar otras acciones necesarias
                    location.reload();
                }
            };

            // Enviar la solicitud
            xhr.send(params);
        }
    }
</script>