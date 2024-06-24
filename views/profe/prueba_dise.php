<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colegio XYZ</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Estilos del banner */
        .banner {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 50px 0;
        }

        .banner h1 {
            font-size: 3em;
            margin-bottom: 10px;
        }

        .banner p {
            font-size: 1.2em;
        }

        /* Estilos de las secciones */
        section {
            padding: 40px 0;
        }

        section h2 {
            font-size: 2em;
            margin-bottom: 20px;
        }

        section p {
            font-size: 1.1em;
            line-height: 1.6;
        }

        /* Estilos de los logos */
        .logos {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .logos img {
            width: 150px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <!-- Banner -->
    <header class="banner">
        <h1>Colegio XYZ</h1>
        <p>Formando mentes brillantes desde 1990</p>
    </header>

    <!-- Historia del Colegio -->
    <section class="historia">
        <h2>Historia del Colegio</h2>
        <p>Breve descripción histórica del colegio...</p>
    </section>

    <!-- Especialidades -->
    <section class="especialidades">
        <h2>Especialidades</h2>
        <ul>
            <li>Especialidad 1</li>
            <li>Especialidad 2</li>
            <li>Especialidad 3</li>
        </ul>
    </section>

    <!-- Equipo Directivo -->
    <section class="equipo">
        <h2>Equipo Directivo</h2>
        <p>Conoce a nuestro equipo directivo...</p>
    </section>

    <!-- Actividades Extracurriculares -->
    <section class="actividades">
        <h2>Actividades Extracurriculares</h2>
        <p>Descubre las actividades que ofrecemos...</p>
    </section>

    <!-- Logos de Convenios -->
    <section class="convenios">
        <h2>Logos de Convenios</h2>
        <div class="logos">
            <img src="logo1.png" alt="Logo 1">
            <img src="logo2.png" alt="Logo 2">
            <img src="logo3.png" alt="Logo 3">
        </div>
    </section>

    <!-- Importar Anime.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

    <!-- Animaciones -->
    <script>
        // Animación para el banner
        anime({
            targets: '.banner',
            translateY: [-50, 0],
            opacity: [0, 1],
            easing: 'easeOutExpo',
            duration: 1500,
            delay: 500
        });

        // Animaciones para las secciones
        anime({
            targets: 'section',
            translateY: [50, 0],
            opacity: [0, 1],
            easing: 'easeOutExpo',
            duration: 1500,
            delay: anime.stagger(200)
        });
    </script>
</body>
</html>