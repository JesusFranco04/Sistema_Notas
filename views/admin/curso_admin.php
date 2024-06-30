<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos | Sistema De Gestión UEBF</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
        integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
    .table-container {
        max-height: 400px;
        overflow-x: auto;
        overflow-y: auto;
    }

    .filters-add-button {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .filters-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filters-form label,
    .filters-form select,
    .filters-form button {
        font-size: 0.875rem;
        /* Tamaño de letra más pequeño */
    }

    .actions a {
        display: block;
        margin-bottom: 10px;
        /* Espacio entre botones */
    }

    .table thead th {
        color: white;
        background-color: #dc3545;
        /* Color rojo */
        text-align: center;
        /* Centrar texto */
    }

    .table tbody td {
        text-align: center;
        /* Centrar texto en las celdas del cuerpo */
    }

    .copyright-container {
        background-color: #f8f9fa;
        /* Color de fondo */
        padding: 10px;
        /* Espacio interno */
        border-top: 1px solid #ccc;
        /* Borde superior */
    }
    </style>
</head>

<body>
    <?php
    // Incluye el archivo navbar_admin.php solo una vez desde el mismo directorio
    include_once 'navbar_admin.php';
    ?>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        Cursos Registrados
                    </div>
                    <div class="card-body">
                        <!-- Filtros de Búsqueda y Botón de Añadir -->
                        <div class="filters-add-button">
                            <form class="filters-form">
                                <label class="my-1 mr-2" for="ordenarPor">Ordenar por:</label>
                                <select class="custom-select my-1 mr-sm-2" id="ordenarPor">
                                    <option selected>Seleccionar...</option>
                                    <option value="recientes">Más Recientes</option>
                                    <option value="antiguos">Más Antiguos</option>
                                    <option value="nombreAZ">Nombre A-Z</option>
                                    <!-- Agregar más opciones según necesidad -->
                                </select>

                                <label class="my-1 mr-2" for="filtrarPorEstado">Filtrar por Estado:</label>
                                <select class="custom-select my-1 mr-sm-2" id="filtrarPorEstado">
                                    <option selected>Seleccionar...</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>

                                <button type="submit" class="btn btn-primary my-1 mr-2">Buscar</button>
                            </form>
                            <a href="http://localhost/sistema_notas/views/admin/form_curso_admin.php"
                                class="btn btn-danger">
                                <i class="bx bx-plus"></i> Agregar más cursos
                            </a>
                        </div>

                        <!-- Tabla de Cursos con Scrollbars -->
                        <div class="table-container">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre del Curso</th>
                                        <th>Nivel</th>
                                        <th>Especialidad</th>
                                        <th>Paralelo</th>
                                        <th>Estado</th>
                                        <th>Usuario que ingresó</th>
                                        <th>Fecha de ingreso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Ejemplo de datos de cursos desde la base de datos -->
                                    <tr>
                                        <td>1</td>
                                        <td>Matemáticas</td>
                                        <td>Noveno</td>
                                        <td>Mecánica Automotriz</td>
                                        <td>A</td>
                                        <td>Activo</td>
                                        <td>Admin123</td>
                                        <td>2024-06-30</td>
                                        <td class="actions">
                                            <a href="#" class="btn btn-sm btn-primary">
                                                <i class='bx bxs-edit'></i> Editar
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger">
                                                <i class='bx bxs-trash'></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Historia</td>
                                        <td>Octavo</td>
                                        <td>Humanidades</td>
                                        <td>B</td>
                                        <td>Inactivo</td>
                                        <td>Admin456</td>
                                        <td>2024-06-29</td>
                                        <td class="actions">
                                            <a href="#" class="btn btn-sm btn-primary">
                                                <i class='bx bxs-edit'></i> Editar
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger">
                                                <i class='bx bxs-trash'></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                    <!-- Puedes repetir este patrón para cada curso desde la base de datos -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de Página -->
    <footer class="text-center mt-4">
        <div class="copyright-container">
            <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano.
                Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"
        integrity="sha384-Ls6XIiqFwV6mbdJi4RtcH6I9zNq0J3T9UJ0V6J+J0AqlAdgrvVv6WqZ6G7feAkb" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
        integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8sh+EmmAdHx56E/WFbLEs5EJEC6KpLcIEGcl5B" crossorigin="anonymous">
    </script>
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <!-- Otros scripts -->
    <script>
    document.getElementById('sidebarToggle').click();
    </script>
</body>

</html>