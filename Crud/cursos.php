<?php
// Ejemplo de manejo de errores
if ($stmt->error) {
    die("Error en la inserción: " . $stmt->error);
} else {
    echo "Registro insertado correctamente.";
}
