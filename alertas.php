<?php
// alertas.php

// Función para mostrar una alerta
function mostrarAlerta($mensaje, $tipo = 'info') {
    echo '<div class="alert alert-'.$tipo.' alert-dismissible fade show" role="alert">
            '.$mensaje.'
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}

// Alerta de Éxito (Verde)
function alertaExito($mensaje) {
    mostrarAlerta($mensaje, 'success');
}

// Alerta de Advertencia (Amarilla)
function alertaAdvertencia($mensaje) {
    mostrarAlerta($mensaje, 'warning');
}

// Alerta de Error (Roja)
function alertaError($mensaje) {
    mostrarAlerta($mensaje, 'danger');
}

// Alerta de Información (Azul)
function alertaInformacion($mensaje) {
    mostrarAlerta($mensaje, 'info');
}

// Alerta de Éxito con Detalles
function alertaExitoDetalles($mensaje) {
    alertaExito($mensaje); // Utilizando la función de alerta de éxito
}

// Alerta de Confirmación (Modal)
function alertaConfirmacion() {
    // Ejemplo de modal de confirmación en Bootstrap
    echo '
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de eliminar este registro?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger">Eliminar</button>
                </div>
            </div>
        </div>
    </div>';
}

?>

<!-- Estilos y scripts de Bootstrap deben ser incluidos en tu proyecto -->
<!-- Por ejemplo, incluye los siguientes enlaces en tu archivo HTML principal -->
<!--
<link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
-->
