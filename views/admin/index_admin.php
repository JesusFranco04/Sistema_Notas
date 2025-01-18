<?php
session_start();
// Verificar si el usuario ha iniciado sesión y si su rol es "Administrador" o "Superusuario"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
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
    <title>SISTEMA DE GESTIÓN UEBF | ADMINISTRADOR</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <!-- Incluir Lightbox2 CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <!-- Incluir Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS -->
    <style>
    .modal {
        display: none;
        /* Oculto por defecto */
        justify-content: center;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        /* Fondo semitransparente */
        z-index: 9999;
        /* Asegurar que esté por encima de otros elementos */
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 600px;
        width: 90%;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }


    /* === Estilos generales para el cuerpo === */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #ffffff;
        /* Cambiado a blanco puro */
    }

    /* === Estilos del Slider === */
    .slider-container {
        position: relative;
        width: 90%;
        max-width: 1200px;
        height: 400px;
        overflow: hidden;
        border-radius: 15px;
        margin: 20px auto;
        box-shadow: 0px 8px 40px rgba(0, 0, 0, 0.2);
        perspective: 1000px;
    }

    .slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transform: translateX(100%) rotateY(30deg);
        transition: opacity 1s ease, transform 1s ease;
    }

    .slide.active {
        opacity: 1;
        transform: translateX(0) rotateY(0);
        z-index: 1;
    }

    .slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 15px;
    }

    .slider-text {
        position: absolute;
        bottom: 15%;
        left: 50%;
        transform: translateX(-50%);
        color: #fff;
        background-color: #a82926;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 1.5rem;
        text-align: center;
        width: 80%;
        max-width: 90%;
        opacity: 0;
        animation: fadeIn 1.5s ease forwards, fadeOut 1.5s ease forwards 4s;
    }

    .progress-bar {
        position: absolute;
        bottom: 10px;
        left: 10px;
        right: 10px;
        height: 6px;
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background-color: #e70022;
        width: 0;
        animation: progressFill 5s linear forwards;
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
            transform: translateX(-50%) translateY(10px);
        }

        100% {
            opacity: 1;
            transform: translateX(-50%) translateY(-10px);
        }
    }

    @keyframes fadeOut {
        0% {
            opacity: 1;
        }

        100% {
            opacity: 0;
            transform: translateX(-50%) translateY(10px);
        }
    }

    @keyframes progressFill {
        from {
            width: 0;
        }

        to {
            width: 100%;
        }
    }

    /* === Estilos para el título de la sección Especialidades === */
    .section-title {
        text-align: center;
        font-size: 2.5em;
        font-weight: bold;
        color: #07244a;
        /* Azul */
        margin: 30px 0 20px;
        /* Espaciado superior e inferior */
        font-family: 'Arial', sans-serif;
        position: relative;
    }

    /* Línea decorativa debajo del título */
    .section-title::after {
        content: "";
        display: block;
        width: 80px;
        height: 4px;
        background-color: #e70022;
        /* Rojo */
        margin: 10px auto;
        border-radius: 2px;
    }

    /* === Estilos para la sección de Especialidades === */
    .especialidades {
        background-color: white;
        /* Fondo blanco */
        padding: 40px 10px;
        /* Espaciado interno */
        width: 100%;
        border-top: 3px solid #07244a;
        /* Borde superior azul */
        border-bottom: 3px solid #07244a;
        /* Borde inferior azul */
        text-align: center;
    }

    /* === Estilos para los cuadros de especialidades === */
    .contenedor-cuadros {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        /* Permite que los cuadros se ajusten en pantallas pequeñas */
        gap: 20px;
        padding: 25px;
    }

    .cuadro {
        width: 250px;
        /* Tamaño estándar para cuadros */
        height: 250px;
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
        background-image: url('../../imagenes/electronica2.jpeg');
    }

    .cuadro2 {
        background-color: #218838;
        background-image: url('../../imagenes/electricidad1 (2).jpeg');
    }

    .cuadro3 {
        background-color: #0064cb;
        background-image: url('../../imagenes/mecánica_automotriz.png');
    }

    /* Efecto al pasar el cursor */
    .cuadro:hover {
        filter: brightness(1.2);
        transform: scale(1.05);
        /* Efecto de ampliación */
    }


    /* Estilos para contenedor y cuadros */
    .contenedor-cuadros {
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .cuadro {
        width: 200px;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5em;
        font-weight: bold;
        color: white;
        text-align: center;
        position: relative;
        cursor: pointer;
        background-size: cover;
        background-position: center;
        background-blend-mode: overlay;
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


    /* Animación suave para el contenido del modal */
    .modal-content {
        background-color: #fff;
        width: 80%;
        max-width: 800px;
        padding: 0px;
        border-radius: 8px;
        position: relative;
        text-align: center;
        transform: scale(0.8);
        /* Tamaño inicial reducido */
        opacity: 0;
        /* Opacidad inicial */
        animation: scaleIn 0.8s ease-out forwards;
        /* Animación de entrada */
    }

    /* Animación de escalado suave */
    @keyframes scaleIn {
        0% {
            transform: scale(0.8);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
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
        margin-top: 20px;
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

    /* === Sección de Historia: Fondo completamente rojo === */
    .historia-section {
        background-color: #32b54f;
        /* verde oscuro */
        padding: 6rem 2rem;
        /* Aumenté el padding para mayor espacio */
        color: #ffffff;
        /* Texto blanco */
    }

    /* Título de la sección */
    .historia-title {
        font-size: 3rem;
        /* Tamaño ajustado para ser legible en desktop y responsive */
        font-weight: 700;
        text-align: center;
        margin-bottom: 2rem;
        /* Espaciado inferior más claro */
        color: #ffffff;
        /* Blanco */
        text-transform: uppercase;
        letter-spacing: 1.5px;
        /* Ajusté el espaciado de letras */
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
        /* Sombra más sutil */
    }

    /* Subtítulo */
    .historia-subtitle {
        text-align: center;
        font-style: italic;
        color: #ffffff;
        /* Blanco */
        font-size: 1.5rem;
        /* Mejor escala para subtítulos */
        margin-bottom: 2.5rem;
        /* Espaciado inferior consistente */
        letter-spacing: 1px;
    }

    /* Tarjetas de historia */
    .historia-card {
        background-color: rgba(0, 0, 0, 0.3);
        /* Fondo oscuro semitransparente */
        border-radius: 12px;
        /* Ligera curvatura para un diseño moderno */
        padding: 2.5rem;
        /* Espaciado interno ajustado */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        /* Sombra más ligera */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 2rem;
        /* Espaciado inferior entre tarjetas */
    }

    /* Efecto hover en las tarjetas */
    .historia-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        /* Sombra más pronunciada */
    }

    /* Preguntas destacadas */
    .historia-question {
        font-size: 2rem;
        /* Tamaño adecuado para visibilidad */
        font-weight: 600;
        color: #ffffff;
        /* Blanco */
        text-align: center;
        margin-bottom: 1.5rem;
        /* Espaciado ajustado */
        text-transform: uppercase;
    }

    /* Texto descriptivo */
    .historia-text {
        font-size: 1.2rem;
        /* Mayor legibilidad */
        line-height: 1.8;
        /* Separación de líneas cómoda */
        color: #ffffff;
        /* Blanco */
        text-align: justify;
        margin-bottom: 1.5rem;
        /* Espaciado inferior uniforme */
        font-weight: 300;
        /* Peso ligero para elegancia */
    }

    /* === Botón de acción (ajustes responsivos) === */
    .btn-descubre {
        background-color: #e7e5e4;
        /* Fondo claro */
        color: #07244a;
        /* Azul oscuro */
        font-weight: bold;
        border: none;
        padding: 1rem 2.5rem;
        /* Botón más amplio */
        text-transform: uppercase;
        border-radius: 40px;
        /* Bordes redondeados para diseño moderno */
        transition: background-color 0.3s ease, transform 0.2s ease;
        display: block;
        max-width: 90%;
        /* Limita el ancho máximo del botón */
        width: fit-content;
        /* Ajusta el tamaño al texto */
        margin: 2.5rem auto 0;
        /* Centrado y espaciado superior */
        text-align: center;
        /* Asegura el texto centrado */
    }

    /* Efecto hover en el botón */
    .btn-descubre:hover {
        background-color: #ffffff;
        /* Fondo blanco */
        color: #07244a;
        /* Azul oscuro */
        transform: scale(1.05);
        /* Efecto de aumento */
    }

    /* === Responsividad para pantallas pequeñas === */
    @media (max-width: 768px) {
        .btn-descubre {
            padding: 0.8rem 2rem;
            /* Reduce el padding para pantallas pequeñas */
            font-size: 0.9rem;
            /* Ajusta el tamaño del texto */
            max-width: 100%;
            /* Asegura que no se salga del contenedor */
        }
    }

    @media (max-width: 480px) {
        .btn-descubre {
            padding: 0.7rem 1.5rem;
            /* Ajusta aún más el padding */
            font-size: 0.85rem;
            /* Tamaño más pequeño para texto */
        }
    }


    /* Estilos de la galería de imágenes */
    .lightbox-gallery {
        display: flex;
        flex-wrap: nowrap;
        justify-content: center;
        gap: 20px;
        overflow-x: auto;
        margin-top: 3rem;
    }

    .lightbox-gallery a {
        display: block;
        width: 220px;
        height: 160px;
        overflow: hidden;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .lightbox-gallery img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
    }

    .lightbox-gallery img:hover {
        transform: scale(1.1);
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    }

    /* Estilos para el botón de chevron-up */
    .chevron-up {
        font-size: 2rem;
        color: #ffffff;
        /* Azul claro */
        cursor: pointer;
        display: none;
        text-align: center;
        margin-top: 2rem;
        transition: transform 0.3s ease;
    }

    .chevron-up:hover {
        transform: translateY(-5px);
    }

    /* === Sección de Misión y Visión === */
    .vision-mission-section {
        padding: 50px;
        background-color: #32b54f;
        /* Fondo verde */
        display: flex;
        flex-wrap: wrap;
        /* Diseño adaptativo */
        justify-content: center;
        gap: 30px;
        /* Separación entre tarjetas */
    }

    /* === Tarjetas === */
    .card {
        background: #ffffff;
        /* Fondo blanco */
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
        color: #07244a;
        /* Azul oscuro */
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
        background: linear-gradient(to right, #218838, #07244a);
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
        color: #218838;
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
        background: #218838;
        /* Verde */
        transform: scale(1.2);
    }

    /* Tooltip */
    .tooltip {
        position: absolute;
        top: 75px;
        left: 50%;
        transform: translateX(-50%);
        background: #07244a;
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

    /* === Estilos de la galería === */
    .gallery-section {
        width: 100%;
        max-width: 1200px;
        /* Limitar ancho máximo */
        margin: 50px auto;
        /* Centrado vertical y horizontal */
        padding: 20px;
        /* Espaciado equilibrado */
        background-color: #fff;
        /* Fondo blanco */
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        /* Sombra suave alrededor */
    }

    .section-title {
        text-align: center;
        font-size: 2rem;
        font-weight: bold;
        color: #07244a;
        /* Color para el título */
        margin: 30px 0 20px;
        position: relative;
    }

    .section-title::after {
        content: "";
        display: block;
        width: 100px;
        height: 6px;
        background-color: #e70022;
        /* Barra roja debajo del título */
        margin: 10px auto;
        border-radius: 3px;
    }

    .gallery-header p {
        font-size: 1rem;
        color: #777;
        /* Texto gris claro */
        margin-bottom: 30px;
        text-align: center;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(250px, 1fr));
        /* 4 columnas adaptativas */
        gap: 20px;
        /* Espaciado uniforme */
        justify-content: center;
        /* Centrar contenido horizontalmente */
        justify-items: center;
        /* Centrar cada cuadro dentro de su celda */
        padding: 0 20px;
        /* Espaciado horizontal */
    }

    .cuadro {
        width: 100%;
        /* Ancho completo del contenedor */
        aspect-ratio: 1;
        /* Mantiene proporción cuadrada automáticamente */
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        /* Sombra suave */
        transition: transform 0.3s ease;
    }

    .cuadro img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* Ajustar imagen */
        transition: opacity 0.3s ease, transform 0.3s ease;
        border-radius: 10px;
    }

    .cuadro .image-default {
        opacity: 1;
    }

    .cuadro .image-hover {
        opacity: 0;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transition: opacity 0.3s ease, box-shadow 0.3s ease;
    }

    .cuadro:hover {
        transform: scale(1.05);
        /* Efecto hover */
    }

    .cuadro:hover .image-default {
        opacity: 0;
    }

    .cuadro:hover .image-hover {
        opacity: 1;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        /* Sombra más pronunciada */
    }

    .specialty-name {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0;
        transition: opacity 0.3s ease;
        padding: 5px 8px;
        background-color: rgba(0, 0, 0, 0.6);
        /* Fondo semitransparente */
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .cuadro:hover .specialty-name {
        opacity: 1;
    }

    /* === Estilos responsivos === */

    /* Tablets (pantallas medianas) */
    @media (max-width: 1024px) {
        .gallery-grid {
            grid-template-columns: repeat(2, minmax(200px, 1fr));
            /* 2 columnas adaptativas */
            gap: 15px;
        }

        .cuadro {
            aspect-ratio: 1;
            /* Mantener proporción cuadrada */
        }

        .section-title {
            font-size: 1.8rem;
            /* Título más pequeño */
        }

        .gallery-header p {
            font-size: 0.9rem;
            /* Texto más compacto */
        }
    }

    /* Celulares (pantallas pequeñas) */
    @media (max-width: 768px) {
        .gallery-grid {
            grid-template-columns: 1fr;
            /* 1 columna */
            gap: 10px;
            padding: 0 10px;
            /* Menor espacio horizontal */
        }

        .cuadro {
            aspect-ratio: 1;
            /* Mantener proporción cuadrada */
        }

        .section-title {
            font-size: 1.5rem;
            /* Título más pequeño */
        }

        .gallery-header p {
            font-size: 0.8rem;
            /* Texto más compacto */
        }

        .specialty-name {
            font-size: 0.9rem;
            /* Texto más pequeño */
            padding: 5px;
            /* Espaciado ajustado */
        }
    }

    footer {
        background-color: white;
        /* Color de fondo blanco */
        color: #737373;
        /* Color del texto en gris oscuro */
        text-align: center;
        /* Centrar el texto */
        padding: 20px 0;
        /* Espaciado interno vertical */
        width: 100%;
        /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }
    </style>
</head>

<body>
    <?php include_once 'navbar_admin.php'; ?>
    <!-- Slider principal -->
    <div class="slider-container">
        <div class="slide active">
            <img src="../../imagenes/tu-imagen10.jpg" alt="Estudiantes en la Unidad Educativa San Francisco"
                loading="lazy">
            <div class="slider-text">Unidad Educativa Benjamín Franklin: El lugar donde comienza tu futuro técnico</div>
            <div class="progress-bar">
                <div class="progress-bar-fill"></div>
            </div>
        </div>
        <div class="slide">
            <img src="../../imagenes/tu-imagen20.jpg" alt="Educación con esfuerzo y buen trato" loading="lazy">
            <div class="slider-text">El conocimiento técnico que necesitas, con la excelencia que mereces</div>
            <div class="progress-bar">
                <div class="progress-bar-fill"></div>
            </div>
        </div>
        <div class="slide">
            <img src="../../imagenes/tu-imagen30.jpg" alt="Juntos construimos el futuro" loading="lazy">
            <div class="slider-text">Formamos técnicos expertos, listos para construir el futuro con innovación</div>
            <div class="progress-bar">
                <div class="progress-bar-fill"></div>
            </div>
        </div>
    </div>

    <!-- Sección de Historia -->
    <section class="historia-section">
        <div class="container">
            <h2 class="historia-title">Nuestra Historia</h2>
            <p class="historia-subtitle">"55 años formando jóvenes con valores y excelencia técnica para su
                futuro"</p>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="historia-card">
                        <h3 class="historia-question">¿Desea una formación completa que garantice el futuro exitoso de
                            su hijo?"</h3>
                        <p class="historia-text">
                            En la Unidad Educativa Benjamín Franklin, con más de 55 años de trayectoria, ofrecemos una
                            formación técnica de primer nivel en áreas como Electrónica de Consumo, Electricidad y
                            Mecánica Automotriz. Sabemos que la mejor inversión que puede hacer por el futuro de su hijo
                            es una educación que combine teoría y práctica. Nuestra metodología 'aprender haciendo'
                            asegura que sus hijos no solo adquieran conocimientos, sino que los apliquen en escenarios
                            reales.
                        </p>
                        <p class="historia-text">
                            Desde octavo de básica hasta tercero de bachillerato, nos enfocamos en desarrollar
                            integralmente a nuestros estudiantes, preparándolos para los desafíos del mundo laboral.
                            Gracias a nuestros convenios con empresas, sus hijos tendrán la oportunidad de realizar
                            pasantías y proyectos prácticos, además de participar en visitas a museos y empresas, donde
                            podrán consolidar lo aprendido en clase.
                        </p>
                        <p class="historia-text">
                            Como institución privada, nuestra misión es brindar una formación completa que va más allá
                            de lo académico. Fomentamos el arte y el deporte, elementos esenciales para el desarrollo
                            personal de los estudiantes. Bajo la dirección de nuestra rectora, Alexandra de Rocío Ruano
                            Sánchez, nos comprometemos a proporcionar una educación que no solo sea excelente, sino que
                            también prepare a sus hijos para una vida exitosa y llena de oportunidades.
                        </p>
                        <a href="#" class="btn-descubre" id="descubre-btn">Descubre más sobre nuestra filosofía</a>
                        <!-- Galería de imágenes en Lightbox -->
                        <div class="lightbox-gallery d-none">
                            <a href="..\..\imagenes\imagen1.jpeg" data-lightbox="galeria" data-title="Imagen 1"><img
                                    src="..\..\imagenes\imagen1.jpeg" alt="Imagen 1"></a>
                            <a href="..\..\imagenes\imagen2.jpeg" data-lightbox="galeria" data-title="Imagen 2"><img
                                    src="..\..\imagenes\imagen2.jpeg" alt="Imagen 2"></a>
                            <a href="..\..\imagenes\imagen3.jpeg" data-lightbox="galeria" data-title="Imagen 3"><img
                                    src="..\..\imagenes\imagen3.jpeg" alt="Imagen 3"></a>
                            <a href="..\..\imagenes\imagen4.jpeg" data-lightbox="galeria" data-title="Imagen 4"><img
                                    src="..\..\imagenes\imagen4.jpeg" alt="Imagen 4"></a>
                            <a href="..\..\imagenes\imagen5.jpeg" data-lightbox="galeria" data-title="Imagen 5"><img
                                    src="..\..\imagenes\imagen5.jpeg" alt="Imagen 5"></a>
                            <a href="..\..\imagenes\imagen6.jpeg" data-lightbox="galeria" data-title="Imagen 6"><img
                                    src="..\..\imagenes\imagen6.jpeg" alt="Imagen 6"></a>
                            <a href="..\..\imagenes\imagen7.jpeg" data-lightbox="galeria" data-title="Imagen 7"><img
                                    src="..\..\imagenes\imagen7.jpeg" alt="Imagen 7"></a>
                            <a href="..\..\imagenes\imagen8.jpeg" data-lightbox="galeria" data-title="Imagen 8"><img
                                    src="..\..\imagenes\imagen8.jpeg" alt="Imagen 8"></a>
                        </div>
                        <!-- Chevron-up (icono para regresar al inicio) -->
                        <div class="chevron-up" id="chevron-up">
                            <i class="bi bi-chevron-up"></i> <!-- Ícono de chevron hacia arriba -->
                        </div>
                    </div>
                </div>
    </section>

    <!-- Sección de Especialidades -->
    <h1 class="section-title">ESPECIALIDADES</h1>
    <div class="contenedor-cuadros">
        <div class="cuadro cuadro1" onclick="openModal(1)">Electrónica</div>
        <div class="cuadro cuadro2" onclick="openModal(2)">Electricidad</div>
        <div class="cuadro cuadro3" onclick="openModal(3)">Mecánica Automotriz</div>
    </div>

    <!-- Modal para cada especialidad -->
    <div class="modal" id="modal">
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

    <div class="gallery-section">
        <!-- Título de la sección Galería -->
        <div class="section-title">
            GALERÍA
        </div>

        <div class="gallery-header">
            <p>Explore nuestra galería y descubra las diversas opciones académicas que brindamos para el desarrollo
                integral de sus hijos.</p>
        </div>

        <div class="gallery-grid">
            <!-- Cuadro de Electrónica -->
            <div class="cuadro">
                <img src="../../imagenes/collage-electrónica1.png" alt="Electrónica" class="image-default">
                <img src="../../imagenes/collage-electrónica2.png" alt="Electrónica Hover" class="image-hover">
                <div class="specialty-name">Electrónica</div>
            </div>

            <div class="cuadro">
                <img src="../../imagenes/collage-electrónica3.png" alt="Electrónica" class="image-default">
                <img src="../../imagenes/collage-electrónica4.png" alt="Electrónica Hover" class="image-hover">
                <div class="specialty-name">Electrónica</div>
            </div>

            <!-- Cuadro de Electricidad -->
            <div class="cuadro">
                <img src="../../imagenes/collage-electridad1.png" alt="Electricidad" class="image-default">
                <img src="../../imagenes/collage-electridad2.png" alt="Electricidad Hover" class="image-hover">
                <div class="specialty-name">Electricidad</div>
            </div>

            <div class="cuadro">
                <img src="../../imagenes/collage-electridad3.png" alt="Electricidad" class="image-default">
                <img src="../../imagenes/collage-electridad4.png" alt="Electricidad Hover" class="image-hover">
                <div class="specialty-name">Electricidad</div>
            </div>

            <!-- Cuadro de Mecánica Automotriz -->
            <div class="cuadro">
                <img src="../../imagenes/collage-mecanica_automotriz1.png" alt="Mecánica Automotriz"
                    class="image-default">
                <img src="../../imagenes/collage-mecanica_automotriz2.png" alt="Mecánica Automotriz Hover"
                    class="image-hover">
                <div class="specialty-name">Mecánica Automotriz</div>
            </div>

            <div class="cuadro">
                <img src="../../imagenes/collage-mecanica_automotriz3.png" alt="Mecánica Automotriz"
                    class="image-default">
                <img src="../../imagenes/collage-mecanica_automotriz4.png" alt="Mecánica Automotriz Hover"
                    class="image-hover">
                <div class="specialty-name">Mecánica Automotriz</div>
            </div>

            <!-- Cuadro de Promoción -->
            <div class="cuadro">
                <img src="../../imagenes/collage-promocion1.png" alt="Promoción" class="image-default">
                <img src="../../imagenes/collage-promocion2.png" alt="Promoción Hover" class="image-hover">
                <div class="specialty-name">Promoción Escolar</div>
            </div>

            <div class="cuadro">
                <img src="../../imagenes/collage-promocion3.png" alt="Promoción" class="image-default">
                <img src="../../imagenes/collage-promocion4.png" alt="Promoción Hover" class="image-hover">
                <div class="specialty-name">Promoción Escolar</div>
            </div>
        </div>
    </div>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

    <!-- Incluir jQuery, Popper.js y Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <!-- Incluir Lightbox2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>


    <script>
    // Mostrar la galería de imágenes al hacer clic en el botón
    document.getElementById('descubre-btn').addEventListener('click', function(e) {
        e.preventDefault();
        // Mostrar la galería y ocultar el botón
        document.querySelector('.lightbox-gallery').classList.remove('d-none');
        document.getElementById('descubre-btn').classList.add('d-none');
        // Mostrar el ícono de Chevron
        document.getElementById('chevron-up').style.display = 'block';
    });

    // Ocultar la galería de imágenes y volver al estado inicial cuando se haga clic en el ícono de Chevron hacia arriba
    document.getElementById('chevron-up').addEventListener('click', function() {
        // Ocultar la galería y mostrar el botón
        document.querySelector('.lightbox-gallery').classList.add('d-none');
        document.getElementById('descubre-btn').classList.remove('d-none');
        // Ocultar el botón de chevron-up
        document.getElementById('chevron-up').style.display = 'none';
    });

    // Script del slider principal
    document.addEventListener('DOMContentLoaded', () => {
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const intervalTime = 5000;

        function changeSlide() {
            slides[currentSlide].classList.remove('active');
            slides[currentSlide].querySelector('.progress-bar-fill').style.animation = 'none';
            slides[currentSlide].querySelector('.slider-text').style.animation = 'none';
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
            slides[currentSlide].querySelector('.progress-bar-fill').style.animation =
                'progressFill 5s linear forwards';
            slides[currentSlide].querySelector('.slider-text').style.animation =
                'fadeIn 1.5s ease forwards, fadeOut 1.5s ease forwards 4s';
        }

        setInterval(changeSlide, intervalTime);
    });

    // Información de cada especialidad
    const especialidades = {
        1: {
            title: "Electrónica de Consumo",
            info: "La especialización en esta área de la Unidad Educativa Benjamín Franklin capacita a los estudiantes en el diseño, reparación y mantenimiento de dispositivos electrónicos. Con un enfoque práctico y el uso de tecnología de punta, ofrecemos una formación integral que combina teoría con experiencia real, preparando a los jóvenes para destacar en el ámbito doméstico y comercial."
        },
        2: {
            title: "Electricidad",
            info: "La especialización en Electricidad capacita a los estudiantes en el diseño, fabricación, reparación y mantenimiento de sistemas eléctricos para el hogar y el comercio. Integramos una base teórica sólida con prácticas intensivas y tecnología de vanguardia, brindando a los alumnos las habilidades necesarias para sobresalir desde una etapa temprana en su formación."
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

        // Mostrar el modal
        modal.style.display = "flex";
    }

    // Función para cerrar el modal
    function closeModal() {
        document.getElementById("modal").style.display = "none";
    }
    </script>
</body>

</html>