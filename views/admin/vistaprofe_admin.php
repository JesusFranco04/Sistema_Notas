<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Profesores | Sistema de Gestión UEBF</title>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/css/boxicons.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f0f0;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 900px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: left;
        color: #333;
        margin-bottom: 20px;
        font-size: 28px;
    }

    hr {
        margin-top: 10px;
        margin-bottom: 20px;
        border: 0;
        border-top: 1px solid #ccc;
    }

    .filters {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .filters input[type="text"],
    .filters select {
        padding: 10px;
        margin-right: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        flex: 1;
    }

    .filters button {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 14px;
    }

    .filters button:hover {
        background-color: #0056b3;
    }

    .cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .card {
        overflow: hidden;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card .image-container {
        width: 100%;
        height: 100px;
        overflow: hidden;
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .card .image-container img {
        width: 40%;
        height: 100%;
        object-fit: cover;
        border-radius: 60%;
    }

    .card .info {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .card .info p {
        margin: 5px 0;
        font-size: 14px;
        color: #333;
    }

    .card .info p strong {
        font-weight: bold;
        color: #007bff;
    }

    .card .buttons {
        padding: 10px;
        background-color: #f5f5f5;
        border-top: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card .buttons button {
        padding: 8px 16px;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 14px;
    }

    .card .buttons button:hover {
        background-color: #c82333;
    }

    footer {
        text-align: center;
        margin-top: 20px;
        color: #666;
        font-size: 12px;
    }

    /* Estilos del modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        overflow: auto;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .modal-content h2 {
        margin-top: 0;
    }

    .modal-content p {
        margin: 10px 0;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    </style>
</head>

<body>
    <?php
    // Incluye el archivo navbar_admin.php solo una vez desde el mismo directorio
    include_once 'navbar_admin.php';
    ?>

    <div class="container">
        <h1>Visualización de Profesores</h1>
        <hr>
        <!-- Filtros para búsqueda rápida -->
        <div class="filters">
            <input type="text" placeholder="Buscar por nombre...">
            <select id="filtroJornada">
                <option value="">Filtrar por jornada</option>
                <option value="matutina">Matutina</option>
                <option value="vespertina">Vespertina</option>
            </select>
            <select id="filtroMateria">
                <option value="">Filtrar por materia</option>
                <option value="matematicas">Matemáticas</option>
                <option value="fisica">Física</option>
                <option value="historia">Historia</option>
                <option value="quimica">Química</option>
                <option value="literatura">Literatura</option>
                <option value="educacion_fisica">Educación Física</option>
            </select>
            <select id="filtroSubnivel">
                <option value="">Filtrar por subnivel</option>
                <option value="EBG">EBG</option>
                <option value="BTI">BTI</option>
            </select>
            <button onclick="filterProfessors()">Buscar</button>
        </div>
        <!-- Tarjetas de profesores -->
        <div class="cards-container">
            <!-- Tarjeta 1 -->
            <div class="card">
                <div class="image-container">
                    <img src="http://localhost/sistema_notas/imagenes/path_to_profile_images1.jpg" alt="Juan Pérez">
                </div>
                <div class="info">
                    <div>
                        <p><strong>Nombre:</strong> Juan Pérez</p>
                        <p><strong>Materia:</strong> Matemáticas</p>
                        <p><strong>Cursos:</strong> Noveno A, Décimo B</p>
                    </div>
                    <div>
                        <p><strong>Tutorías:</strong> Grupo 1</p>
                        <p><strong>Jornada:</strong> Matutina</p>
                        <p><strong>Cédula:</strong> 1234567890</p>
                    </div>
                </div>
                <div class="buttons">
                    <button onclick="openModal(1)">Ver más detalle</button>
                    <button>Eliminar</button>
                </div>
            </div>
            <!-- Tarjeta 2 -->
            <div class="card">
                <div class="image-container">
                    <img src="http://localhost/sistema_notas/imagenes/path_to_profile_images2.jpg"
                        alt="María Rodríguez">
                </div>
                <div class="info">
                    <div>
                        <p><strong>Nombre:</strong> María Rodríguez</p>
                        <p><strong>Materia:</strong> Física</p>
                        <p><strong>Cursos:</strong> Octavo C, Décimo A</p>
                    </div>
                    <div>
                        <p><strong>Tutorías:</strong> Grupo 2</p>
                        <p><strong>Jornada:</strong> Vespertina</p>
                        <p><strong>Cédula:</strong> 2345678901</p>
                    </div>
                </div>
                <div class="buttons">
                    <button onclick="openModal(2)">Ver más detalle</button>
                    <button>Eliminar</button>
                </div>
            </div>
            <!-- Tarjeta 3 -->
            <div class="card">
                <div class="image-container">
                    <img src="http://localhost/sistema_notas/imagenes/path_to_profile_images3.jpg" alt="Pedro Gómez">
                </div>
                <div class="info">
                    <div>
                        <p><strong>Nombre:</strong> Pedro Gómez</p>
                        <p><strong>Materia:</strong> Historia</p>
                        <p><strong>Cursos:</strong> Séptimo B, Noveno C</p>
                    </div>
                    <div>
                        <p><strong>Tutorías:</strong> Grupo 3</p>
                        <p><strong>Jornada:</strong> Matutina</p>
                        <p><strong>Cédula:</strong> 3456789012</p>
                    </div>
                </div>
                <div class="buttons">
                    <button onclick="openModal(3)">Ver más detalle</button>
                    <button>Eliminar</button>
                </div>
            </div>
            <!-- Tarjeta 4 -->
            <div class="card">
                <div class="image-container">
                    <img src="http://localhost/sistema_notas/imagenes/path_to_profile_images4.jpg" alt="Ana López">
                </div>
                <div class="info">
                    <div>
                        <p><strong>Nombre:</strong> Ana López</p>
                        <p><strong>Materia:</strong> Química</p>
                        <p><strong>Cursos:</strong> Octavo B, Décimo C</p>
                    </div>
                    <div>
                        <p><strong>Tutorías:</strong> Grupo 4</p>
                        <p><strong>Jornada:</strong> Vespertina</p>
                        <p><strong>Cédula:</strong> 4567890123</p>
                    </div>
                </div>
                <div class="buttons">
                    <button onclick="openModal(4)">Ver más detalle</button>
                    <button>Eliminar</button>
                </div>
            </div>
            <!-- Tarjeta 5 -->
            <div class="card">
                <div class="image-container">
                    <img src="http://localhost/sistema_notas/imagenes/path_to_profile_images5.jpg"
                        alt="Carlos Martínez">
                </div>
                <div class="info">
                    <div>
                        <p><strong>Nombre:</strong> Carlos Martínez</p>
                        <p><strong>Materia:</strong> Literatura</p>
                        <p><strong>Cursos:</strong> Séptimo A, Noveno B</p>
                    </div>
                    <div>
                        <p><strong>Tutorías:</strong> Grupo 5</p>
                        <p><strong>Jornada:</strong> Matutina</p>
                        <p><strong>Cédula:</strong> 5678901234</p>
                    </div>
                </div>
                <div class="buttons">
                    <button onclick="openModal(5)">Ver más detalle</button>
                    <button>Eliminar</button>
                </div>
            </div>
            <!-- Tarjeta 6 -->
            <div class="card">
                <div class="image-container">
                    <img src="http://localhost/sistema_notas/imagenes/path_to_profile_images6.jpg" alt="Laura Sánchez">
                </div>
                <div class="info">
                    <div>
                        <p><strong>Nombre:</strong> Laura Sánchez</p>
                        <p><strong>Materia:</strong> Educación Física</p>
                        <p><strong>Cursos:</strong> Octavo A, Décimo D</p>
                    </div>
                    <div>
                        <p><strong>Tutorías:</strong> Grupo 6</p>
                        <p><strong>Jornada:</strong> Vespertina</p>
                        <p><strong>Cédula:</strong> 6789012345</p>
                    </div>
                </div>
                <div class="buttons">
                    <button onclick="openModal(6)">Ver más detalle</button>
                    <button>Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar detalles de profesor -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Detalles del Profesor</h2>
            <div id="modal-body">
                <!-- Contenido del modal se insertará dinámicamente -->
            </div>
        </div>
    </div>
    <footer>
        &copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
        los derechos reservados.
    </footer>
    <script>
    // Obtener el modal y el span para cerrarlo
    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];

    // Cuando el usuario haga clic en el span (x), cerrar el modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Cuando el usuario haga clic fuera del modal, cerrarlo también
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Función para abrir el modal y mostrar detalles del profesor
    function openModal(id) {
        // Aquí se debería hacer una llamada AJAX para obtener los datos del profesor por su ID
        // Por ahora, mostraremos datos de ejemplo
        var modalBody = document.getElementById("modal-body");
        modalBody.innerHTML = `
                <p><strong>ID:</strong> ${id}</p>
                <p><strong>Nombres:</strong> Juan</p>
                <p><strong>Apellidos:</strong> Pérez</p>
                <p><strong>Cédula:</strong> 1234567890</p>
                <p><strong>Teléfono:</strong> 0987654321</p>
                <p><strong>Correo Electrónico:</strong> juan.perez@example.com</p>
                <p><strong>Dirección:</strong> Av. Principal #123</p>
                <p><strong>Fecha de Nacimiento:</strong> 01-01-1990</p>
                <p><strong>Género:</strong> Masculino</p>
                <p><strong>Discapacidad:</strong> No</p>
                <p><strong>Rol:</strong> Profesor</p>
                <p><strong>Fecha de Creación:</strong> 2024-06-30</p>
            `;
        // Mostrar el modal
        modal.style.display = "block";
    }

    // Función para filtrar profesores (solo para demostración, implementar lógica real con AJAX)
    function filterProfessors() {
        alert("Filtrando profesores...");
        // Aquí se implementaría la lógica de filtrado real con AJAX y actualización de las tarjetas
    }
    </script>

</body>

</html>