<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Descripción de tu sitio">
    <meta name="author" content="Tu Nombre">
    <title>SISTEMA DE GESTIÓN UEBF | PROFESOR</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
    /* Estilos generales */
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f9fa;
        color: #333;
    }

    /* Estilos para el encabezado */
    header {
        background-color: #8b0000;
        color: #fff;
        padding: 20px;
        text-align: center;
    }

    header h1 {
        margin: 0;
        font-size: 32px;
    }

    /* Estilos para la barra de navegación */
    nav {
        background-color: #a2000e;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
    }

    .menu {
        list-style-type: none;
        margin: 0;
        padding: 0;
        display: flex;
    }

    .menu li {
        position: relative;
    }

    .menu li a {
        display: block;
        color: #fff;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
    }

    .menu li a:hover {
        background-color: #ff6347;
    }

    /* Dropdown */
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #ff6347;
        min-width: 160px;
        z-index: 1;
        left: 0;
        top: 100%;
    }

    .dropdown-content li {
        display: block;
    }

    .dropdown-content li a {
        color: #fff;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        text-align: left;
    }

    .dropdown-content li a:hover {
        background-color: #ff4500;
    }

    /* Mostrar subsecciones al pasar el ratón */
    .dropdown:hover .dropdown-content {
        display: block;
    }

    /* Botón de cerrar sesión */
    .session-info {
        display: flex;
        align-items: center;
        color: #fff;
    }

    .session-info img {
        border-radius: 50%;
        margin-right: 10px;
        width: 32px;
        height: 32px;
    }

    .session-info span {
        font-size: 14px;
        color: #e5e5e5;
        margin-right: 20px;
    }

    .session-info button {
        background-color: #ff6347;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 50px;
        cursor: pointer;
    }

    .session-info button:hover {
        background-color: #ff4500;
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

    /* Estilos para la historia del colegio */
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

    /* Estilos para las pestañas */
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

    /* Estilos para las tarjetas de especialidades */
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

    /* Estilos para la sección de la directiva */
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

    /* Estilos para el pie de página */
    footer {
        background-color: #8b0000;
        color: #fff;
        text-align: center;
        padding: 20px 0;
        position: relative;
        bottom: 0;
        width: 100%;
    }

    footer p {
        margin: 0;
    }

    /* Media queries */
    @media (max-width: 768px) {
        .directiva {
            flex-direction: column;
            align-items: center;
        }

        .directivo {
            margin-bottom: 20px;
            flex: 1 0 80%;
        }
    }
    </style>
</head>

<body>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sistema de Gestión UEBF</title>
        <link rel="stylesheet" href="styles.css">
        <style>
        /* Estilos adicionales */
        .history-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 40px;
        }

        .history-content {
            flex: 1 1 60%;
            margin-right: 20px;
        }

        .history-image {
            flex: 1 1 40%;
        }

        .history-image img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        </style>
    </head>

    <body>
        <!-- Encabezado -->
        <header>
            <h1>SISTEMA DE GESTIÓN UEBF</h1>
            <p>Bienvenido(a) Profesor(a) {{Nombre del Profesor}}</p>
        </header>

        <!-- Barra de Navegación -->
        <nav>
            <!-- Barra de navegación -->
            <ul class="menu">
                <!-- Lista de elementos del menú -->
                <li><a href="http://localhost/sistema_notas/views/profe/index_profe.php">Inicio</a></li>
                <!-- Enlace a la página de inicio -->
                <li><a href="http://localhost/sistema_notas/views/profe/registronota_profe.php">Registro de
                        Calificaciones</a></li>
                <!-- Enlace a la página de registro de calificaciones -->
                <li class="dropdown">
                    <a href="#">Consulta de Calificaciones</a>
                    <!-- Enlace que despliega un menú de consulta de calificaciones -->
                    <ul class="dropdown-content">
                        <!-- Lista desplegable de consulta -->
                        <li><a href="http://localhost/sistema_notas/views/profe/notasclase_profe.php">Consulta por
                                Clase</a></li>
                        <!-- Enlace a consulta por clase -->
                        <li><a href="http://localhost/sistema_notas/views/profe/notasestudiante_profe.php">Consulta por
                                Estudiante</a></li>
                        <!-- Enlace a consulta por estudiante -->
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">Reportes</a>
                    <!-- Enlace que despliega un menú de reportes -->
                    <ul class="dropdown-content">
                        <!-- Lista desplegable de reportes -->
                        <li><a href="http://localhost/sistema_notas/views/profe/reporteclase_profe.php">Reporte por
                                Clase</a></li>
                        <!-- Enlace a reporte por clase -->
                        <li><a href="http://localhost/sistema_notas/views/profe/reporteestudiante_profe.php">Reporte por
                                Estudiante</a></li>
                        <!-- Enlace a reporte por estudiante -->
                    </ul>
                </li>
            </ul>
        <div class="session-info">
            <img src="http://localhost/sistema_notas/imagenes/media/{{$_SESSION['archivo']}}" alt="Imagen de perfil">
            <span>{{Nombre del Profesor}}</span>
            <button onclick="window.location.href='http://localhost/sistema_notas/login.php">Cerrar Sesión</button>
        </div>
        </nav>

        <!-- Banner con imágenes -->
        <div class="banner">
        <div class="banner-images">
            <img class="active" src="http://localhost/sistema_notas/imagenes/banners1.png" alt="Imagen 1">
            <img src="http://localhost/sistema_notas/imagenes/banners2.png" alt="Imagen 2">
            <img src="http://localhost/sistema_notas/imagenes/banners3.png" alt="Imagen 3">
            <div class="banner-text">SISTEMA DE GESTIÓN - UEBF</div>
        </div>
        </div>

        <!-- Sección de Historia del Colegio -->
        <div class="history-section">
            <div class="history-content">
                <h2>Historia del Colegio</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vitae velit in ex ultricies condimentum.
                    Morbi vitae enim vitae velit blandit fermentum. Nullam ut condimentum leo. Duis bibendum nunc a
                    semper elementum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac
                    turpis egestas. Mauris pretium justo at nulla suscipit, sed congue dolor ultricies. Quisque luctus,
                    velit et pellentesque fermentum, eros felis lobortis odio, id lacinia lacus lectus sed ex. Aenean et
                    commodo nulla. Fusce ultricies efficitur risus sit amet ultricies. Sed ultrices tortor sed nulla
                    lobortis, vel sodales lacus feugiat.</p>
                <p>Nulla et ultricies risus, sit amet efficitur libero. Etiam a magna fermentum, blandit felis a,
                    fermentum ligula. Donec at egestas lectus, vel volutpat magna. Vestibulum eget justo vulputate,
                    congue elit ut, fermentum tellus. Proin nec libero quam. Morbi porttitor in neque nec pharetra.
                    Phasellus ut pharetra felis. Suspendisse feugiat neque ipsum, id convallis justo bibendum a.</p>
            </div>
            <div class="history-image">
                <img src="http://localhost/sistema_notas/imagenes/Trabalhe-Conosco-S%c3%adrio-Liban%c3%aas-2b.png"
                    alt="Imagen del Colegio">
            </div>
        </div>

        <!-- Sección de Visión y Misión -->
        <div class="tabs">
            <button class="tab-button" onclick="openTab(event, 'vision')">Visión</button>
            <button class="tab-button" onclick="openTab(event, 'mision')">Misión</button>
        </div>

        <div id="vision" class="tab-content">
            <h2>Visión</h2>
            <p>Ser reconocidos en el ámbito educativo nacional e internacional por la formación integral y de calidad de
                nuestros estudiantes, basada en valores éticos, morales y científicos.</p>
        </div>

        <div id="mision" class="tab-content">
            <h2>Misión</h2>
            <p>Formar y educar a nuestros estudiantes en un ambiente de excelencia académica, promoviendo la
                investigación, la innovación y el desarrollo humano sostenible, para que sean líderes competentes y
                comprometidos con el progreso de la sociedad.</p>
        </div>

        <!-- Sección de Especializaciones -->
        <div class="cards">
            <div class="card">
                <img src="http://localhost/sistema_notas/imagenes/media/electronica.jpg"
                    alt="Especialización en Electrónica de Consumo">
                <h3>Especialización en Electrónica de Consumo</h3>
                <p>Descripción breve de la especialización en Electrónica de Consumo.</p>
                <a href="#" class="btn">Ver Más</a>
            </div>
            <div class="card">
                <img src="http://localhost/sistema_notas/imagenes/media/electricidad.jpg"
                    alt="Especialización en Electricidad">
                <h3>Especialización en Electricidad</h3>
                <p>Descripción breve de la especialización en Electricidad.</p>
                <a href="#" class="btn">Ver Más</a>
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

        <!-- Pie de página -->
        <footer>
            <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
                Todos los derechos reservados.</p>
        </footer>

        <!-- Script para tabs -->
        <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab-button");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
        </script>
    </body>

    </html>