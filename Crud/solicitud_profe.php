<?php
include 'config.php';

$directorio_destino = '../../Sistema_Notas/imagenes/media/';

$ruta_completa_destino = realpath($directorio_destino) . '/';

function convertirAFormatoPNG($archivo_temporal, $ruta_destino, $nombre_archivo)
{
    $imagen_original = imagecreatefromjpeg($archivo_temporal);
    if (!$imagen_original) {
        echo "Error al cargar la imagen JPEG/JPG.";
        return false;
    }
    $ancho = imagesx($imagen_original);
    $alto = imagesy($imagen_original);
    $imagen_png = imagecreatetruecolor($ancho, $alto);
    imagesavealpha($imagen_png, true);
    $trans_color = imagecolorallocatealpha($imagen_png, 0, 0, 0, 127);
    imagefill($imagen_png, 0, 0, $trans_color);
    imagecopy($imagen_png, $imagen_original, 0, 0, 0, 0, $ancho, $alto);
    $ruta_final = $ruta_destino . $nombre_archivo . '.png';
    $guardado = imagepng($imagen_png, $ruta_final);
    imagedestroy($imagen_original);
    imagedestroy($imagen_png);
    return $guardado ? $ruta_final : false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario y validar/limpiarlos si es necesario
    $cedula = $_POST['cedula'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $correo_electronico = $_POST['correo_electronico'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $discapacidad = $_POST['discapacidad'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

    // Procesar el archivo si se ha subido
    if (isset($_FILES['archivo'])) {
        $nombre_original = $_FILES['archivo']['name'];
        $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

        if ($extension != 'jpg' && $extension != 'jpeg' && $extension != 'png') {
            echo "Solo se permiten archivos con extensión JPG, JPEG o PNG.";
            exit;
        }

        $nombre_unico = uniqid('archivo_');
        $archivo_temporal = $_FILES['archivo']['tmp_name'];

        if ($extension == 'jpg' || $extension == 'jpeg') {
            $resultado_conversion = convertirAFormatoPNG($archivo_temporal, $ruta_completa_destino, $nombre_unico);
            if (!$resultado_conversion) {
                echo "Hubo un error al convertir o subir el archivo.";
                exit;
            }
            // Se guardó como PNG, actualizamos la variable $archivo con la ruta final
            $archivo = $resultado_conversion;
        } elseif ($extension == 'png') {
            $ruta_archivo = $ruta_completa_destino . $nombre_unico . '.' . $extension;

            if (!move_uploaded_file($archivo_temporal, $ruta_archivo)) {
                echo "Hubo un error al subir el archivo.";
                exit;
            }
            // Se subió correctamente, actualizamos la variable $archivo con la ruta final
            $archivo = $ruta_archivo;
        }
    } else {
        echo "No se ha subido ningún archivo.";
        exit;
    }

    // Verificar que la columna "genero" exista en la base de datos
    // Para este ejemplo, asumiremos que existe. Si no, necesitarás modificar la base de datos o el código según corresponda.

    // Preparar la consulta SQL utilizando una consulta preparada
    $sql = "INSERT INTO soli_profe (cedula, telefono, nombres, apellidos, correo_electronico, rol, genero, direccion, discapacidad, contrasena, archivo, fecha_nacimiento, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    // Preparar la declaración
    $stmt = $conn->prepare($sql);

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("ssssssssssss", $cedula, $telefono, $nombres, $apellidos, $correo_electronico, $rol, $genero, $direccion, $discapacidad, $contrasena, $archivo, $fecha_nacimiento);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo '<script>alert("Solicitud enviada exitosamente.");</script>';
        echo '<script>window.location.replace("http://localhost/Sistema_Notas/views/admin/agregarprofe_admin.php");</script>';
        exit;
    } else {
        echo "Error al insertar el registro: " . $stmt->error;
    }

    // Cerrar la declaración y la conexión
    $stmt->close();
    $conn->close();
}
?>
