<?php
include '../config.php';

// Verificar si se ha proporcionado un ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Verificar si se ha confirmado la eliminación
    if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
        // Crear una conexión
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificar la conexión
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }

        // Preparar la declaración SQL
        $stmt = $conn->prepare("DELETE FROM jornada WHERE id = ?");
        if ($stmt) {
            // Vincular el parámetro
            $stmt->bind_param("i", $id);

            // Ejecutar la declaración
            if ($stmt->execute()) {
                // Redirigir a la página de paralelos
                header('Location: ../../views/admin/jornada_admin.php');
                exit;
            } else {
                echo "Error al ejecutar la consulta: " . $stmt->error;
            }

            // Cerrar la declaración
            $stmt->close();
        } else {
            echo "Error al preparar la consulta: " . $conn->error;
        }

        // Cerrar la conexión
        $conn->close();
    } else {
        // Mostrar el formulario de confirmación
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Eliminar jornada</title>
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        </head>
        <body>

        <!-- Modal -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ¿Estás de acuerdo en eliminar este campo?
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="">
                            <input type="hidden" name="confirm" value="yes">
                            <button type="submit" class="btn btn-danger">Confirmar</button>
                            <a href="../../views/admin/jornada_admin" class="btn btn-secondary">Regresar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('#confirmDeleteModal').modal('show');
            });
        </script>

        </body>
        </html>
        <?php
    }
} else {
    echo "No se proporcionó ID";
}
?>
