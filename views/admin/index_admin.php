<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA DE GESTIÓN UEBF | ADMINISTRADOR</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f4f4f4;
        }

        .hero {
            padding: 150px 20px;
            background: url('../../imagenes/banner.jpg') no-repeat center center/cover;
            color: #fff; /* Color de texto blanco para mejor contraste */
            text-align: center; /* Centrar el texto */
        }

        .hero h2 {
            font-size: 2.5em; /* Tamaño de fuente grande para el título */
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7); /* Sombra del texto para dar efecto de resalto */
        }

        .hero p {
            font-size: 1.2em; /* Tamaño de fuente del párrafo */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Sombra del texto para mejorar la legibilidad */
        }

        .section {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section h2 {
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        .info-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .info-grid .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            flex: 1;
            min-width: 300px;
            overflow: hidden;
        }

        .info-grid .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .info-grid .card .card-body {
            padding: 20px;
        }

        .info-grid .card h3 {
            margin-top: 0;
            color: #c1121f; /* Rojo bonito */
        }

        .footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 20px;
            position: relative;
        }

	#mission-vision {
	    background-color: #ffffff;
	    padding: 60px 20px;
	    text-align: center;
	    width: 100vw; /* Ocupa todo el ancho de la pantalla */
	    position: relative;
	    overflow: hidden;
	}

	.timeline {
	    position: relative;
	    max-width: 1000px; /* Ancho máximo para el contenido */
	    margin: 0 auto; /* Centra el contenido horizontalmente */
	    padding: 20px;
	    border-left: 4px solid #E62433;
	    background-color: #f9f9f9;
	    border-radius: 10px;
	    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
	    display: flex;
	    flex-direction: column;
	    gap: 20px;
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

        #collage {
            background-color: #ffffff;
            padding: 60px 20px;
            text-align: center;
        }

        .subtitle {
            font-size: 1.5em; /* Tamaño de subtítulo */
            color: #333; /* Color del texto */
            margin-bottom: 20px; /* Espacio debajo del subtítulo */
            font-weight: 700; /* Negrita */
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(2, 300px);
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .grid-item {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            opacity: 0; /* Inicialmente oculto */
            transition: opacity 1s ease-in-out;
        }

        .grid-item img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 10px;
        }

        /* Tamaños específicos para cada imagen */
        .item1 {
            grid-column: span 2; /* Ocupa dos columnas */
            grid-row: span 2; /* Ocupa dos filas */
        }
        
        .item2 {
            grid-column: span 1;
            grid-row: span 1;
        }

        .item3 {
            grid-column: span 2;
            grid-row: span 1;
        }

        .item4 {
            grid-column: span 1;
            grid-row: span 2;
        }

        .item5 {
            grid-column: span 1;
            grid-row: span 1;
        }

        .item6 {
            grid-column: span 2;
            grid-row: span 1;
        }

        .item7 {
            grid-column: span 1;
            grid-row: span 1;
        }

        .item8 {
            grid-column: span 1;
            grid-row: span 1;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        #carousel-section {
            text-align: center;
            padding: 40px 20px;
            background-color: #ffffff;
        }

        #carousel-section h2 {
            font-size: 2em;
            color: #E62433;
            margin-bottom: 20px;
        }

        #carousel-section p {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 40px;
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

        .carousel-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            padding: 0 20px;
        }

        .carousel-nav button {
            background-color: rgba(255, 255, 255, 0.8);
            border: none;
            color: #E62433;
            font-size: 2em;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s;
        }

        .carousel-nav button:hover {
            background-color: rgba(255, 255, 255, 1);
        }
        footer {
        background-color: white; /* Color de fondo blanco */
        color: #737373; /* Color del texto en gris oscuro */
        text-align: center; /* Centrar el texto */
        padding: 20px 0; /* Espaciado interno vertical */
        width: 100%; /* Ancho completo */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Sombra más pronunciada */
        }

        footer p {
            margin: 0; /* Eliminar el margen de los párrafos */
        }
    </style>
</head>
<body>
    <?php include_once 'navbar_admin.php'; ?>
    <section class="hero">
        <h2>Bienvenido, al Sistema de Gestión UEBF</h2>
        <p>Un lugar dedicado a la excelencia académica y al desarrollo integral de nuestros estudiantes.</p>
    </section>
    <section id="carousel-section">
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
        </section>
    <section id="info" class="section">
        <h2>Especialidades</h2>
        <div class="info-grid">
            <div class="card">
                <div id="carouselSobreNosotros" class="carousel slide">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="..\..\imagenes\electronica2.jpeg" alt="Electrónica 1">
                        </div>
                        <div class="carousel-item">
                            <img src="..\..\imagenes\electronica1.jpeg" alt="Electrónica 2">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselSobreNosotros" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselSobreNosotros" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <div class="card-body">
                    <h3>Electrónica</h3>
                    <p>La especialización en Electrónica de Consumo de la Unidad Educativa Benjamín Franklin prepara a los estudiantes en el diseño, reparación y mantenimiento de dispositivos electrónicos. Ofrecemos una educación que combina teoría sólida con prácticas intensivas y tecnología de vanguardia, brindando a los alumnos las habilidades necesarias para destacarse en el ámbito doméstico y comercial.</p>
                </div>
            </div>
            <div class="card">
                <div id="carouselEspecialidades" class="carousel slide">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="..\..\imagenes\electricidad1 (1).jpeg" alt="Especialidades 1">
                        </div>
                        <div class="carousel-item">
                            <img src="..\..\imagenes\electricidad1 (2).jpeg" alt="Especialidades 2">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselEspecialidades" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselEspecialidades" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>

                <div class="card-body">
                    <h3>Electricidad</h3>
                    <p>La especialización en Electrónica de Consumo de la Unidad Educativa Benjamín Franklin enseña a los estudiantes a diseñar, fabricar, reparar y mantener dispositivos electrónicos para el hogar y el comercio. Combinamos una sólida formación teórica con prácticas intensivas y el uso de tecnología avanzada, preparando a los alumnos para destacarse en el ámbito de la electrónica desde una edad temprana.</p>
                </div>
            </div>
            <div class="card">
                <div id="carouselContacta" class="carousel slide">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="..\..\imagenes\mecanica3.jpeg" alt="Contacta con Nosotros 1">
                        </div>
                        <div class="carousel-item">
                            <img src="..\..\imagenes\mecanica.jpeg" alt="Contacta con Nosotros 2">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselContacta" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselContacta" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <div class="card-body">
                    <h3>Mecánica Automotriz</h3>
                    <p>La especialización en Mecánica Automotriz de la Unidad Educativa Benjamín Franklin forma a los estudiantes en el diagnóstico, mantenimiento y reparación de sistemas automotrices. El programa combina teoría y prácticas intensivas con tecnología de punta, preparando a los alumnos para enfrentar los desafíos del sector automotriz.</p>
                </div>
            </div>
    </section>
    <section id="collage">
        <h2 class="subtitle">Galería</h2>
        <div class="grid-container">
            <div class="grid-item item1"><img src="..\..\imagenes\collage1.jpeg" alt="Imagen 1"></div>
            <div class="grid-item item2"><img src="..\..\imagenes\collage2.jpeg" alt="Imagen 2"></div>
            <div class="grid-item item3"><img src="..\..\imagenes\collage3.jpeg" alt="Imagen 3"></div>
            <div class="grid-item item4"><img src="..\..\imagenes\collage4.jpeg" alt="Imagen 4"></div>
            <div class="grid-item item5"><img src="..\..\imagenes\collage5.jpeg" alt="Imagen 5"></div>
            <div class="grid-item item6"><img src="..\..\imagenes\collage6.jpeg" alt="Imagen 6"></div>
            <div class="grid-item item7"><img src="..\..\imagenes\mecanica.jpeg" alt="Imagen 7"></div>
            <div class="grid-item item8"><img src="..\..\imagenes\mecanica.jpeg" alt="Imagen 8"></div>
        </div>
    </section>
</div>
<footer>
    <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.</p>
</footer>
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
    <!-- Bootstrap core JavaScript-->
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
</body>
</html>
