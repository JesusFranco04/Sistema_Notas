<?php
session_start();
include('../../Crud/config.php'); // Ruta al archivo de configuración de la base de datos

// Verifica si el usuario está autenticado
if (!isset($_SESSION['cedula'])) {
    // Redirige al usuario a la página de inicio de sesión si no está autenticado
    header("Location: http://localhost/sistema_notas/login.php");
    exit();
}

// Obtener la cédula del usuario desde la sesión
$cedula = $_SESSION['cedula'];

// Consulta para obtener el id_padre basado en la cédula del usuario
$sql = "SELECT id_padre FROM padre WHERE cedula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$stmt->bind_result($id_padre);
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
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f9fc; /* Gris claro para el fondo */
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .header {
        background-color: #E62433; /* Rojo para el fondo del encabezado */
        color: #ffffff; /* Blanco para el texto */
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid #003366; /* Azul marino para el borde inferior */
    }

    .header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
    }

    .container {
        flex: 1;
        width: 90%;
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background-color: #ffffff; /* Blanco para el fondo del contenedor */
        border-radius: 10px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .profile-summary {
        display: flex;
        align-items: center;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }

    .profile-summary .avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background-color: #003366; /* Azul marino para el avatar */
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-size: 60px;
        color: #ffffff; /* Blanco para el icono del avatar */
    }

    .profile-summary .info {
        flex: 1;
    }

    .profile-summary .info h1 {
        margin: 0;
        font-size: 22px;
        color: #003366; /* Azul marino para el texto del perfil */
    }

    .profile-summary .info p {
        margin: 5px 0;
        color: #555555;
        font-size: 16px;
    }

    .actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px; /* Espacio debajo de la sección de acciones */
    }

    .actions .card {
        background-color: #ffffff; /* Blanco para el fondo de las tarjetas */
        border: 2px solid #003366; /* Azul marino para el borde */
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s;
        color: #003366; /* Azul marino para el texto y los iconos */
    }

    .actions .card:hover {
        background-color: #003366; /* Azul marino al pasar el mouse */
        color: #ffffff; /* Blanco para el texto al pasar el mouse */
        transform: translateY(-5px);
    }

    .actions .card i {
        font-size: 48px;
    }

    .actions .card h2 {
        margin-top: 10px;
        font-size: 18px;
    }

    .footer {
        text-align: center;
        padding: 15px;
        background-color: #E62433; /* Rojo para el fondo del pie de página */
        color: #ffffff; /* Blanco para el texto del pie de página */
        border-top: 4px solid #003366; /* Azul marino para el borde superior del pie de página */
    }

    /* Nuevas Secciones */
    .hero {
        background-color: #003366; /* Azul marino para el fondo de la sección hero */
        color: #ffffff; /* Blanco para el texto */
        padding: 40px;
        text-align: center;
        border-radius: 8px;
        margin-bottom: 30px; /* Espacio debajo de la sección hero */
    }

    .hero h2 {
        margin: 0;
        font-size: 26px;
        font-weight: 600;
    }

    .hero p {
        font-size: 18px;
        margin-top: 10px;
    }

    .info-section {
        padding: 30px;
        background-color: #ffffff; /* Blanco para el fondo de la sección de información */
        border: 2px solid #45a049; /* Azul marino para el borde */
        border-radius: 8px;
        margin-bottom: 30px; /* Espacio debajo de la sección de información */
    }

    .info-section h2 {
        font-size: 22px;
        color: #2c692f; /* Azul marino para el texto */
        margin-bottom: 20px;
    }

    .info-section p {
        font-size: 16px;
        color: #555555;
        margin-bottom: 20px;
    }

    .info-list {
        list-style-type: disc;
        padding-left: 20px;
    }

    .info-list li {
        margin-bottom: 10px;
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

    .carousel-nav {
        position: absolute;
        top: 50%;
        width: 100%;
        display: flex;
        justify-content: space-between;
        transform: translateY(-50%);
    }

    .carousel-nav button {
        background-color: #E62433; /* Rojo para el fondo del botón */
        color: #ffffff; /* Blanco para el texto del botón */
        border: none;
        border-radius: 50%;
        padding: 10px;
        font-size: 24px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .carousel-nav button:hover {
        background-color: #d61e28; /* Rojo oscuro al pasar el mouse */
    }
    </style>
    </head>
    <body>

    <div class="header">
    <h1>Sistema de Gestión UEBF</h1>
    </div>

    <div class="container">
    <div class="profile-summary">
        <div class="avatar">
        <!-- Utiliza un icono de Boxicons como placeholder -->
        <i class='bx bx-user'></i>
        </div>
        <div class="info">
        <h1>Bienvenido(a), 
            <?php
            // Verifica si las variables de sesión están establecidas
            if (isset($_SESSION['cedula']) && isset($_SESSION['rol'])) {
                echo htmlspecialchars($_SESSION['cedula']);  // Escapa caracteres especiales para seguridad
                echo " | " . htmlspecialchars($_SESSION['rol']) . " ";
            }
            ?>
        </h1>
        </div>
    </div>

    <div class="actions">
        <div class="card btn-view-children">
            <i class='bx bx-user'></i>
            <h2>Ver Información de los Hijos</h2>
        </div>
        <div class="card btn-view-grades">
            <i class='bx bx-book'></i>
            <h2>Consultar Calificaciones</h2>
        </div>
        <div class="card btn-logout" onclick="window.location.href='http://localhost/sistema_notas/login.php'">
            <i class='bx bx-log-out'></i>
            <h2>Cerrar Sesión</h2>
        </div>
    </div>

    <!-- Nuevas Secciones -->
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
    </section>

    </div>

    <div class="footer">
        &copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener el id_padre desde la sesión de PHP
        const idPadre = "<?php echo $_SESSION['id_padre']; ?>";

        // Card para ver información de los hijos
        document.querySelector('.btn-view-children').addEventListener('click', function() {
            alert('Estamos preparando la información de sus hijos. Por favor, espere un momento mientras se carga.');

            // Redirigir a la página de información de los hijos con el id_padre en la URL
            window.location.href = 'http://localhost/sistema_notas/views/family/estudiante_fami.php?id_padre=' + idPadre;
        });
    });

        document.querySelector('.btn-view-grades').addEventListener('click', function() {
            alert('Redirigiendo a la página de selección de estudiante para consultar calificaciones.');
            // Redirige a la página de selección de estudiante
            window.location.href = 'http://localhost/sistema_notas/views/family/seleccionar_estudiante.php';
        });


        // Card para cerrar sesión
        document.querySelector('.btn-logout').addEventListener('click', function() {
        if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
            // Aquí puedes agregar lógica para cerrar sesión
            alert('Cerrando sesión...');
            // window.location.href = 'login.html'; // Redirigir a la página de login
        }
    

        // Carousel
        let currentIndex = 0;
        const images = document.querySelectorAll('.carousel-images img');
        const totalImages = images.length;

        function updateCarousel() {
        const offset = -currentIndex * 100;
        document.querySelector('.carousel-images').style.transform = `translateX(${offset}%)`;
        }

        window.nextImage = function() {
        currentIndex = (currentIndex + 1) % totalImages;
        updateCarousel();
        }

        window.prevImage = function() {
        currentIndex = (currentIndex - 1 + totalImages) % totalImages;
        updateCarousel();
        }
    });
    </script>

    </body>
    </html>
