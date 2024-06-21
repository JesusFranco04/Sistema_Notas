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


if (isset($_POST['submit']) && isset($_FILES['archivo'])) {

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
        if ($resultado_conversion) {
            echo "El archivo se convirtió y subió correctamente como PNG.";
        } else {
            echo "Hubo un error al convertir o subir el archivo.";
        }
    } elseif ($extension == 'png') {
        $ruta_archivo = $ruta_completa_destino . $nombre_unico . '.' . $extension;

        if (move_uploaded_file($archivo_temporal, $ruta_archivo)) {
            echo "El archivo se subió correctamente.";
        } else {
            echo "Hubo un error al subir el archivo.";
        }
    }
}
?>