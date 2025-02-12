<?php
session_start();

// Verificar si el usuario ha iniciado sesión y si su rol es "Profesor"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Profesor'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA DE GESTIÓN UEBF | PROFESOR</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: white;
        margin: 0;
        padding: 0;
        color: #333;
        isolation: isolate;
    }

    header {
        background-color: #c61e1e;
        color: white;
        padding: 20px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        height: 150px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid #0052aa;
    }

    header h1 {
        display: flex;
        /* Alinea los elementos en fila */
        align-items: center;
        /* Alineación vertical */
        font-size: 1.5rem;
        /* Tamaño del texto */
        color: #ffffff;
        /* Todo en blanco */
        gap: 0.3rem;
        /* Espacio reducido entre los elementos */
    }

    header h1 .badge {
        font-size: 1.2rem;
        /* Tamaño del texto del rol (más grande) */
        font-weight: bold;
        /* Texto en negrita */
        color: #ffffff;
        /* Texto en blanco */
        background: none;
        /* Sin fondo */
        text-transform: uppercase;
        /* Texto en mayúsculas */
        margin-top: 0.2rem; /* Baja el rol ligeramente */
    }

    header h1 i {
        font-size: 1.8rem;
        /* Icono ligeramente grande */
        color: #ffffff;
        /* Icono completamente blanco */
        margin-top: 0.2rem; /* Baja el rol ligeramente */
    }


    header p {
        margin: 5px 0;
        font-size: 16px;
    }

    .system-name {
        font-size: 14px;
        margin-bottom: 10px;
        color: #f0f0f0;
    }

    .logout-button {
        background-color: white;
        color: #E62433;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 5px;
        position: absolute;
        bottom: 15px;
        right: 15px;
    }

    /* Media Queries */
    @media (max-width: 768px) {
        header {
            flex-direction: column;
            height: auto;
            padding: 15px;
        }

        header h1 {
            font-size: 20px;
        }

        header p {
            font-size: 14px;
        }

        .system-name {
            font-size: 12px;
        }

        .logout-button {
            position: static;
            margin-top: 10px;
            align-self: center;
        }
    }

    @media (max-width: 480px) {
        header {
            padding: 10px;
        }

        header h1 {
            font-size: 18px;
        }

        header p {
            font-size: 12px;
        }

        .system-name {
            font-size: 10px;
        }

        .logout-button {
            padding: 8px 16px;
            font-size: 12px;
        }
    }

    /* Banner */
    .banner {
        position: relative;
        overflow: hidden;
        width: 100%;
        height: 400px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
        animation: fadeIn 1.5s forwards;
    }

    .banner img.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(1.1);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .banner-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        padding: 15px;
        background: rgba(0, 51, 102, 0.8);
        /* Color azul oscuro con transparencia */
        color: white;
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        border-radius: 10px;
        animation: fadeText 2s infinite;
    }

    @keyframes fadeText {

        0%,
        100% {
            opacity: 0;
        }

        33% {
            opacity: 1;
        }
    }

    .header-banner::after {
        content: "";
        position: relative;
        /* Asegura que el z-index funcione */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        /* Superponer capa oscura */
        z-index: 100;
        /* Menor que el modal */
    }

    /* Estilo para el encabezado con overlay */
    .header-banner {
        position: relative;
        background: url('../../imagenes/header-image.jpg') no-repeat center center/cover;
        /* Imagen del encabezado */
        height: 300px;
        border-radius: 15px;
    }

    .header-banner-text {
        position: relative;
        z-index: 2;
        color: white;
        text-align: center;
        padding: 50px 20px;
        font-size: 1.8rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }

    /* Sección Historia */
    .historia-section {
        background: linear-gradient(135deg, #003366, #003366);
        background-color: #ffffff;
        /* Fondo blanco */
        color: #00264d;
        padding: 30px 20px;
        max-width: 1250px;
        margin: 0 auto;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    /* Título de la historia */
    .historia-title {
        font-size: 2rem;
        text-align: center;
        color: white;
        margin-bottom: 20px;
        /* Espacio moderado debajo del título */
    }

    /* Subtítulo con efecto neón */
    .historia-subtitle {
        font-size: 1.2rem;
        text-align: center;
        color: #00ccff;
        margin-bottom: 25px;
        /* Espacio moderado debajo del subtítulo */
        text-shadow: 0 0 6px #00ccff, 0 0 12px #008fb3, 0 0 18px rgba(0, 255, 255, 0.5);
    }

    /* Contenido de la historia */
    .historia-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        flex-wrap: wrap;
        /* Asegura que se adapten en pantallas pequeñas */
    }

    /* Contenedor de texto */
    .historia-text-container {
        flex: 1;
        margin-bottom: 20px;
        /* Espacio debajo del contenedor de texto */
    }

    .historia-question {
        font-size: 1.5rem;
        color: #66c2ff;
        margin-bottom: 15px;
        /* Espacio debajo de la pregunta */
    }

    .historia-text {
        font-size: 0.95rem;
        line-height: 1.4;
        /* Reducir espacio entre líneas */
        margin-bottom: 15px;
        /* Espacio entre párrafos */
        color: white;
    }

    /* Collage de imágenes */
    .historia-collage {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-auto-rows: 200px;
        gap: 10px;
        /* Menos espacio entre imágenes */
        flex: 1;
        max-width: 500px;
        /* Ajustar el tamaño del collage */
        justify-content: flex-start;
    }

    .historia-collage a {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        /* Añadir sombra sutil */
    }

    .historia-collage img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease, box-shadow 0.3s ease, filter 0.3s ease;
        /* Transición para efectos */
    }

    .historia-collage a:hover img {
        transform: scale(1.1);
        filter: brightness(1.1) saturate(1.2);
        /* Efecto de brillo y saturación */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        /* Efecto de sombra al hacer hover */
    }

    /* Estilos para dispositivos móviles */
    @media (max-width: 768px) {
        .historia-content {
            flex-direction: column;
            /* Apilar los elementos en columna */
        }

        /* Collage de imágenes en una sola columna en móviles */
        .historia-collage {
            grid-template-columns: 1fr;
            max-width: 100%;
            /* Asegurar que ocupe el 100% del ancho disponible */
        }

        .historia-collage img {
            height: auto;
            /* Mantener la proporción de las imágenes */
        }
    }

    /* Estilo general para la sección */
    .valores-section {
        background-color: #fff;
        /* Fondo ligeramente blanco */
        padding: 60px 20px;
        text-align: center;
    }

    /* Título de la sección */
    .valores-title {
        font-size: 2.8rem;
        color: #E62433;
        /* Rojo institucional */
        margin-bottom: 15px;
        font-weight: bold;
        text-transform: uppercase;
    }

    /* Línea debajo del título */
    .valores-line {
        border: 2px solid #E62433;
        /* Línea más gruesa */
        width: 100px;
        margin: 0 auto 40px;
        height: 2px;
    }

    /* Contenedor de los valores */
    .valores-container {
        display: flex;
        justify-content: center;
        gap: 40px;
        /* Más espacio entre círculos */
        flex-wrap: wrap;
        max-width: 1100px;
        margin: 0 auto;
    }

    /* Estilo de cada tarjeta de valor */
    .valor-card {
        background-color: #0d5316;
        /* Verde inicial */
        color: #ffffff;
        /* Texto e ícono blancos */
        padding: 30px;
        border-radius: 50%;
        /* Hacer el fondo circular */
        transition: all 0.3s ease;
        text-align: center;
        width: 150px;
        /* Aumentar tamaño del círculo */
        height: 150px;
        /* Aumentar tamaño del círculo */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    .valor-card:hover {
        transform: translateY(-10px);
        /* Más elevado */
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        background-color: #002956;
        /* Azul oscuro al hover */
        color: #fcd34d;
        /* Color dorado al hover */
    }

    /* Estilo del icono */
    .valor-icon {
        font-size: 2.5rem;
        /* Ajustar tamaño del ícono */
        transition: transform 0.3s ease, color 0.3s ease;
        margin-bottom: 5px;
        /* Reducir separación entre ícono y texto */
        color: inherit;
        /* Mismo color que el círculo */
    }

    /* Cambio de tamaño del icono al hover */
    .valor-card:hover .valor-icon {
        transform: scale(1.3);
        /* Escalar ícono */
    }

    /* Estilo del texto dentro de la tarjeta */
    .valor-text {
        font-size: 0.8rem;
        /* Reducir tamaño del texto */
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: inherit;
        /* Mismo color que el círculo */
        text-align: center;
        /* Asegurar centrado */
        white-space: normal;
        /* Permitir saltos de línea si es necesario */
        word-wrap: break-word;
        /* Ajustar palabras largas */
    }

    /* Estilos para dispositivos móviles */
    @media (max-width: 768px) {
        .valores-container {
            gap: 20px;
        }

        .valor-card {
            width: 120px;
            /* Tamaño más pequeño para móviles */
            height: 120px;
            /* Tamaño más pequeño para móviles */
            padding: 20px;
        }

        .valor-icon {
            font-size: 2rem;
            /* Ícono más pequeño para pantallas pequeñas */
        }

        .valor-text {
            font-size: 0.8rem;
            /* Texto más pequeño */
        }
    }

    /* === Sección de Misión y Visión === */
    .vision-mission-section {
        padding: 50px;
        background-color: #ffffff;
        /* Fondo blanco */

        display: flex;
        flex-wrap: wrap;
        /* Diseño adaptativo */
        justify-content: center;
        gap: 30px;
        /* Separación entre tarjetas */
    }

    /* === Tarjetas === */
    .card {
        background: #a2ffae;
        /* Fondo rojo */
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        padding: 30px;
        width: 100%;
        max-width: 390px;
        /* Tamaño estándar en escritorio */
        height: 410px;
        /* Altura de la tarjeta ajustada */
        text-align: center;
        position: relative;
        /* Necesario para ::before */
        overflow: hidden;
        /* Evitar desbordamientos */
        transition: all 0.3s ease;
        /* Transición fluida para transform y shadow */
        color: #343a40;
        /* Gris oscuro */

    }

    .card:hover {
        transform: translateY(-10px);
        /* Elevación */
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        /* Sombra más pronunciada */
    }

    /* === Línea decorativa en la parte superior === */
    .card::before {
        content: '';
        /* Línea decorativa */
        position: absolute;
        top: 0;
        /* Parte superior */
        left: 0;
        /* Inicio en la izquierda */
        width: 100%;
        height: 5px;
        /* Altura de la línea */
        background: linear-gradient(to right, #fff, #0d5316);
        /* Degradado verde a azul oscuro */
        transform-origin: left;
        /* Crecimiento desde la izquierda */
        transform: scaleX(0);
        /* Línea invisible por defecto */
        transition: transform 0.3s ease-in-out;
        /* Efecto suave */
    }

    .card:hover::before {
        transform: scaleX(1);
        /* Línea se extiende completamente */
    }

    /* === Icono dentro de la tarjeta === */
    .card-icon {
        font-size: 50px;
        /* Tamaño estándar */
        color: #0a4312;
        /* Verde */
        margin-bottom: 15px;
        transition: color 0.3s ease;
        /* Cambio de color fluido */
    }

    .card:hover .card-icon {
        color: #07244a;
        /* Cambia a azul oscuro */
    }

    /* === Títulos y texto === */
    .card-title {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 15px;
        text-transform: uppercase;
        color: #07244a;
        /* Azul oscuro */
    }

    .card-text {
        font-size: 14px;
        line-height: 1.6;
        color: #555555;
        /* Gris oscuro */
    }

    /* === Círculos decorativos con tooltip === */
    .circle-icons {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
    }

    .circle {
        background: #07244a;
        /* Azul oscuro */
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        color: #ffffff;
        /* Blanco */
        transition: background 0.3s ease, transform 0.3s ease;
        position: relative;
    }

    .circle:hover {
        background: #156524;
        /* Verde */
        transform: scale(1.2);
    }

    /* Tooltip */
    .tooltip {
        position: absolute;
        top: 75px;
        left: 50%;
        transform: translateX(-50%);
        background: #0a4312;
        /* Azul oscuro */
        color: #ffffff;
        /* Blanco */
        font-size: 11px;
        padding: 5px 8px;
        border-radius: 5px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease, top 0.3s ease;
    }

    .circle:hover .tooltip {
        opacity: 1;
        visibility: visible;
        top: 70px;
    }

    /* === Responsividad === */
    @media (max-width: 768px) {
        .vision-mission-section {
            gap: 20px;
            /* Reducir separación */
        }

        .card {
            max-width: 300px;
            /* Tamaño más pequeño */
            padding: 20px;
        }

        .circle {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }

        .tooltip {
            font-size: 10px;
            padding: 4px 6px;
        }
    }

    @media (max-width: 480px) {
        .vision-mission-section {
            padding: 30px;
        }

        .card {
            max-width: 280px;
            padding: 15px;
        }

        .circle-icons {
            gap: 15px;
        }

        .circle {
            width: 45px;
            height: 45px;
            font-size: 18px;
        }

        .tooltip {
            font-size: 9px;
            padding: 3px 5px;
        }
    }

    /* === Estilos para el título de la sección Especialidades === */
    /* Título de la sección */
    .section-title {
        font-size: 2.8rem;
        color: #E62433;
        /* Rojo institucional */
        margin-bottom: 10px;
        /* Reducimos el margen inferior */
        font-weight: bold;
        text-align: center;
        /* Centramos el texto */
        text-transform: uppercase;
    }


    /* Línea debajo del título */
    .section-line {
        border: 2px solid #E62433;
        /* Línea más gruesa */
        width: 100px;
        margin: 0 auto 20px;
        /* Reducimos el margen inferior */
        height: 2px;
    }

    /* === Estilos para los cuadros de especialidades === */
    .contenedor-cuadros {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        /* Permite que los cuadros se ajusten en pantallas pequeñas */
        gap: 15px;
        /* Reducimos el espacio entre cuadros */
        padding: 15px;
        /* Reducimos el espacio general alrededor del contenedor */
        margin: 35px;
    }

    .cuadro {
        width: 200px;
        /* Tamaño estándar para cuadros */
        height: 200px;
        /* Altura estándar */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2em;
        /* Tamaño de texto */
        font-weight: bold;
        color: white;
        text-align: center;
        cursor: pointer;
        background-size: cover;
        background-position: center;
        background-blend-mode: overlay;
        transition: filter 0.3s ease;
        border-radius: 10px;
        /* Bordes redondeados */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        /* Sombra ligera */
    }

    /* Colores para los cuadros */
    .cuadro1 {
        background-color: #a82926;
    }

    .cuadro2 {
        background-color: #218838;
    }

    .cuadro3 {
        background-color: #0064cb;
    }

    /* Efecto al pasar el cursor */
    .cuadro:hover {
        filter: brightness(1.2);
        transform: scale(1.05);
        /* Efecto de ampliación */
    }

    /* Cuadro de Electrónica */
    .cuadro1 {
        background-image: url('../../imagenes/cuadro1-electronica.png');
        /* Cambia el color y la opacidad si prefieres */
    }

    /* Cuadro de Electricidad */
    .cuadro2 {
        background-image: url('../../imagenes/cuadro2-electricidad.png');
        /* Cambia el color y la opacidad si prefieres */
    }

    /* Cuadro de Mecánica Automotriz */
    .cuadro3 {
        background-image: url('../../imagenes/cuadro3-mecánica.png');
        /* Cambia el color y la opacidad si prefieres */
    }

    .modal {
        display: none;
        /* Asegura que el modal esté oculto por defecto */
        position: fixed;
        /* Asegura que el modal esté posicionado encima de la página */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        /* Fondo oscuro */
        z-index: 1000;
        /* Asegura que el modal esté encima de otros elementos */
        align-items: center;
        justify-content: center;
        /* Inicialmente invisible */
        transition: opacity 0.3s ease;
        /* Transición suave */
    }

    .modal.show {
        display: flex;
        /* Hacer visible el modal */
        opacity: 1;
        /* Mostrar el modal */
    }


    /* Animación suave para el contenido del modal */
    .modal-content {
        background-color: white;
        border-radius: 10px;
        width: 80%;
        max-width: 500px;
        position: relative;
        text-align: center;
        transform: scale(0.8);
        /* Tamaño inicial reducido */
        animation: scaleIn 0.8s ease-out forwards;
        /* Animación de entrada */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    /* Estilo para la cabecera del modal */
    .modal-header {
        background-color: #DE112D;
        /* Color rojo */
        color: white;
        /* Texto blanco */
        font-weight: bold;
        padding: 15px;
        border-top-left-radius: 8px;
        /* Esquinas redondeadas */
        border-top-right-radius: 8px;
        display: flex;
        justify-content: space-between;
        /* Espaciar el título y la "X" */
        align-items: center;
        height: 60px;
        /* Altura uniforme */
    }

    /* Botón de cierre ("X") */
    .modal-header .close-btn {
        background: none;
        /* Sin fondo */
        border: none;
        /* Sin bordes */
        font-size: 2rem;
        /* Tamaño grande para la "X" */
        color: white;
        /* Color blanco */
        cursor: pointer;
        /* Mano al pasar el mouse */
        padding: 0;
        margin: 0;
        transition: color 0.3s ease;
        /* Suavizar el cambio de color */
    }

    .modal-header .close-btn:hover {
        color: #a50f23;
        /* Color dorado al pasar el mouse */
    }

    /* Título del modal */
    .modal-title {
        font-size: 24px;
        font-weight: bold;
        margin: 0;
    }

    /* === Estilo para el cuerpo del modal === */
    .modal-body {
        padding: 20px;
        /* Espacio para la información */
        margin-top: 10px;
        /* Espacio entre la franja roja y el contenido */
        font-size: 16px;
        /* Tamaño de fuente legible */
        line-height: 1.5;
        /* Mejor lectura */
    }

    /* === Estilo para las imágenes en el modal === */
    .carousel-track {
        display: flex;
        justify-content: center;
        gap: 20px;
        padding: 20px;
        flex-wrap: wrap;
        /* Permitir que las imágenes se ajusten al ancho del contenedor */
    }

    /* Ajustar las imágenes dentro del modal */
    .carousel {
        display: flex;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .carousel img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        /* Ajustar imagen sin distorsión */
        border-radius: 8px;
        /* Esquinas redondeadas */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        /* Sombra elegante */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .carousel img:hover {
        transform: scale(1.1);
        /* Efecto de zoom al pasar el mouse */
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        /* Sombra más pronunciada */
    }

    /* === Estilos para la sección Galería === */
    /* Título de la sección */
    .galerias-title {
        font-size: 2.8rem;
        color: white;
        /* Rojo institucional */
        margin-bottom: 15px;
        font-weight: bold;
        text-align: center;
        /* Centramos el texto */
        text-transform: uppercase;
    }

    /* Línea debajo del título */
    .galerias-line {
        border: 2px solid white;
        /* Línea más gruesa */
        width: 100px;
        margin: 0 auto 40px;
        height: 2px;
    }

    /* Fondo azul para la galería */
    .galeria {
        padding: 60px 0;
        background-color: #00264d;
        /* Fondo azul */
        text-align: center;
        color: white;
    }

    .galeria h2 {
        font-size: 3rem;
        color: #fff;
        margin-bottom: 50px;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: bold;
    }

    /* Estructura del grid */
    .galeria-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        /* Mantiene las columnas iguales */
        gap: 30px;
        justify-items: center;
    }

    /* Estilo de cada elemento (video o imagen) */
    .galeria-grid .item {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        /* Sombra más suave */
        background-color: #2d2d2d;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        height: 250px;
        /* Tamaño fijo para todos los elementos */
    }

    /* Efecto al pasar el ratón (hover) */
    .galeria-grid .item:hover {
        transform: translateY(-5px);
        /* Movimiento más suave */
        box-shadow: 0 10px 18px rgba(0, 0, 0, 0.15);
        /* Sombra más suave */
    }

    /* Bordes coloridos al hacer hover */
    .galeria-grid .item:nth-child(odd):hover {
        border: 5px solid #ffa1a8;
        /* Rojo brillante */
    }

    .galeria-grid .item:nth-child(even):hover {
        border: 5px solid #3aee9f;
        /* verde eléctrico */
    }

    /* Para las imágenes y los videos */
    .galeria-grid img,
    .galeria-grid video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* Asegura que las imágenes y videos cubran el área del contenedor sin distorsionarse */
        display: block;
        border-radius: 20px;
        transition: transform 0.3s ease-in-out;
    }

    .galeria-grid img:hover,
    .galeria-grid video:hover {
        transform: scale(1.02);
        /* Zoom más suave */
    }

    /* Efectos de opacidad al pasar el ratón */
    .galeria-grid .item:hover img,
    .galeria-grid .item:hover video {
        opacity: 0.85;
        /* Opacidad más sutil */
    }

    /* Estilo para los videos */
    video {
        object-fit: cover;
        border-radius: 20px;
        max-height: 250px;
        /* Mantener la misma altura que los demás elementos */
    }

    /* Móviles y pantallas pequeñas */
    @media (max-width: 768px) {
        .galeria-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            /* Ajuste en pantallas pequeñas */
        }

        .galeria h2 {
            font-size: 2.5rem;
        }
    }

    footer {
        border-top: 3px solid #073b73;
        /* Borde en la parte superior */
        background-color: #ad0f0f;
        color: white;
        text-align: center;
        /* Centrar el texto */
        padding: 20px 0;
        /* Espaciado interno vertical */
        width: 100%;
        /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        /* Sombra más pronunciada */
        bottom: 0;
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }
    </style>
</head>

<body>
    <header>
        <div class="system-name">SISTEMA DE GESTIÓN UEBF</div>
        <h1>
            Bienvenido(a),
            <?php
            // Verifica si las variables de sesión están establecidas
            if (isset($_SESSION['nombres'], $_SESSION['apellidos'], $_SESSION['rol'])) {
                // Escapa caracteres especiales para seguridad
                echo htmlspecialchars($_SESSION['nombres']) . " " . htmlspecialchars($_SESSION['apellidos']); // Muestra nombres y apellidos
                echo " | <span class='badge'>" . htmlspecialchars($_SESSION['rol']) . "</span>"; // Rol sin fondo
                echo " <i class='bx bx-user-circle'></i>"; // Icono blanco simple
            }
            ?>
        </h1>
        <p>Gestiona tus actividades y calificaciones de manera sencilla</p>
        <button class="logout-button" onclick="window.location.href='http://localhost/sistema_notas/logout.php'">Cerrar
            Sesión</button>
        </div>
    </header>

    <!-- Banner de imágenes con animación -->
    <div class="container-fluid my-5">
        <div class="banner">
            <img src="../../imagenes/tu-imagen10.jpg" alt="Imagen 1" class="active">
            <img src="../../imagenes/tu-imagen20.jpg" alt="Imagen 2">
            <img src="../../imagenes/tu-imagen30.jpg" alt="Imagen 3">
            <div class="banner-text" id="bannerText">
                Unidad Educativa Benjamín Franklin: El lugar donde comienza tu futuro técnico
            </div>
        </div>
    </div>

    <!-- Sección Historia -->
    <section class="historia-section">
        <div class="container">
            <h2 class="historia-title">Nuestra Historia</h2>
            <p class="historia-subtitle">"55 años formando jóvenes con valores y excelencia técnica para su futuro"
            </p>
            <div class="historia-content">
                <!-- Texto a la izquierda -->
                <div class="historia-text-container">
                    <h3 class="historia-question">
                        ¿Desea una formación completa que garantice el futuro exitoso de su hijo?
                    </h3>
                    <p class="historia-text">
                        En la Unidad Educativa Benjamín Franklin, formamos a nuestros estudiantes de manera
                        integral,
                        combinando teoría y práctica en áreas como Electrónica, Electricidad y Mecánica Automotriz.
                        Tu rol como docente es clave para inspirar y guiar a las futuras generaciones, preparando a
                        nuestros jóvenes para el mundo laboral.
                    </p>
                    <p class="historia-text">
                        Promovemos el desarrollo académico, artístico y deportivo, brindando experiencias reales
                        como
                        pasantías y proyectos prácticos.
                        Ser parte de este equipo es contribuir a la construcción de futuros exitosos bajo el
                        liderazgo
                        comprometido de nuestra rectora, Alexandra de Rocío Ruano Sánchez.
                    </p>
                </div>
                <!-- Collage a la derecha -->
                <div class="historia-collage">
                    <a href="../../imagenes/imagen2.jpeg" data-lightbox="galeria" data-title="Imagen 2">
                        <img src="../../imagenes/imagen2.jpeg" alt="Imagen 1">
                    </a>
                    <a href="../../imagenes/imagen3.jpeg" data-lightbox="galeria" data-title="Imagen 3">
                        <img src="../../imagenes/imagen3.jpeg" alt="Imagen 2">
                    </a>
                    <a href="../../imagenes/imagen4.jpeg" data-lightbox="galeria" data-title="Imagen 4">
                        <img src="../../imagenes/imagen4.jpeg" alt="Imagen 3">
                    </a>
                    <a href="../../imagenes/imagen5.jpeg" data-lightbox="galeria" data-title="Imagen 5">
                        <img src="../../imagenes/imagen5.jpeg" alt="Imagen 4">
                    </a>
                </div>
            </div>
        </div>
    </section>

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
            <div class="valor-card">
                <i class='bx bx-group valor-icon'></i>
                <p class="valor-text">Trabajo en equipo</p>
            </div>
        </div>
    </section>

    <!-- Sección de Cursos -->
    <section
        style="background-color: #003366; color: white; padding: 40px 20px; text-align: center; width: 100%; margin: 0; border-top: 3px solid #E62433; border-bottom: 3px solid #E62433;">
        <h2 style="font-size: 28px; margin-bottom: 20px;">Gestione y Revise sus Cursos</h2>
        <p style="font-size: 18px; max-width: 800px; margin: 0 auto;">
            Acceda fácilmente a la lista completa de los cursos que imparte. Consulte de manera detallada las listas de
            estudiantes inscritos, administre las calificaciones y mantenga actualizada toda la información necesaria
            para un seguimiento académico eficiente.
        </p>
        <a href="http://localhost/sistema_notas/views/profe/curso_profe.php" style="text-decoration: none;">
            <button class="ver-cursos-btn"
                style="background-color: white; color: #003366; border: none; padding: 15px 30px; font-size: 18px; margin-top: 30px; cursor: pointer; border-radius: 5px;">
                Ver Cursos
            </button>
        </a>
    </section>

    <!-- Aquí agregarías tu nueva sección de Visión y Misión -->
    <div class="vision-mission-section">
        <!-- Tarjeta de Misión -->
        <div class="card">
            <div class="card-icon"><i class='bx bxs-book-reader'></i></div>
            <h3 class="card-title">Misión</h3>
            <p class="card-text">Brindamos una educación de excelencia, formando líderes íntegros y comprometidos,
                preparados para afrontar los desafíos del futuro con sólidos valores éticos y sociales.</p>
            <div class="circle-icons">
                <div class="circle">
                    <i class='bx bx-leaf'></i>
                    <div class="tooltip">Calidad Educativa</div>
                </div>
                <div class="circle">
                    <i class='bx bx-group'></i>
                    <div class="tooltip">Formación Integral</div>
                </div>
                <div class="circle">
                    <i class='bx bx-laptop'></i>
                    <div class="tooltip">Innovación</div>
                </div>
                <div class="circle">
                    <i class='bx bx-user-voice'></i>
                    <div class="tooltip">Responsabilidad Social</div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Visión -->
        <div class="card">
            <div class="card-icon"><i class='bx bx-show'></i></div>
            <h3 class="card-title">Visión</h3>
            <p class="card-text">Ser una institución líder, reconocida por su excelencia educativa y su impacto positivo
                en la sociedad, promoviendo innovación y liderazgo en cada estudiante.</p>
            <div class="circle-icons">
                <div class="circle">
                    <i class='bx bx-globe'></i>
                    <div class="tooltip">Impacto Social</div>
                </div>
                <div class="circle">
                    <i class='bx bx-book-open'></i>
                    <div class="tooltip">Excelencia Académica</div>
                </div>
                <div class="circle">
                    <i class='bx bx-star'></i>
                    <div class="tooltip">Liderazgo</div>
                </div>
                <div class="circle">
                    <i class='bx bx-briefcase'></i>
                    <div class="tooltip">Progreso Económico</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Especialidades -->
    <h2 class="section-title">Especialidades</h2>
    <hr class="section-line">
    <div class="contenedor-cuadros">
        <div class="cuadro cuadro1" onclick="openModal(1)">Electrónica</div>
        <div class="cuadro cuadro2" onclick="openModal(2)">Electricidad</div>
        <div class="cuadro cuadro3" onclick="openModal(3)">Mecánica Automotriz</div>
    </div>

    <!-- Modal para cada especialidad -->
    <div class="modal" id="modal" style="display: none; opacity: 0;">
        <div class="modal-content">
            <!-- Cabecera con franja roja -->
            <div class="modal-header">
                <h2 class="modal-title" id="modal-title">Título de la Especialidad</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <!-- Información de la especialidad -->
            <div class="modal-body" id="modal-info">
                Descripción de la especialidad aquí.
            </div>
            <!-- Galería de imágenes -->
            <div class="carousel" id="carousel">
                <div class="carousel-track">
                    <img src="../../imagenes/electronica1.png" class="specialty-1">
                    <img src="../../imagenes/electronica2.png" class="specialty-1">
                    <img src="../../imagenes/electronica3.png" class="specialty-1">
                    <img src="../../imagenes/electronica4.png" class="specialty-1">
                    <img src="../../imagenes/electricidad1.png" class="specialty-2">
                    <img src="../../imagenes/electricidad2.png" class="specialty-2">
                    <img src="../../imagenes/electricidad3.png" class="specialty-2">
                    <img src="../../imagenes/electricidad4.png" class="specialty-2">
                    <img src="../../imagenes/mecanica1.png" class="specialty-3">
                    <img src="../../imagenes/mecanica2.png" class="specialty-3">
                    <img src="../../imagenes/mecanica3.png" class="specialty-3">
                    <img src="../../imagenes/mecanica4.png" class="specialty-3">
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Galeria -->
    <section id="galeria" class="galeria">
        <h2 class="galerias-title">Galería</h2>
        <hr class="galerias-line">
        <div class="galeria-grid">
            <!-- Videos -->
            <div class="item">
                <video autoplay muted loop>
                    <source src="../../imagenes/video1.mp4" type="video/mp4">
                    Tu navegador no soporta el formato de video.
                </video>
            </div>
            <div class="item">
                <video autoplay muted loop>
                    <source src="../../imagenes/video2.mp4" type="video/mp4">
                    Tu navegador no soporta el formato de video.
                </video>
            </div>
            <div class="item">
                <video autoplay muted loop>
                    <source src="../../imagenes/video3.mp4" type="video/mp4">
                    Tu navegador no soporta el formato de video.
                </video>
            </div>

            <!-- Imágenes -->
            <div class="item">
                <img src="../../imagenes/imagen-u5.jpeg" alt="Imagen 1">
            </div>
            <div class="item">
                <img src="../../imagenes/imagen-u2.jpeg" alt="Imagen 2">
            </div>
            <div class="item">
                <img src="../../imagenes/imagen-u6.jpeg" alt="Imagen 3">
            </div>
            <div class="item">
                <img src="../../imagenes/imagen-u4.jpeg" alt="Imagen 4">
            </div>
            <div class="item">
                <img src="../../imagenes/imagen-u.jpeg" alt="Imagen 5">
            </div>
            <div class="item">
                <img src="../../imagenes/imagen-u3.jpeg" alt="Imagen 6">
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

    <script>
    // Banner rotatorio
    let bannerImages = document.querySelectorAll('.banner img');
    let bannerText = document.getElementById('bannerText');
    let currentImage = 0;
    let phrases = [
        "Unidad Educativa Benjamín Franklin: El lugar donde comienza tu futuro técnico",
        "El conocimiento técnico que necesitas, con la excelencia que mereces",
        "Formamos técnicos expertos, listos para construir el futuro con innovación"
    ];
    let currentPhrase = 0;
    setInterval(() => {
        bannerImages[currentImage].classList.remove('active');
        currentImage = (currentImage + 1) % bannerImages.length;
        bannerImages[currentImage].classList.add('active');
        currentPhrase = (currentPhrase + 1) % phrases.length;
        bannerText.innerText = phrases[currentPhrase];
    }, 4000);

    // Función para ampliar imágenes
    function expandImage(image) {
        const newWindow = window.open("", "_blank");
        newWindow.document.write(`<img src="${image.src}" style="width:100%;">`);
    }

    // Información de cada especialidad
    const especialidades = {
        1: {
            title: "Electrónica de Consumo",
            info: "La especialización en esta área de la Unidad Educativa Benjamín Franklin capacita a los estudiantes en el diseño, reparación y mantenimiento de dispositivos electrónicos. Con un enfoque práctico y el uso de tecnología de punta, ofrecemos una formación integral que combina teoría con experiencia real, preparando a los jóvenes para destacar en el ámbito doméstico y comercial."
        },
        2: {
            title: "Electricidad",
            info: "La especialización en electricidad capacita a los estudiantes en el diseño, fabricación, reparación y mantenimiento de sistemas eléctricos para el hogar y el comercio. Integramos una base teórica sólida con prácticas intensivas y tecnología de vanguardia, brindando a los alumnos las habilidades necesarias para sobresalir desde una etapa temprana en su formación."
        },
        3: {
            title: "Mecánica Automotriz",
            info: "Esta especialización capacita a los estudiantes en el diagnóstico, mantenimiento y reparación de sistemas de vehículos. A través de una formación que integra teoría y prácticas intensivas con tecnología de vanguardia, preparamos a los jóvenes para enfrentar con éxito los retos del sector automotriz."
        }
    };

    // Función para abrir el modal
    function openModal(id) {
        const modal = document.getElementById("modal");
        const modalTitle = document.getElementById("modal-title");
        const modalInfo = document.getElementById("modal-info");
        const carouselTrack = document.querySelector(".carousel-track");

        // Cargar título e información según la especialidad
        modalTitle.textContent = especialidades[id].title;
        modalInfo.textContent = especialidades[id].info;

        // Mostrar solo las imágenes correspondientes a la especialidad
        const allImages = carouselTrack.querySelectorAll("img");
        allImages.forEach(img => {
            img.style.display = img.classList.contains(`specialty-${id}`) ? "block" : "none";
        });

        // Asegurarse de que el modal tenga una transición suave
        modal.style.transition = "opacity 0.5s ease-in-out";
        modal.style.opacity = 1; // Hacerlo visible
        modal.style.display = "flex"; // Asegurarse de que se muestre como flex
    }

    // Función para cerrar el modal
    function closeModal() {
        const modal = document.getElementById("modal");
        modal.style.transition = "opacity 0.5s ease-in-out"; // Desvanecimiento de cierre
        modal.style.opacity = 0;
        setTimeout(() => {
            modal.style.display = "none"; // Esconde el modal después de la animación
        }, 500); // Espera que termine la transición de opacidad antes de ocultarlo
    }
    </script>
</body>

</html>