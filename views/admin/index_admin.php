<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>SISTEMA DE GESTIÓN UEBF | ADMINISTRADOR</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Estilos personalizados -->
    <style>
    /* Aquí va tu código CSS */
    body,
    html {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Estilos para el banner */
    .banner {
        width: 100%;
        height: 400px;
        position: relative;
        overflow: hidden;
    }

    .banner-images {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    .banner-images img {
        max-width: 100%;
        height: auto;
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        z-index: 1;
        transition: opacity 1s ease;
    }

    .banner-images img.active {
        opacity: 1;
        z-index: 2;
    }

    .banner-text {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        font-size: 36px;
        text-align: center;
        opacity: 0;
        transition: opacity 0.5s ease;
        z-index: 3;
        font-family: 'Arial', sans-serif;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .banner-images:hover .banner-text {
        opacity: 1;
    }

    .section-title {
        text-align: center;
        margin-bottom: 40px;
        font-size: 36px;
        color: #444;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .history-section {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
    }

    .history-content {
        flex: 1 1 60%;
        padding: 20px;
    }

    .history-content p {
        line-height: 1.6;
        margin-bottom: 20px;
        font-size: 18px;
    }

    .history-image img {
        max-width: 100%;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .divider {
        width: 80%;
        margin: 40px auto;
        border-top: 2px solid #ccc;
        position: relative;
    }

    .divider::after {
        content: '';
        width: 100px;
        height: 2px;
        background-color: #444;
        position: absolute;
        top: -1px;
        left: 50%;
        transform: translateX(-50%);
    }

    .cards {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        margin-top: 40px;
    }

    .card {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
        max-width: 300px;
        width: 100%;
        text-align: center;
    }

    .card img {
        max-width: 100%;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .tabs {
        display: flex;
        justify-content: center;
        margin: 20px 0;
    }

    .tab-button {
        background-color: #f1f1f1;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .tab-button:hover {
        background-color: #ddd;
    }

    .tab-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
        display: none;
    }

    .tab-content p {
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .tab-content ol {
        padding-left: 20px;
    }

    .tab-content li {
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .history-section {
            flex-direction: column;
        }

        .history-content,
        .history-image {
            flex: 1 1 100%;
            text-align: center;
        }

        .cards {
            flex-direction: column;
            align-items: center;
        }
    }

    .directiva {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    .directivo {
        text-align: center;
        margin: 20px;
        flex: 1 0 30%;
    }

    .directivo img {
        border-radius: 50%;
        width: 150px;
        height: 150px;
        transition: transform 0.3s ease-in-out;
    }

    .directivo p {
        margin: 10px 0;
    }

    .directivo p.nombre {
        font-size: 18px;
        font-weight: bold;
    }

    .directivo p.cargo {
        font-size: 14px;
        color: #444;
    }

    .directivo:hover img {
        transform: scale(1.1);
    }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
</head>


<body>
    <?php
    // Incluye el archivo navbar_admin.php solo una vez desde el mismo directorio
    include_once 'navbar_admin.php';
    ?>

    <!-- Sección del Banner -->
    <div class="banner">
        <div class="banner-images">
            <img class="active" src="http://localhost/sistema_notas/imagenes/banners1.png" alt="Imagen 1">
            <img src="http://localhost/sistema_notas/imagenes/banners2.png" alt="Imagen 2">
            <img src="http://localhost/sistema_notas/imagenes/banners3.png" alt="Imagen 3">
            <div class="banner-text">SISTEMA DE GESTIÓN - UEBF</div>
        </div>
    </div>

    <!-- Sección de la Historia del Colegio -->
    <div class="container">
        <h1 class="section-title">Historia del Colegio</h1>
        <div class="history-section">
            <div class="history-content">
                <p>El Colegio XYZ fue fundado en 1950 con el propósito de ofrecer una educación de calidad a los jóvenes
                    de la región. A lo largo de los años, hemos crecido y evolucionado, siempre manteniendo nuestro
                    compromiso con la excelencia académica y la formación integral de nuestros estudiantes.</p>
                <p>Nuestra historia está marcada por hitos importantes, como la construcción de nuevas instalaciones, la
                    implementación de programas educativos innovadores y la celebración de nuestros logros en el ámbito
                    académico, deportivo y cultural.</p>
                <p>Hoy en día, el Colegio XYZ se enorgullece de ser una institución educativa reconocida por su
                    dedicación al desarrollo de futuros líderes y ciudadanos responsables. Continuamos trabajando para
                    adaptarnos a los cambios y desafíos del mundo moderno, sin perder de vista nuestros valores y
                    tradiciones.</p>
            </div>
            <div class="history-image">
                <img src="http://localhost/sistema_notas/imagenes/Trabalhe-Conosco-S%c3%adrio-Liban%c3%aas-2b.png"
                    alt="Imagen del Colegio">
            </div>
        </div>

        <!-- Línea Divisoria -->
        <div class="divider"></div>

        <div class="tabs">
            <button class="tab-button" onclick="showTab('mission')">Misión</button>
            <button class="tab-button" onclick="showTab('vision')">Visión</button>
        </div>
        <div class="tab-content" id="mission">
            <h1 class="section-title">Misión</h1>
            <p>En la Unidad Educativa Particular Benjamín Franklin, nuestra misión es proporcionar una educación de
                excelencia que forme a estudiantes competentes, éticos y comprometidos con su desarrollo personal y el
                progreso de la sociedad ecuatoriana. En la Unidad Educativa Particular Benjamín Franklin, nuestra misión
                es formar ciudadanos íntegros, críticos y comprometidos con su país, capaces de contribuir al desarrollo
                sostenible y al bienestar de la sociedad ecuatoriana a través del conocimiento, la innovación y el
                servicio. Se sustenta en los siguientes principios:</p>
            <ol>
                <li>Proporcionar una Educación de Calidad: Nos comprometemos a ofrecer un programa educativo riguroso y
                    actualizado, centrado en el desarrollo de habilidades técnicas, científicas y humanísticas que
                    preparen a nuestros estudiantes para enfrentar los desafíos del mundo contemporáneo.</li>
                <li>Promover la Formación Integral: Valoramos el desarrollo integral de nuestros estudiantes, por lo que
                    ofrecemos un ambiente de aprendizaje que fomente el crecimiento académico, emocional, social y
                    ético. Buscamos cultivar el pensamiento crítico, la creatividad, el trabajo en equipo y el liderazgo
                    como pilares fundamentales de su formación.</li>
                <li>Fomentar la Práctica y la Innovación: Reconocemos la importancia de la aplicación práctica del
                    conocimiento y la innovación como motores de cambio. Por ello, proporcionamos oportunidades para que
                    nuestros estudiantes participen en proyectos prácticos, investigaciones científicas y actividades
                    extracurriculares que estimulen su curiosidad y creatividad.</li>
                <li>Promover la Inclusión y la Diversidad: Nos comprometemos a ofrecer una educación inclusiva que
                    atienda a la diversidad de necesidades, intereses y capacidades de nuestros estudiantes. Promovemos
                    un ambiente de respeto, tolerancia y aceptación donde cada individuo se sienta valorado y apoyado en
                    su proceso de aprendizaje.</li>
                <li>Establecer Vínculos con la Comunidad: Reconocemos la importancia de trabajar en colaboración con la
                    comunidad y el sector productivo para enriquecer la experiencia educativa de nuestros estudiantes y
                    facilitar su inserción en el mundo laboral. Buscamos establecer alianzas estratégicas que brinden
                    oportunidades de prácticas estudiantiles, pasantías y empleo futuro.</li>
            </ol>
        </div>

        <div class="tab-content" id="vision" style="display:none;">
            <h1 class="section-title">Visión</h1>
            <p>Nuestra visión educativa se fundamenta en los siguientes pilares:</p>
            <ol>
                <li>Excelencia Académica: Buscamos ser reconocidos por ofrecer una educación de calidad que promueva el
                    pensamiento crítico, la creatividad y la innovación en las áreas de electrónica, electricidad y
                    mecánica automotriz. Nuestro objetivo es que nuestros estudiantes adquieran los conocimientos y
                    habilidades necesarios para destacarse en un mundo cada vez más tecnológico y competitivo.</li>
                <li>Formación Integral: Valoramos el desarrollo personal, social y emocional de nuestros estudiantes.
                    Promovemos el respeto, la tolerancia y la responsabilidad como valores fundamentales para su
                    crecimiento como individuos y ciudadanos comprometidos con su entorno.</li>
                <li>Enfoque Práctico: Reconocemos la importancia de la aplicación práctica del conocimiento. Por ello,
                    ofrecemos un enfoque pedagógico que combina la teoría con la práctica, a través de laboratorios
                    equipados y proyectos reales sostenibles y/o ecológicos que permiten a nuestros estudiantes poner en
                    práctica lo aprendido y desarrollar habilidades técnicas y profesionales desde temprana edad.</li>
                <li>Inclusión y Diversidad: Nos comprometemos a ofrecer una educación inclusiva que responda a la
                    diversidad de necesidades, intereses y capacidades de nuestros estudiantes. Fomentamos un ambiente
                    de respeto y apoyo mutuo donde cada individuo se sienta valorado y motivado a alcanzar su máximo
                    potencial.</li>
                <li>Vinculación con la Comunidad: Reconocemos la importancia de establecer vínculos sólidos con la
                    comunidad y el sector productivo. Buscamos promover alianzas estratégicas que enriquezcan la
                    experiencia educativa de nuestros estudiantes y les brinden oportunidades de prácticas profesionales
                    y empleo futuro.</li>
            </ol>
            <p>En la Unidad Educativa Particular Benjamín Franklin, aspiramos a formar líderes y ciudadanos íntegros,
                capaces de contribuir al progreso y desarrollo de su país a través del conocimiento, la innovación y el
                compromiso social.</p>
        </div>


        <!-- Sección de Especialidades -->
        <h1 class="section-title">Especialidades</h1>
        <div class="cards">
            <div class="card">
                <img src="http://localhost/sistema_notas/imagenes/mec%c3%a1nica_automotriz.png" alt="Especialidad 1">
                <h3>Mecánica Automotriz</h3>
                <p>La especialización en Mecánica Automotriz ofrecida por la Unidad Educativa Benjamin Franklin (UEBF)
                    está diseñada para formar profesionales altamente capacitados en el diagnóstico, mantenimiento y
                    reparación de sistemas automotrices. Este programa educativo integra conocimientos teóricos con
                    prácticas intensivas, utilizando tecnología de punta y herramientas modernas para preparar a los
                    estudiantes para los desafíos del sector automotriz.</p>
            </div>
            <div class="card">
                <img src="http://localhost/sistema_notas/imagenes/electricidad.png" alt="Especialidad 2">
                <h3>Electrónica de Consumo</h3>
                <p>La especialización en Electrónica de Consumo ofrecida por la Unidad Educativa Benjamin Franklin
                    (UEBF) está diseñada para formar profesionales con habilidades y conocimientos avanzados en el
                    diseño, fabricación, reparación y mantenimiento de dispositivos electrónicos utilizados en el ámbito
                    doméstico y comercial. Este programa educativo combina una sólida formación teórica con prácticas
                    intensivas, utilizando tecnología de vanguardia y herramientas especializadas para preparar a los
                    estudiantes para una carrera exitosa en el sector de la electrónica de consumo.</p>
            </div>
            <div class="card">
                <img src="http://localhost/sistema_notas/imagenes/electr%c3%b3nica.png" alt="Especialidad 3">
                <h3>Electricidad</h3>
                <p>La especialización en Electricidad ofrecida por la Unidad Educativa Benjamin Franklin (UEBF) está
                    diseñada para formar profesionales altamente capacitados en el diseño, instalación, mantenimiento y
                    reparación de sistemas eléctricos tanto residenciales como industriales. Este programa educativo
                    combina una sólida formación teórica con prácticas intensivas, utilizando tecnología de vanguardia y
                    herramientas especializadas para preparar a los estudiantes para una carrera exitosa en el sector
                    eléctrico.</p>
            </div>
        </div>

        <!-- Línea Divisoria -->
        <div class="divider"></div>

        <h1 class="section-title">Directiva UEBF</h1>
        <section class="directiva">
            <div class="directivo">
                <img src="http://localhost/sistema_notas/imagenes/woman-7175038_640.png" alt="Foto Director">
                <p class="nombre">Dra. Clara Martínez</p>
                <p class="cargo">Directora</p>
            </div>
            <div class="directivo">
                <img src="http://localhost/sistema_notas/imagenes/business-720429_640.png" alt="Foto Subdirector">
                <p class="nombre">Prof. Juan Pérez</p>
                <p class="cargo">Subdirector(a)</p>
            </div>
            <div class="directivo">
                <img src="http://localhost/sistema_notas/imagenes/woman-7165664_640.png"
                    alt="Foto Coordinador Académico">
                <p class="nombre">Lic. Ana Ramírez</p>
                <p class="cargo">Coordinador Académico</p>
            </div>
            <div class="directivo">
                <img src="http://localhost/sistema_notas/imagenes/black-man-4699506_640.png"
                    alt="Foto Coordinador de Convivencia">
                <p class="nombre">Psic. José Gómez</p>
                <p class="cargo">Coordinador(a) de Convivencia</p>
            </div>
            <div class="directivo">
                <img src="http://localhost/sistema_notas/imagenes/sun-7350734_640.png"
                    alt="Foto Coordinador de Desarrollo Estudiantil">
                <p class="nombre">Ing. María López</p>
                <p class="cargo">Coordinador(a) de Desarrollo Estudiantil</p>
            </div>
            <div class="directivo">
                <img src="http://localhost/sistema_notas/imagenes/man-6974298_640.png" alt="Foto Secretario General">
                <p class="nombre">Lic. Roberto Sánchez</p>
                <p class="cargo">Secretario(a) General</p>
            </div>
            <div class="directivo">
                <img src="http://localhost/sistema_notas/imagenes/woman-597173_640.png"
                    alt="Foto Psicólogo/a Pedagógico/a">
                <p class="nombre">Psic. Laura Fernández</p>
                <p class="cargo">Psicólogo/a Pedagógico/a</p>
            </div>
        </section>
    </div>
    <!-- Pie de Página -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
                    Zambrano. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    </div>
    <!-- Scroll to Top Button-->
    <div class="scroll-to-top" onclick="scrollToTop()">
        <i class="fas fa-angle-up"></i>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
    $(document).ready(function() {
        console.log("Document ready!");

        // Mostrar u ocultar el botón al desplazarse
        $(window).scroll(function() {
            console.log("Window scrolled!", $(this).scrollTop());
            if ($(this).scrollTop() > 100) {
                $('.scroll-to-top').fadeIn();
            } else {
                $('.scroll-to-top').fadeOut();
            }
        });

        // Desplazamiento suave hacia arriba al hacer clic en el botón
        $('.scroll-to-top').click(function() {
            console.log("Scroll to top clicked!");
            $('html, body').animate({
                scrollTop: 0
            }, 800);
            return false;
        });
    });

    function scrollToTop() {
        $('html, body').animate({
            scrollTop: 0
        }, 800);
    }
    </script>
    <!-- Bootstrap core JavaScript-->
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <!-- Otros scripts -->
    <script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('accordionSidebar').classList.toggle('collapsed');
    });
    </script>
</body>

</html>