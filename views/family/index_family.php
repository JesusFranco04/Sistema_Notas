<?php
session_start();
include('../../Crud/config.php'); // Ruta al archivo de configuración de la base de datos

// Verificar si el usuario ha iniciado sesión y si su rol es "Padre"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Padre'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Obtener la cédula del usuario desde la sesión
$cedula = $_SESSION['cedula'];

// Consulta para obtener el nombre, apellido, cédula y el id_padre basado en la cédula del usuario
$sql = "SELECT p.nombres, p.apellidos, p.cedula, p.id_padre 
        FROM padre p 
        JOIN usuario u ON p.id_usuario = u.id_usuario 
        WHERE u.cedula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$stmt->bind_result($nombres, $apellidos, $cedula_padre, $id_padre);
$stmt->fetch();
$stmt->close();

// Verifica si se encontró el id_padre
if ($id_padre) {
    $_SESSION['id_padre'] = $id_padre; // Almacena el id_padre en la sesión
} else {
    // Maneja el caso en que no se encontró el id_padre
    echo "No se encontró el id_padre para la cédula proporcionada.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA DE GESTIÓN UEBF | REPRESENTANTE</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Enlace a Boxicons -->
    <link href="https://unpkg.com/boxicons/css/boxicons.min.css" rel="stylesheet">

    <style>
    /* Estilos básicos de la página */
    body {
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
        background-color: white;
        color: #333;
    }

    .header {
        background-color: #a20e14;
        color: white;
        text-align: center;
        padding: 20px;
    }

    .header h1 {
        margin: 0;
        font-size: 32px;
        text-transform: uppercase;
    }

    .help-button {
        margin-top: 10px;
        background-color: white;
        color: #a20e14;
        border: none;
        padding: 10px 20px;
        font-size: 18px;
        cursor: pointer;
        border-radius: 5px;
    }

    .help-button:hover {
        background-color: #f9f9f9;
    }

    .profile-summary {
        display: flex;
        align-items: center;
        justify-content: center;
        /* Centra los elementos horizontalmente */
        background-color: #fff1f2;
        /* Fondo suave */
        padding: 30px;
        width: 100%;
        box-sizing: border-box;
        color: #333;
        height: 200px;
        /* Asegura que haya suficiente altura para centrar el contenido */
    }

    .avatar {
        background-color: #a20e14;
        /* Color de fondo del avatar */
        color: white;
        font-size: 60px;
        /* Aumentar el tamaño del icono para hacerlo más visible */
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 30px;
        /* Espacio entre el avatar y la información */
    }

    .info {
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        /* Centra el texto dentro de .info */
    }

    .info h1 {
        font-size: 32px;
        /* Tamaño más grande para el nombre */
        font-weight: 700;
        color: #333;
        margin: 0;
    }

    .info p {
        font-size: 20px;
        /* Tamaño más grande para el rol */
        color: #3d3d3d;
        margin-top: 10px;
    }

    .info span {
        font-weight: bold;
        color: #a20e14;
        /* Resaltar el rol */
    }

    /* Diseño responsivo */
    @media screen and (max-width: 768px) {
        .profile-summary {
            flex-direction: column;
            height: auto;
            /* Elimina la altura fija en pantallas pequeñas */
            padding: 20px;
        }

        .avatar {
            margin-bottom: 20px;
            /* Da espacio entre el avatar y la información */
        }

        .info {
            margin-left: 0;
        }
    }


    .actions {
        display: flex;
        justify-content: center;
        margin-top: 30px;
        flex-wrap: nowrap;
        /* Asegura que los botones estén en una sola fila */
        gap: 20px;
        /* Espacio entre los botones */
        margin-bottom: 30px;
        /* Agregar espacio debajo de las tarjetas */
    }

    .card {
        margin: 10px;
        text-align: center;
        flex-basis: 30%;
        background-color: #a20e14;
        /* Fondo azul para las tarjetas */
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out;
        width: 200px;
        /* Ancho fijo para las tarjetas */
        color: white;
        /* Texto blanco en las tarjetas */
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card i {
        font-size: 40px;
        color: white;
        /* Íconos blancos */
        margin-bottom: 15px;
    }

    .card h2 {
        font-size: 18px;
        color: white;
        /* Texto blanco */
        font-weight: bold;
    }

    /* Estilo de los botones dentro de las tarjetas */
    .card .btn-view-children,
    .card .btn-view-grades,
    .card .btn-logout {
        background-color: white;
        /* Fondo blanco para los botones */
        color: #a20e14;
        /* Texto azul para los botones */
        font-size: 20px;
        padding: 20px 40px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin-top: 10px;
        transition: background-color 0.3s ease;
    }

    /* Efecto hover para los botones */
    .card .btn-view-children:hover,
    .card .btn-view-grades:hover,
    .card .btn-logout:hover {
        background-color: #f1f1f1;
        /* Color de fondo más claro al pasar el mouse */
    }

    .action-icon {
        font-size: 30px;
        margin-right: 10px;
    }

    .help-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        /* Cambiar a flex cuando se active */
        justify-content: center;
        align-items: center;
        z-index: 9999;
        /* Asegúrate de que sea mayor que cualquier otro elemento */
    }

    .help-modal.active {
        display: flex;
        /* Muestra el modal cuando se activa */
    }

    .help-content {
        background-color: white;
        padding: 12px;
        border-radius: 8px;
        text-align: left;
        width: calc(100% - 40px);
        /* Ajusta el ancho con márgenes laterales */
        max-width: 1200px;
        /* Permite que el modal sea más grande horizontalmente */
        margin: 0 auto;
        /* Centra horizontalmente el modal */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        display: flex;
        /* Agregado para disposición horizontal */
        flex-direction: row;
        /* Organiza los elementos en fila (horizontal) */
        flex-wrap: wrap;
        /* Permite que los elementos se ajusten cuando sea necesario */
        gap: 20px;
        /* Espaciado entre los elementos */
        z-index: 10000;
        /* Mayor que el overlay */
    }

    .help-content h3 {
        font-size: 28px;
        margin-bottom: 20px;
        color: #333;
        font-weight: bold;
        text-align: center;
        width: 100%;
        /* Se asegura de que el título ocupe todo el ancho */
    }

    .instructions {
        font-size: 20px;
        line-height: 1.6;
        text-align: left;
        margin-bottom: 20px;
        width: 45%;
        /* Ajusté el tamaño para permitir espacio en la horizontal */
        flex-grow: 1;
        /* Hace que esta sección ocupe el espacio disponible */
    }

    .instructions li {
        margin-bottom: 15px;
    }

    .buttons-container {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        gap: 10px;
        width: 100%;
        /* Hace que los botones se alineen correctamente */
    }

    .step-header {
        background-color: #a20e14;
        color: white;
        padding: 20px;
        text-align: center;
        font-size: 22px;
        font-weight: bold;
        border-radius: 8px 8px 0 0;
        width: 100%;
        /* Asegura que el encabezado ocupe todo el ancho */
    }

    .next-btn,
    .back-btn,
    .exit-btn {
        font-size: 20px;
        padding: 15px 30px;
        cursor: pointer;
        border-radius: 10px;
        min-width: 150px;
        transition: background-color 0.3s ease;
        text-align: center;
    }

    .next-btn {
        background-color: #a20e14;
        color: white;
    }

    .next-btn:hover {
        background-color: #7e090d;
    }

    .back-btn {
        background-color: #e5e7eb;
        color: #000;
    }

    .back-btn:hover {
        background-color: #b0b0b0;
    }

    .exit-btn {
        background-color: #163f6b;
        color: white;
    }

    .exit-btn:hover {
        background-color: #0e2643;
    }

    /* Fondo azul con gradiente */
    .historia-section-blue {
        background: #163f6b;
        color: #ffffff;
        width: 100%;
        padding: 60px 20px;
        box-sizing: border-box;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 450px;
        margin-bottom: 30px;
        /* Espacio entre el gradiente y el carrusel */
    }

    /* Contenedor principal */
    .historia-container {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        /* Igualar altura de los elementos */
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        gap: 20px;
        /* Espacio entre texto y carrusel */
    }

    /* Estilo del texto */
    .historia-text-container {
        flex: 1 1 50%;
        padding-right: 30px;
        background: #f5f9fd;
        /* Nuevo color de fondo */
        backdrop-filter: blur(5px);
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        display: flex;
        /* Asegura un diseño flexible */
        flex-direction: column;
        /* Organiza el contenido verticalmente */
        justify-content: center;
        /* Centra verticalmente el contenido */
        height: 100%;
        /* Igualar altura con el carrusel */
    }

    /* Estilo del título */
    .historia-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 15px;
        color: #163f6b;
        /* Azul elegante */
        text-align: center;
        /* Centra el título */
        position: relative;
        /* Necesario para la línea decorativa */
    }

    .historia-title::after {
        font-size: 32px;
        content: '';
        display: block;
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #163f6b, #00aaff);
        margin: 10px auto 0;
        border-radius: 5px;
    }

    .historia-subtitle {
        font-size: 1.3rem;
        font-style: italic;
        margin-bottom: 20px;
        color: #2a73c3;
        /* Azul claro y legible */
        text-shadow:
            0 0 5px rgba(42, 115, 195, 0.5),
            0 0 10px rgba(42, 115, 195, 0.4),
            0 0 15px rgba(42, 115, 195, 0.3);
        font-family: 'Playfair Display', serif;
        /* Elegante y curvada */
        white-space: nowrap;
        /* Mantiene todo en una línea */
        text-align: center;
        /* Centra el subtítulo */
    }

    .historia-question {
        font-size: 1.6rem;
        margin-bottom: 20px;
        font-weight: 600;
        color: #163f6b;
        /* Nuevo color para preguntas */
    }

    .historia-text {
        font-size: 16px;
        line-height: 1.8;
        margin-bottom: 15px;
        color: #163f6b;
        /* Nuevo color para el texto */
    }

    /* Carrusel */
    .historia-carousel-container {
        flex: 1 1 40%;
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        height: 760px;
        /* Permitir que tome el espacio disponible */
        display: flex;
        align-items: center;
        background-color: #163f6b;
    }

    .historia-carousel {
        display: flex;
        transition: transform 1s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        width: 400%;
        /* Número de imágenes x 100% */
        height: 100%;
    }

    .historia-carousel-slide {
        flex: 1 0 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    .historia-carousel-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .historia-carousel-slide img:hover {
        transform: scale(1.05);
        box-shadow: 0px 12px 20px rgba(0, 0, 0, 0.4);
    }

    /* Indicadores del carrusel */
    .historia-carousel-indicators {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
    }

    .historia-carousel-indicators div {
        width: 12px;
        height: 12px;
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .historia-carousel-indicators div.active {
        background-color: #ffffff;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .historia-container {
            flex-direction: column;
            gap: 20px;
            /* Espacio uniforme entre elementos */
        }

        .historia-text-container {
            padding-right: 0;
            margin-bottom: 20px;
        }

        .historia-carousel-container {
            margin-top: 20px;
            aspect-ratio: 4 / 3;
            height: auto;
        }
    }

    /* === Tarjetas de Misión y Visión === */
    .seccion-tarjetas {
        margin: 60px;
        background-color: #ffffff;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 50px;
    }

    .tarjeta {
        background-color: #c0d9b6;
        border-radius: 15px;
        padding: 50px 40px;
        width: 100%;
        max-width: 380px;
        height: auto;
        text-align: center;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        color: #233240;
    }

    .tarjeta:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .icono-tarjeta {
        font-size: 60px;
        margin-bottom: 20px;
        color: #002500;
        transition: transform 0.3s ease;
    }

    .icono-tarjeta:hover {
        transform: scale(1.1);
    }

    .titulo-tarjeta {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .texto-tarjeta {
        font-size: 16px;
        line-height: 1.6;
        color: #233240;
        margin-bottom: 25px;
    }

    /* === Iconos y Tooltips === */
    .iconos-circulares {
        display: flex;
        justify-content: center;
        gap: 25px;
        margin-top: 30px;
    }

    .circulo {
        background: #233240;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 30px;
        color: white;
        transition: background 0.3s ease, transform 0.3s ease;
        position: relative;
    }

    .circulo:hover {
        background: #002500;
        transform: scale(1.2);
    }

    .tooltip {
        position: absolute;
        top: 85px;
        left: 50%;
        transform: translateX(-50%);
        background: #233240;
        color: white;
        font-size: 13px;
        padding: 8px 12px;
        border-radius: 8px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease, top 0.3s ease;
    }

    .circulo:hover .tooltip {
        opacity: 1;
        visibility: visible;
        top: 75px;
    }

    /* === Estilos Responsivos === */
    @media (max-width: 768px) {
        .seccion-tarjetas {
            gap: 30px;
        }

        .tarjeta {
            max-width: 320px;
            padding: 40px;
        }

        .circulo {
            width: 60px;
            height: 60px;
            font-size: 26px;
        }

        .tooltip {
            font-size: 12px;
            padding: 6px 10px;
        }
    }

    @media (max-width: 480px) {
        .seccion-tarjetas {
            padding: 40px 10px;
        }

        .tarjeta {
            max-width: 280px;
            padding: 35px;
        }

        .circulo {
            width: 50px;
            height: 50px;
            font-size: 22px;
        }

        .tooltip {
            font-size: 11px;
            padding: 5px 8px;
        }
    }

    /* Estilos Generales */
    .valores-section {
        background-color: #ecf0f1;
        /* Fondo gris muy claro */
        padding: 40px;
        /* Espaciado interno de la sección */
        text-align: center;
        /* Centra el texto */
        font-family: 'Roboto', sans-serif;
        /* Tipografía moderna */
    }

    .valores-title {
        font-size: 32px;
        /* Tamaño ajustado del título */
        color: #163f6b;
        /* Color azul grisáceo para el título */
        font-weight: 700;
        /* Negrita */
        margin-bottom: 20px;
        /* Espaciado inferior */
        text-transform: uppercase;
    }

    .valores-line {
        border: none;
        border-top: 3px solid #163f6b;
        /* Línea superior con color azul grisáceo */
        width: 70px;
        /* Ancho inicial de la línea */
        margin: 0 auto 40px;
        /* Margen automático en los lados, y espaciado inferior */
        position: relative;
        transition: width 0.3s ease-in-out, background-color 0.3s ease;
        /* Transiciones suaves para el hover */
    }

    /* Efecto de expansión y cambio de color en el hover de la línea */
    .valores-section:hover .valores-line {
        width: 90px;
        /* Expande la línea cuando se pasa el mouse */
        background: linear-gradient(90deg, #163f6b, #e74c3c);
        /* Gradiente suave entre azul grisáceo y rojo claro */
    }

    /* Contenedor de las tarjetas */
    .valores-container {
        display: flex;
        /* Flexbox para la disposición de las tarjetas */
        justify-content: space-between;
        /* Espaciado entre las tarjetas */
        align-items: center;
        /* Alinea las tarjetas verticalmente */
        gap: 30px;
        /* Espacio entre las tarjetas */
        flex-wrap: wrap;
        /* Permite que las tarjetas se envuelvan en la fila */
        max-width: 1200px;
        /* Ancho máximo */
        margin: 0 auto;
        /* Centra el contenedor */
        padding: 0 10px;
        /* Espaciado interno en los lados */
    }

    /* Estilo de cada tarjeta de valor */
    .valor-card {
        font-size: 20px;
        background-color: white;
        /* Fondo blanco para las tarjetas */
        border-radius: 12px;
        /* Bordes redondeados */
        padding: 25px;
        /* Espaciado interno */
        width: 160px;
        /* Ancho fijo de las tarjetas */
        text-align: center;
        /* Centra el texto dentro de la tarjeta */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        /* Sombra suave alrededor de las tarjetas */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        /* Efectos de transición */
        cursor: pointer;
        /* Cambia el cursor cuando pasa sobre la tarjeta */
        position: relative;
        /* Posicionamiento relativo para efectos */
    }

    /* Efecto de hover en las tarjetas */
    .valor-card:hover {
        transform: translateY(-8px);
        /* Eleva la tarjeta cuando se pasa el mouse */
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
        /* Sombra más pronunciada */
        background-color: #e9ffe5;
        /* Fondo verde claro */
    }

    /* Efecto de animación para el icono */
    .valor-icon {
        font-size: 40px;
        /* Tamaño del icono */
        color: #a20e14;
        /* Color rojo oscuro para el icono */
        margin-bottom: 4px;
        /* Espaciado inferior */
        transition: color 0.3s ease, transform 0.3s ease;
        /* Transición suave de color y transformación */
    }

    /* Efecto de flotación y cambio de color del icono en hover */
    .valor-card:hover .valor-icon {
        color: #233240;
        /* Cambia el color del icono */
        transform: scale(1.2) translateY(-3px);
        /* Agranda el icono y lo desplaza ligeramente */
        animation: floatIcon 1.5s ease-in-out infinite;
        /* Animación de flotación continua */
    }

    /* Animación de flotación para el icono */
    @keyframes floatIcon {
        0% {
            transform: scale(1.1) translateY(-3px);
            /* Posición inicial */
        }

        50% {
            transform: scale(1.2) translateY(-7px);
            /* Posición intermedia */
        }

        100% {
            transform: scale(1.1) translateY(-3px);
            /* Posición final */
        }
    }

    /* Efecto de pulsación en el icono al hacer clic */
    .valor-card:active .valor-icon {
        transform: scale(1.1);
        /* Le da un pequeño efecto de pulsación */
    }

    /* Estilo del texto dentro de la tarjeta */
    .valor-text {
        font-size: 1.1rem;
        /* Tamaño de fuente ajustado */
        color: #34495e;
        /* Color gris oscuro para el texto */
        font-weight: 600;
        /* Peso de la fuente */
        line-height: 1.4;
        /* Altura de línea ajustada */
        transition: color 0.3s ease;
        /* Transición suave de color */
    }

    /* Efecto de cambio de color del texto en hover */
    .valor-card:hover .valor-text {
        color: #233240;
        /* Color del texto cuando se pasa el mouse */
    }

    /* Colores de las tarjetas */
    .valor-card:nth-child(1) {
        border-left: 5px solid #163f6b;
        /* Color para la tarjeta 1 */
    }

    .valor-card:nth-child(2) {
        border-left: 5px solid #163f6b;
        /* Color para la tarjeta 2 */
    }

    .valor-card:nth-child(3) {
        border-left: 5px solid #163f6b;
        /* Color para la tarjeta 3 */
    }

    .valor-card:nth-child(4) {
        border-left: 5px solid #163f6b;
        /* Color para la tarjeta 4 */
    }

    .valor-card:nth-child(5) {
        border-left: 5px solid #163f6b;
        /* Color para la tarjeta 5 */
    }

    /* Animación para la aparición de las tarjetas */
    @keyframes fadeIn {
        from {
            opacity: 0;
            /* Comienza invisible */
            transform: translateY(20px);
            /* Desplaza hacia abajo */
        }

        to {
            opacity: 1;
            /* Termina visible */
            transform: translateY(0);
            /* Vuelve a su posición original */
        }
    }

    /* Aplica la animación de aparición a las tarjetas */
    .valores-container>.valor-card {
        animation: fadeIn 0.6s ease-out;
        /* Tiempo y tipo de animación */
    }

    /* Estilo del título de la sección de especialidades */
    .titulo-seccion-especialidades {
        font-size: 32px;
        color: #a20e14;
        text-align: center;
        margin-bottom: 15px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        /* Reducción del espacio entre letras */
    }

    /* Línea de separación */
    .linea-seccion-especialidades {
        width: 100%;
        /* Ocupa todo el ancho de la pantalla */
        height: 6px;
        background: linear-gradient(135deg, #7e090d, #ff6369);
        margin: 0 auto 20px;
        border: none;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }


    /* Contenedor de especialidades */
    .contenedor-especialidades {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
        /* Menos espacio entre cuadros */
        padding: 20px;
    }

    /* Cuadros de especialidades */
    .cuadro-especialidad {
        width: 230px;
        /* Tamaño ligeramente más pequeño */
        height: 230px;
        /* Tamaño ligeramente más pequeño */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        /* Tamaño del texto más pequeño */
        font-weight: 500;
        color: #ffffff;
        text-align: center;
        cursor: pointer;
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3)), url('../../imagenes/fondo.jpg');
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        margin: 15px;
        /* Menos espacio entre los cuadros */
    }

    /* Efecto de hover para los cuadros */
    .cuadro-especialidad:hover {
        transform: scale(1.05);
        /* Un ligero aumento en el tamaño del cuadro */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        /* Aumenta la sombra en hover */
    }


    .cuadro-especialidad::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(162, 14, 20, 0.8), rgba(255, 111, 97, 0.8));
        z-index: 1;
        mix-blend-mode: multiply;
    }

    .cuadro-especialidad span {
        position: relative;
        z-index: 2;
    }

    .cuadro-especialidad-electronica {
        background-image: url('../../imagenes/cuadro1-electronica.png');
    }

    .cuadro-especialidad-electricidad {
        background-image: url('../../imagenes/cuadro2-electricidad.png');
    }

    .cuadro-especialidad-mecanica {
        background-image: url('../../imagenes/cuadro3-mecánica.png');
    }

    .ventana-especialidades {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .ventana-especialidades.mostrar {
        display: flex;
    }

    .contenido-ventana-especialidades {
        background: #ffffff;
        border-radius: 15px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transform: scale(0.9);
        animation: scaleIn 0.3s ease forwards;
        padding: 10px;
    }

    .cabecera-ventana-especialidades {
        background-color: #a20e14;
        color: white;
        font-weight: bold;
        padding: 15px 25px;
        /* Aumento del padding */
        font-size: 24px;
        /* Aumento del tamaño de la fuente */
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 60px;
        /* Altura aumentada */
    }

    /* Botón de cierre (X) */
    .boton-cerrar {
        position: absolute;
        top: 15px;
        right: 20px;
        background: none;
        border: none;
        font-size: 2.5rem;
        /* Botón más grande */
        color: white;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    /* Cuerpo del modal */
    .cuerpo-ventana-especialidades {
        padding: 20px 30px;
        /* Aumento de padding */
        font-size: 22px;
        /* Aumento del tamaño del texto */
        line-height: 1.8;
        /* Mayor espacio entre líneas */
        text-align: justify;
    }

    .galeria-ventana-especialidades {
        display: flex;
        justify-content: center;
        gap: 15px;
        padding: 15px;
        flex-wrap: wrap;
    }

    .galeria-ventana-especialidades img {
        width: 150px;
        /* Imágenes más grandes */
        height: 150px;
        /* Imágenes más grandes */
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    /* Animación de las imágenes al pasar el ratón */
    .galeria-ventana-especialidades img:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    @keyframes scaleIn {
        to {
            transform: scale(1);
        }
    }

    .ocultar {
        display: none;
    }

    /* Cambio de tamaño de los botones */
    .boton-cerrar:hover {
        color: #500003;
    }

    /* Estilo general para la sección de fotos (contenedor principal) */
    .foto-container {
        background-color: #fff1f2;
        margin: 30px auto;
        padding: 20px 20px 40px;
        /* Ajuste de padding para reducir el espacio superior */
        text-align: center;
        border-radius: 15px;
        max-width: 1200px;
    }

    .foto-container h2 {
        font-size: 32px;
        font-weight: bold;
        color: #7e090d;
        background: #a20e14;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-transform: uppercase;
        margin-bottom: 30px;
        /* Reducido el margen inferior */
    }

    .foto-container h3 {
        font-size: 18px;
        font-weight: 400;
        color: #7e090d;
        margin-top: 30px;
        /* Ajuste del margen superior para un espacio estándar */
    }

    .grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        /* 4 columnas */
        gap: 30px;
        justify-items: center;
        margin-top: 20px;
    }

    /* Aumentar espacio entre las dos filas */
    .grid-container+.grid-container {
        margin-top: 40px;
        /* Aumentar el espacio entre las filas */
    }

    .grid-item {
        position: relative;
        overflow: hidden;
        border-radius: 15px;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease, filter 0.3s ease;
        cursor: pointer;
        background-color: #fff;
        height: 300px;
        /* Altura fija para los contenedores */
        width: 100%;
    }

    .grid-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border-radius: 15px;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .grid-item .caption {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 15px;
        background-color: rgba(0, 0, 0, 0.7);
        color: #fff;
        font-size: 20px;
        text-align: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }

    .grid-item:hover {
        transform: translateY(-10px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2), 0 0 20px #7e090d;
        filter: brightness(1.1);
    }

    .grid-item:hover .caption {
        opacity: 1;
    }

    /* Efecto hover entre imagen principal y secundaria */
    .grid-item img.secondary {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .grid-item:hover img.primary {
        opacity: 0;
    }

    .grid-item:hover img.secondary {
        opacity: 1;
        transform: scale(1.05);
        /* Expande ligeramente la imagen secundaria */
    }

    /* Hacer el diseño responsivo */
    @media (max-width: 768px) {
        .foto-container {
            padding: 20px;
        }

        .grid-item {
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
        }

        .foto-container h2 {
            font-size: 28px;
        }

        .grid-container {
            grid-template-columns: 1fr 1fr;
            /* Dos columnas en pantallas medianas */
        }
    }

    @media (max-width: 480px) {
        .grid-container {
            grid-template-columns: 1fr;
            /* Una columna en pantallas pequeñas */
        }

        .foto-container h2 {
            font-size: 24px;
        }
    }

    /* Estilos para el pie de página (footer) */
    footer {
        background-color: #a20e14;
        color: white;
        text-align: center;
        padding: 20px;
        font-family: 'Roboto', sans-serif;
    }
    </style>
</head>

<body>
    <!-- Encabezado Principal -->
    <header class="header">
        <h1>Sistema de Gestión UEBF</h1>
        <button class="help-button" onclick="showHelp()">¿Necesitas Ayuda?</button>
    </header>

    <!-- Sección Resumen del Perfil -->
    <section class="profile-summary">
        <div class="avatar">
            <!-- Icono de Boxicons como placeholder -->
            <i class='bx bx-user'></i>
        </div>
        <div class="info">
            <h1>Bienvenido(a), <?php echo htmlspecialchars($nombres . ' ' . $apellidos); ?></h1>
            <p>Cédula: <span><?php echo htmlspecialchars($cedula_padre); ?></span></p>
            <p>Tu rol es: <span><?php echo htmlspecialchars($_SESSION['rol']); ?></span></p>
        </div>
    </section>

    <!-- Sección de acciones principales -->
    <section class="actions">
        <div class="card btn-view-children">
            <i class='bx bx-user'></i>
            <h2>Ver Información de Hijos</h2>
        </div>
        <div class="card btn-view-grades">
            <i class='bx bx-book'></i>
            <h2>Consultar Calificaciones</h2>
        </div>
        <div class="card btn-logout" onclick="window.location.href='http://localhost/sistema_notas/logout.php'">
            <i class='bx bx-log-out'></i>
            <h2>Cerrar Sesión</h2>
        </div>
    </section>

    <!-- Modal de Ayuda -->
    <div id="help-modal" class="help-modal">
        <div class="help-content">
            <div id="step-header" class="step-header">
                Instrucciones - Paso 1
            </div>
            <ul class="instructions">
                <li id="step-1">
                    <strong>Ver Información de Hijos:</strong> Para ver la información de tus hijos, sigue estos pasos:
                    <ol>
                        <li>Busca en la pantalla el cuadro que dice <strong>"Ver Información de Hijos"</strong>.</li>
                        <li>Este cuadro tiene una imagen de una persona (<i class='bx bx-user'></i>) y está justo debajo
                            de tu nombre y datos personales, en el centro de la pantalla.</li>
                        <li>Haz <strong>clic izquierdo</strong> en ese cuadro.</li>
                        <li>En la nueva página podrás ver:
                            <ul>
                                <li>Los nombres y apellidos de tus hijos.</li>
                                <li>Información escolar como el nivel, paralelo, subnivel, especialidad, jornada, y el
                                    año escolar.</li>
                            </ul>
                        </li>
                    </ol>
                </li>
                <li id="step-2" style="display: none;">
                    <strong>Consultar Calificaciones:</strong> Si quieres ver las calificaciones de tus hijos, haz lo
                    siguiente:
                    <ol>
                        <li>Busca el cuadro que dice <strong>"Consultar Calificaciones".</strong></li>
                        <li>Este cuadro tiene una imagen de un libro (<i class='bx bx-book'></i>) y está junto al cuadro
                            de "Ver Información de Hijos", debajo de tu perfil.</li>
                        <li>Haz <strong>clic izquierdo</strong> en ese cuadro.</li>
                        <li>Se abrirá una nueva página donde podrás ver las calificaciones de cada uno de tus hijos.
                        </li>
                    </ol>
                </li>
                <li id="step-3" style="display: none;">
                    <strong>¿Necesitas Ayuda?:</strong> Si tienes alguna pregunta o necesitas ayuda, sigue estos pasos:
                    <ol>
                        <li>Ve a la parte superior de la pantalla, en el centro.</li>
                        <li>Verás un botón que dice <strong>"¿Necesitas Ayuda?"</strong>.</li>
                        <li>Si en algún momento no estás seguro de lo que debes hacer, puedes volver a leer este manual
                            de instrucciones en cualquier momento.</li>
                        <li>Para regresar a un paso anterior, busca el botón <strong>"Regresar"</strong> y haz
                            <strong>clic izquierdo</strong> sobre él con el mouse.
                        </li>
                        <li>Si ya has entendido todo y quieres avanzar, haz clic en el botón
                            <strong>"Siguiente"</strong> con el botón izquierdo del mouse.
                        </li>
                        <li>Cuando hayas terminado y quieras salir, ve a la última página del manual y verás un botón
                            que dice <strong>"Cerrar"</strong>. Haz <strong>clic izquierdo</strong> sobre ese botón con
                            el mouse para salir del manual de instrucciones.</li>
                    </ol>
                </li>
                <li id="step-4" style="display: none;">
                    <strong>Cerrar Sesión:</strong> Para salir del sistema, haz lo siguiente:
                    <ol>
                        <li>Busca el cuadro que dice <strong>"Cerrar Sesión".</strong></li>
                        <li>Este cuadro tiene una imagen de una flecha (<i class='bx bx-log-out'></i>) y está en la
                            parte derecha, junto a "Consultar Calificaciones".</li>
                        <li>Haz <strong>clic izquierdo</strong> en ese cuadro para salir del sistema.</li>
                    </ol>
                </li>
            </ul>
            <div class="buttons-container">
                <button id="back-btn" class="back-btn" onclick="prevStep()" style="display:none;">Regresar</button>
                <button id="next-btn" class="next-btn" onclick="nextStep()">Siguiente</button>
                <button id="exit-btn" class="exit-btn" style="display:none" onclick="closeHelp()">Salir</button>
            </div>
        </div>
    </div>

    <!-- Sección Historia -->
    <section class="historia-section historia-section-blue">
        <div class="historia-container">
            <!-- Texto a la izquierda -->
            <div class="historia-text-container">
                <h2 class="historia-title">NUESTRA HISTORIA</h2>
                <p class="historia-subtitle">"55 años formando jóvenes con valores y excelencia técnica para su futuro"
                </p>
                <h3 class="historia-question">
                    ¿Busca un lugar donde su hijo pueda crecer con confianza y alcanzar su máximo potencial?
                </h3>
                <p class="historia-text">
                    En la Unidad Educativa Benjamín Franklin, entendemos lo importante que es para usted la educación de
                    su hijo. Por eso, confiarnos su aprendizaje significa ponerlo en manos de expertos comprometidos con
                    su desarrollo integral.
                </p>
                <p class="historia-text">
                    Ofrecemos una formación de excelencia que combina valores, teoría y práctica en áreas como
                    Electrónica, Electricidad y Mecánica Automotriz. Nuestro enfoque educativo asegura que cada
                    estudiante esté preparado para enfrentar los retos del futuro con confianza y habilidades reales.
                </p>
                <p class="historia-text">
                    Más allá del aprendizaje técnico, fomentamos el crecimiento académico, artístico y deportivo de su
                    hijo. A través de pasantías y proyectos prácticos, garantizamos experiencias que conectan
                    directamente con el mundo real. Bajo el liderazgo comprometido de nuestra rectora, Alexandra de
                    Rocío Ruano Sánchez, trabajamos diariamente para construir un futuro brillante para cada joven.
                </p>
            </div>
            <!-- Carrusel a la derecha -->
            <div class="historia-carousel-container">
                <div class="historia-carousel">
                    <div class="historia-carousel-slide"><img src="../../imagenes/imagen-u4.jpeg" alt="Imagen 1"></div>
                    <div class="historia-carousel-slide"><img src="../../imagenes/electronica3.png" alt="Imagen 2">
                    </div>
                    <div class="historia-carousel-slide"><img src="../../imagenes/electricidad3.png" alt="Imagen 3">
                    </div>
                    <div class="historia-carousel-slide"><img src="../../imagenes/mecanica4.png" alt="Imagen 4"></div>
                    <div class="historia-carousel-slide"><img src="../../imagenes/electronica1.png" alt="Imagen 5">
                    </div>
                    <div class="historia-carousel-slide"><img src="../../imagenes/imagen-u5.jpeg" alt="Imagen 6"></div>
                    <div class="historia-carousel-slide"><img src="../../imagenes/imagen-u3.jpeg" alt="Imagen 7"></div>
                </div>
            </div>
        </div>
    </section>

    <div class="seccion-tarjetas">
        <!-- Tarjeta de Misión -->
        <div class="tarjeta">
            <div class="icono-tarjeta"><i class='bx bxs-book-reader'></i></div>
            <h3 class="titulo-tarjeta">Misión</h3>
            <p class="texto-tarjeta">Brindamos una educación de excelencia, formando líderes íntegros y comprometidos,
                preparados para afrontar los desafíos del futuro con sólidos valores éticos y sociales.</p>
            <div class="iconos-circulares">
                <div class="circulo">
                    <i class='bx bx-leaf'></i>
                    <div class="tooltip">Calidad Educativa</div>
                </div>
                <div class="circulo">
                    <i class='bx bx-group'></i>
                    <div class="tooltip">Formación Integral</div>
                </div>
                <div class="circulo">
                    <i class='bx bx-laptop'></i>
                    <div class="tooltip">Innovación</div>
                </div>
                <div class="circulo">
                    <i class='bx bx-user-voice'></i>
                    <div class="tooltip">Responsabilidad Social</div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Visión -->
        <div class="tarjeta">
            <div class="icono-tarjeta"><i class='bx bx-show'></i></div>
            <h3 class="titulo-tarjeta">Visión</h3>
            <p class="texto-tarjeta">Ser una institución líder, reconocida por su excelencia educativa y su impacto
                positivo
                en la sociedad, promoviendo innovación y liderazgo en cada estudiante.</p>
            <div class="iconos-circulares">
                <div class="circulo">
                    <i class='bx bx-globe'></i>
                    <div class="tooltip">Impacto Social</div>
                </div>
                <div class="circulo">
                    <i class='bx bx-book-open'></i>
                    <div class="tooltip">Excelencia Académica</div>
                </div>
                <div class="circulo">
                    <i class='bx bx-star'></i>
                    <div class="tooltip">Liderazgo</div>
                </div>
                <div class="circulo">
                    <i class='bx bx-briefcase'></i>
                    <div class="tooltip">Progreso Económico</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Valores Institucionales -->
    <section class="valores-section">
        <h2 class="valores-title">Valores Institucionales</h2>
        <hr class="valores-line">
        <div class="valores-container">
            <div class="valor-card">
                <i class='bx bx-trophy valor-icon'></i>
                <p class="valor-text">Compromiso</p>
            </div>
            <div class="valor-card">
                <i class='bx bx-briefcase-alt-2 valor-icon'></i>
                <p class="valor-text">Formación Integral</p>
            </div>
            <div class="valor-card">
                <i class='bx bx-star valor-icon'></i>
                <p class="valor-text">Excelencia</p>
            </div>
            <div class="valor-card">
                <i class='bx bx-time valor-icon'></i>
                <p class="valor-text">Oportunidad</p>
            </div>
            <div class="valor-card">
                <i class='bx bx-shield-quarter valor-icon'></i>
                <p class="valor-text">Responsabilidad</p>
            </div>
        </div>
    </section>

    <!-- Título de la sección de especialidades -->
    <hr class="linea-seccion-especialidades">
    <h2 class="titulo-seccion-especialidades">Especialidades</h2>
    <div class="contenedor-especialidades">
        <div class="cuadro-especialidad cuadro-especialidad-electronica" onclick="abrirVentanaEspecialidades(1)">
            <span>Electrónica</span>
        </div>
        <div class="cuadro-especialidad cuadro-especialidad-electricidad" onclick="abrirVentanaEspecialidades(2)">
            <span>Electricidad</span>
        </div>
        <div class="cuadro-especialidad cuadro-especialidad-mecanica" onclick="abrirVentanaEspecialidades(3)">
            <span>Mecánica Automotriz</span>
        </div>
    </div>
    <hr class="linea-seccion-especialidades">

    <div class="ventana-especialidades" id="ventana-especialidades">
        <div class="contenido-ventana-especialidades">
            <div class="cabecera-ventana-especialidades" id="titulo-ventana-especialidades">Título</div>
            <button class="boton-cerrar" onclick="cerrarVentanaEspecialidades()">&times;</button>
            <div class="cuerpo-ventana-especialidades" id="contenido-ventana-especialidades">Descripción</div>
            <div class="galeria-ventana-especialidades">
                <img src="../../imagenes/electronica1.png" class="especialidad-1 ocultar">
                <img src="../../imagenes/electronica2.png" class="especialidad-1 ocultar">
                <img src="../../imagenes/electronica3.png" class="especialidad-1 ocultar">
                <img src="../../imagenes/electronica4.png" class="especialidad-1 ocultar">
                <img src="../../imagenes/electricidad1.png" class="especialidad-2 ocultar">
                <img src="../../imagenes/electricidad2.png" class="especialidad-2 ocultar">
                <img src="../../imagenes/electricidad3.png" class="especialidad-2 ocultar">
                <img src="../../imagenes/electricidad4.png" class="especialidad-2 ocultar">
                <img src="../../imagenes/mecanica1.png" class="especialidad-3 ocultar">
                <img src="../../imagenes/mecanica2.png" class="especialidad-3 ocultar">
                <img src="../../imagenes/mecanica3.png" class="especialidad-3 ocultar">
                <img src="../../imagenes/mecanica4.png" class="especialidad-3 ocultar">
            </div>
        </div>
    </div>

    <!-- Sección que contiene la galería de imágenes con actividades y proyectos -->
    <section class="foto-container">
        <h2>GALERÍA</h2>
        <h3>
            Descubra en nuestra galería las actividades y proyectos que enriquecen el aprendizaje de sus hijos,
            brindándoles un futuro lleno de oportunidades.
        </h3>

        <div class="grid-container">
            <!-- Primera fila de imágenes -->
            <div class="grid-item">
                <img src="../../imagenes/graduacion2.png" alt="Graduación 2023" class="primary">
                <img src="../../imagenes/graduacion.png" alt="Graduación 2023 Detalle" class="secondary">
                <div class="caption">Graduación 2023</div>
            </div>
            <div class="grid-item">
                <img src="../../imagenes/deportivo2.png" alt="Evento Deportivo" class="primary">
                <img src="../../imagenes/deportivo.png" alt="Evento Deportivo Detalle" class="secondary">
                <div class="caption">Evento Deportivo</div>
            </div>
            <div class="grid-item">
                <img src="../../imagenes/proyecto.jpeg" alt="Proyectos Sostenibles o Ecológicos" class="primary">
                <img src="../../imagenes/proyecto2.jpeg" alt="Proyectos Sostenibles Detalle" class="secondary">
                <div class="caption">Proyectos Sostenibles</div>
            </div>
            <div class="grid-item">
                <img src="../../imagenes/fiesta.jpeg" alt="Fiesta de Navidad" class="primary">
                <img src="../../imagenes/fiesta2.jpeg" alt="Fiesta de Navidad Detalle" class="secondary">
                <div class="caption">Fiesta de Navidad</div>
            </div>
        </div>

        <!-- Segunda fila de imágenes -->
        <div class="grid-container">
            <div class="grid-item">
                <img src="../../imagenes/imagen-u2.jpeg" alt="Clases de Música" class="primary">
                <img src="../../imagenes/imagen-u2.jpeg" alt="Clases de Música Detalle" class="secondary">
                <div class="caption">Clases de Música</div>
            </div>
            <div class="grid-item">
                <img src="../../imagenes/electronica2.png" alt="Electrónica de Consumo" class="primary">
                <img src="../../imagenes/electronica4.png" alt="Electrónica de Consumo Detalle" class="secondary">
                <div class="caption">Electrónica de Consumo</div>
            </div>
            <div class="grid-item">
                <img src="../../imagenes/electricidad1.png" alt="Electricidad" class="primary">
                <img src="../../imagenes/electricidad2.png" alt="Electricidad Detalle" class="secondary">
                <div class="caption">Electricidad</div>
            </div>
            <div class="grid-item">
                <img src="../../imagenes/mecanica3.png" alt="Mecánica Automotriz" class="primary">
                <img src="../../imagenes/mecanica4.png" alt="Mecánica Automotriz Detalle" class="secondary">
                <div class="caption">Mecánica Automotriz</div>
            </div>
        </div>
    </section>

    <!-- Pie de página (footer) -->
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener el id_padre desde la sesión de PHP
        const idPadre = "<?php echo $_SESSION['id_padre']; ?>";

        // Card para ver información de los hijos
        document.querySelector('.btn-view-children').addEventListener('click', function() {
            alert(
                'Estamos preparando la información de sus hijos. Por favor, espere un momento mientras se carga.'
            );

            // Redirigir a la página de información de los hijos con el id_padre en la URL
            window.location.href =
                'http://localhost/sistema_notas/views/family/estudiante_fami.php?id_padre=' + idPadre;
        });

        // Card para consultar calificaciones
        document.querySelector('.btn-view-grades').addEventListener('click', function() {
            alert('Redirigiendo a la página de selección de estudiante para consultar calificaciones.');
            // Redirige a la página de selección de estudiante
            window.location.href =
                'http://localhost/sistema_notas/views/family/seleccionar_estudiante.php';
        });

        // Card para cerrar sesión
        document.querySelector('.btn-logout').addEventListener('click', function() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                // Aquí puedes agregar lógica para cerrar sesión
                alert('Cerrando sesión...');
                // window.location.href = 'login.html'; // Redirigir a la página de login
            }
        });

    });

    // Función principal para mostrar el modal de ayuda
    function showHelp() {
        document.getElementById('help-modal').style.display = 'flex';
        currentStep = 1; // Iniciar en el paso 1
        document.getElementById('back-btn').style.display = 'none'; // Ocultar el botón "Regresar" en el paso 1
        document.getElementById('exit-btn').style.display = 'none'; // Ocultar el botón "Salir" en el paso 1
        showStep(currentStep);
    }

    // Función para mostrar el contenido correspondiente a un paso específico
    function showStep(step) {
        // Mostrar u ocultar las instrucciones dependiendo del paso
        document.getElementById('step-1').style.display = (step === 1) ? 'block' : 'none';
        document.getElementById('step-2').style.display = (step === 2) ? 'block' : 'none';
        document.getElementById('step-3').style.display = (step === 3) ? 'block' : 'none';
        document.getElementById('step-4').style.display = (step === 4) ? 'block' : 'none';

        // Cambiar el encabezado del paso
        document.getElementById('step-header').innerText = `Instrucciones - Paso ${step}`;

        // Mostrar los botones correspondientes dependiendo del paso
        if (step === 1) {
            document.getElementById('back-btn').style.display = 'none';
            document.getElementById('next-btn').style.display = 'inline-block';
            document.getElementById('exit-btn').style.display = 'none';
        } else if (step === 2 || step === 3) {
            document.getElementById('back-btn').style.display = 'inline-block';
            document.getElementById('next-btn').style.display = 'inline-block';
            document.getElementById('exit-btn').style.display = 'none';
        } else if (step === 4) {
            document.getElementById('back-btn').style.display = 'inline-block';
            document.getElementById('next-btn').style.display = 'none';
            document.getElementById('exit-btn').style.display = 'inline-block';
        }
    }

    function nextStep() {
        if (currentStep < 4) {
            currentStep++;
            showStep(currentStep);
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    }

    function closeHelp() {
        document.getElementById('help-modal').style.display = 'none';
    }

    // Carrusel automático para la sección Historia
    document.addEventListener('DOMContentLoaded', () => {
        const carousel = document.querySelector('.historia-carousel');
        const slides = document.querySelectorAll('.historia-carousel-slide');
        let currentIndex = 0;

        setInterval(() => {
            currentIndex = (currentIndex + 1) % slides.length; // Ciclo infinito
            carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
        }, 5000); // Cambia cada 5 segundos
    });

    // Objeto que almacena la información de cada especialidad (incluyendo el título y la descripción)
    const especialidades = {
        1: {
            title: "Electrónica de Consumo",
            info: "La especialización en esta área de la Unidad Educativa Benjamín Franklin capacita a los estudiantes en el diseño, reparación y mantenimiento de dispositivos electrónicos."
        },
        2: {
            title: "Electricidad",
            info: "La especialización en electricidad capacita a los estudiantes en el diseño, fabricación, reparación y mantenimiento de sistemas eléctricos para el hogar y el comercio."
        },
        3: {
            title: "Mecánica Automotriz",
            info: "Esta especialización capacita a los estudiantes en el diagnóstico, mantenimiento y reparación de sistemas de vehículos."
        }
    };

    function abrirVentanaEspecialidades(id) {
        const modal = document.getElementById("ventana-especialidades");
        document.getElementById("titulo-ventana-especialidades").textContent = especialidades[id].title;
        document.getElementById("contenido-ventana-especialidades").textContent = especialidades[id].info;

        // Ocultar todas las imágenes
        document.querySelectorAll(".galeria-ventana-especialidades img").forEach(img => {
            img.classList.add("ocultar");
        });

        // Mostrar solo las imágenes de la especialidad seleccionada
        const imagenes = document.querySelectorAll(`.especialidad-${id}`);
        imagenes.forEach(img => {
            img.classList.remove("ocultar");
        });

        modal.classList.add("mostrar");
    }

    function cerrarVentanaEspecialidades() {
        document.getElementById("ventana-especialidades").classList.remove("mostrar");
    }
    </script>
</body>

</html>