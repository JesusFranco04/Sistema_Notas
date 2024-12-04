<?php
session_start();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA DE GESTIÓN UEBF | PROFESOR</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            color: #333;
        }
        header {
            background-color: #E62433;
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
        }
        header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
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
        main {
            padding: 20px;
            text-align: center;
        }
        .content-box {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .content-box h2 {
            color: #E62433;
            font-size: 22px;
        }
        .hero {
            padding: 150px 30px;
            border-radius: 10px;
            background: url('../../imagenes/banner.jpg') no-repeat center center/cover;
            color: #fff; /* Color de texto blanco para mejor contraste */
            text-align: center; /* Centrar el texto */
        }

        .hero h2 {
            font-size: 2.5em; /* Tamaño de fuente grande para el título */
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7); /* Sombra del texto para dar efecto de resalto */
            color: #FFFFFF; /* Color del texto blanco */
        }


        .hero p {
            font-size: 1.2em; /* Tamaño de fuente del párrafo */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Sombra del texto para mejorar la legibilidad */
        }

        .carousel-container {
            position: relative;
            max-width: 900px;
            margin: 0 auto;
            overflow: hidden;
        }

        .carousel-container {
            position: relative;
            max-width: 900px;
            margin: 0 auto;
            overflow: hidden;
        }

        .carousel-images {
            display: flex;
            transition: transform 0.5s ease-in-out;
            will-change: transform; /* Optimiza la transición */
        }

        .carousel-images img {
            width: 100%;
            max-width: 200px; /* Tamaño máximo de las imágenes */
            margin: 0 10px; /* Espacio entre las imágenes */
            flex-shrink: 0; /* Evita que las imágenes se encojan */
        }


        .carousel-images img {
            width: 100%;
            max-width: 200px; /* Tamaño máximo de las imágenes */
            margin: 0 10px; /* Espacio entre las imágenes */
            flex-shrink: 0; /* Evita que las imágenes se encojan */
        }
        
        .timeline {
            position: relative; /* Posiciona el contenedor de manera relativa para permitir la colocación absoluta de los elementos hijos dentro de él */
            width: calc(100% - 40px); /* Ancho del 100% del contenedor padre menos 40 píxeles (20px de padding a cada lado) para respetar el padding definido en el contenedor principal */
            height: calc(100vh - -230px); /* Alto del 100% de la ventana gráfica (viewport height) menos 40 píxeles (20px de padding en la parte superior e inferior) para ajustar el tamaño a la ventana visible del navegador */
            max-width: 100%; /* Establece que el contenedor puede tener hasta el 100% del ancho disponible sin restricción adicional de ancho máximo */
            margin: 0 auto; /* Centra el contenedor horizontalmente en su contenedor padre, utilizando márgenes automáticos a los lados */
            padding: 20px; /* Añade un espacio interno de 20 píxeles alrededor del contenido del contenedor */
            border-left: 4px solid #E62433; /* Agrega un borde izquierdo sólido de 4 píxeles de grosor y color rojo (#E62433) */
            background-color: #f9f9f9; /* Establece un color de fondo gris claro (#f9f9f9) para el contenedor */
            border-radius: 10px; /* Redondea las esquinas del contenedor con un radio de 10 píxeles */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); /* Aplica una sombra difusa al contenedor con un desplazamiento vertical de 6 píxeles, un desenfoque de 12 píxeles, y una opacidad de 0.2 */
            display: flex; /* Usa el modelo de caja flexible para los elementos hijos, permitiendo una distribución flexible del espacio */
            flex-direction: column; /* Organiza los elementos hijos en una columna verticalmente */
            gap: 20px; /* Establece un espacio de 20 píxeles entre los elementos hijos del contenedor */
        }


        .timeline-item {
            position: relative;
            margin: 20px 0;
            padding-left: 60px;
            animation: fadeIn 1s ease-out;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -20px;
            width: 24px;
            height: 24px;
            background-color: #E62433;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            border: 4px solid #ffffff;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        .timeline-item:hover::before {
            transform: scale(1.2);
        }

        .timeline-content {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .timeline-content:hover {
            background-color: #ffe1e3;
        }

        .timeline-content h2 {
            color: #E62433;
            margin-top: 0;
            font-size: 1.6em; /* Tamaño del texto para los títulos */
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .timeline-content p {
            color: #333;
            line-height: 1.6;
            font-size: 1.1em; /* Tamaño del texto para los párrafos */
        }

        .timeline-content ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .timeline-content ul li {
            margin-bottom: 10px;
            font-size: 1.2em; /* Tamaño del texto para los ítems de la lista */
            color: #E62433;
            font-weight: bold;
            position: relative;
            padding-left: 30px;
        }

        .timeline-content ul li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #E62433;
            font-size: 1.5em; /* Tamaño del símbolo en la lista */
            line-height: 1.2em;
        }

        .timeline-item:nth-child(2) {
            margin-left: auto;
            padding-right: 60px;
            padding-left: 0;
            text-align: right;
        }

        .timeline-item:nth-child(2)::before {
            left: auto;
            right: -20px;
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
            if (isset($_SESSION['cedula']) && isset($_SESSION['rol'])) {
                echo htmlspecialchars($_SESSION['cedula']);  // Escapa caracteres especiales para seguridad
                echo " | " . htmlspecialchars($_SESSION['rol']) . " ";
                echo "<i class='bx bx-user-circle'></i>";
            }
            ?>
        </h1>
        <p>Gestiona tus actividades y calificaciones de manera sencilla</p>
        <button class="logout-button" onclick="window.location.href='http://localhost/sistema_notas/login.php'">Cerrar Sesión</button>
    </div> 
</header>

    <main>
        <!-- Espacio para el contenido de la página Home -->
        <div class="content-box">
            <section class="hero">
                <h2>Unidad Educativa Benjamin Franklin</h2>
                <p>Un lugar dedicado a la excelencia académica y al desarrollo integral de nuestros estudiantes.</p>
            </section>
            <section class="info-section">
                <h2>¿Está buscando una educación que prepare a sus hijos para un futuro exitoso?</h2>
                <p>En la Unidad Educativa Particular Benjamín Franklin, ofrecemos una educación de excelencia que va más allá de lo académico:</p>
                <ul class="info-list">
                    <li><strong>Excelencia Académica:</strong> Formamos a los estudiantes en áreas como electrónica, electricidad y mecánica automotriz, desarrollando habilidades que los destacan en un mundo competitivo.</li>
                    <li><strong>Desarrollo Integral:</strong> Fomentamos el crecimiento personal y los valores de respeto, tolerancia y responsabilidad, asegurando una formación completa.</li>
                    <li><strong>Práctica y Experiencia Real:</strong> Combinamos teoría con práctica en laboratorios avanzados y proyectos reales, preparando a los estudiantes para enfrentar desafíos reales.</li>
                    <li><strong>Educación Inclusiva:</strong> Adaptamos nuestro enfoque a las necesidades individuales, creando un ambiente donde cada niño se siente valorado y apoyado.</li>
                    <li><strong>Conexiones y Oportunidades:</strong> Facilitamos prácticas y pasantías mediante alianzas con la comunidad y el sector productivo, preparando a los estudiantes para el mundo laboral.</li>
                    <li><strong>Compromiso con la Sostenibilidad:</strong> Promovemos proyectos ecológicos que enseñan habilidades técnicas y conciencia ambiental.</li>
                </ul>
                <p>En la Unidad Educativa Particular Benjamín Franklin, formamos líderes del futuro con una educación integral que les abre puertas al éxito. ¡Descubra cómo podemos contribuir al futuro brillante de sus hijos!</p>
                    <div class="carousel-container">
                        <div class="carousel-images">
                            <img src="..\..\imagenes\imagen1.jpeg" alt="Imagen 1">
                            <img src="..\..\imagenes\imagen2.jpeg" alt="Imagen 2">
                            <img src="..\..\imagenes\imagen3.jpeg" alt="Imagen 3">
                            <img src="..\..\imagenes\imagen4.jpeg" alt="Imagen 4">
                            <img src="..\..\imagenes\imagen5.jpeg" alt="Imagen 5">
                            <!-- Duplicar las imágenes para el efecto de bucle infinito -->
                            <img src="..\..\imagenes\imagen1.jpeg" alt="Imagen 1">
                            <img src="..\..\imagenes\imagen2.jpeg" alt="Imagen 2">
                            <img src="..\..\imagenes\imagen3.jpeg" alt="Imagen 3">
                            <img src="..\..\imagenes\imagen4.jpeg" alt="Imagen 4">
                            <img src="..\..\imagenes\imagen5.jpeg" alt="Imagen 5">
                        </div>
                        <div class="carousel-nav">
                            <button onclick="prevImage()">&#10094;</button>
                            <button onclick="nextImage()">&#10095;</button>
                        </div>
                    </div>
    
        </div> 
    </main>

<!-- Sección de Cursos -->
<section style="background-color: #003366; color: white; padding: 40px 20px; text-align: center; width: 100%; margin: 0; border-top: 3px solid #E62433; border-bottom: 3px solid #E62433;">
    <h2 style="font-size: 28px; margin-bottom: 20px;">Gestione y Revise sus Cursos</h2>
    <p style="font-size: 18px; max-width: 800px; margin: 0 auto;">
        Aquí podrá acceder a la lista completa de los cursos que está impartiendo. Revise detalladamente las listas de los estudiantes que se encuentran en sus clases, gestione las calificaciones y mantenga actualizada toda la información necesaria para el seguimiento académico de sus estudiantes.
    </p>
    <a href="http://localhost/sistema_notas/views/profe/curso_profe.php" style="text-decoration: none;">
        <button class="ver-cursos-btn" style="background-color: white; color: #003366; border: none; padding: 15px 30px; font-size: 18px; margin-top: 30px; cursor: pointer; border-radius: 5px;">
            Ver Cursos
        </button>
    </a>
</section>


 
    <main>
    <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Nuestra Visión</h2>
                        <p>Buscamos ser reconocidos por:</p>
                        <ul>
                            <li>Excelencia Académica en áreas técnicas avanzadas.</li>
                            <li>Formación Integral en desarrollo personal y social.</li>
                            <li>Enfoque Práctico con proyectos y laboratorios innovadores.</li>
                            <li>Inclusión y Diversidad en un ambiente de respeto y apoyo.</li>
                            <li>Vinculación con la Comunidad y sector productivo.</li>
                        </ul>
                        <p>Formamos líderes capaces de contribuir al progreso del país a través del conocimiento y el compromiso social.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Nuestra Misión</h2>
                        <p>Proporcionamos una educación de excelencia enfocada en:</p>
                        <ul>
                            <li>Educación de Calidad con un programa riguroso y actualizado.</li>
                            <li>Formación Integral para el crecimiento académico, emocional y ético.</li>
                            <li>Práctica e Innovación con proyectos y actividades extracurriculares.</li>
                            <li>Inclusión y Diversidad en un ambiente de respeto y aceptación.</li>
                            <li>Vínculos con la Comunidad para facilitar la inserción laboral.</li>
                        </ul>
                        <p>Formamos ciudadanos críticos y creativos, preparados para enfrentar desafíos y contribuir al desarrollo sostenible.</p>
                    </div>
                </div>
    </div>
    </main>

<!-- Otros enlaces de CSS/JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>


<script>
    // Animación de las imágenes en el collage
        document.addEventListener('DOMContentLoaded', function () {
            anime({
                targets: '.grid-item',
                opacity: [0, 1],
                duration: 1000,
                delay: anime.stagger(100),
                easing: 'easeInOutQuad'
            });
            let index = 0;
        const images = document.querySelectorAll('.carousel-images img');
        const totalImages = images.length;
        const imageWidth = images[0].clientWidth + 20; // Incluye el margen

        function updateCarousel() {
            images.forEach((img, i) => {
                img.style.opacity = i === index ? '1' : '0.6';
            });
        }
        function prevImage() {
            if (index === 0) {
                index = totalImages / 2 - 1; // Mueve al último de la primera mitad
                document.querySelector('.carousel-images').style.transition = 'none'; // Desactiva transición temporalmente
                document.querySelector('.carousel-images').style.transform = `translateX(-${index * imageWidth}px)`;
                setTimeout(() => {
                    document.querySelector('.carousel-images').style.transition = 'transform 0.5s ease-in-out'; // Reactiva transición
                    index = (index - 1 + totalImages / 2) % (totalImages / 2);
                }, 50); // Asegura que la transición se reactiva después de un breve período
            } else {
                index = (index - 1 + totalImages / 2) % (totalImages / 2);
            }
            updateCarousel();
            adjustScroll();
        }

        function nextImage() {
            index = (index + 1) % (totalImages / 2);
            updateCarousel();
            adjustScroll();
        }

        function adjustScroll() {
            const container = document.querySelector('.carousel-images');
            container.style.transform = `translateX(-${index * imageWidth}px)`;
        }

        document.querySelector('.carousel-nav').addEventListener('click', (event) => {
            if (event.target.textContent === '◁') {
                prevImage();
            } else if (event.target.textContent === '▷') {
                nextImage();
            }
        });

        setInterval(nextImage, 3000); // Cambiar imagen automáticamente cada 3 segundos

        window.onload = () => {
            adjustScroll(); // Inicializar el scroll al cargar
            updateCarousel(); // Inicializar la imagen activa al cargar
        };
    });
</script>
</body>
</html>
