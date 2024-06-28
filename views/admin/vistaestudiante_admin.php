<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Estudiantes | Sistema de Gestión UEBF</title>
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
    .sidebar-heading .collapse-header .bx {
        color: #ff8b97;
        /* Color rosa claro para los iconos en los encabezados de sección */
    }

    .bg-gradient-primary {
        background-color: #a2000e;
        /* Color rojo oscuro para el fondo de la barra lateral */
        background-image: none;
        /* Asegurar que no haya imagen de fondo (gradiente) */
    }
    </style>
</head>

<body>
    <?php
    // Incluir el archivo de conexión y verificar la conexión
    include '../../Crud/config.php';

    $sql = "SELECT * FROM estudiantes";
    $resultado = $conn->query($sql);

    if (!$resultado) {
        die("Error en la consulta: " . $conn->error);
    }
    ?>

    <?php include_once 'navbar_admin.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="container">
                <h1 class="mt-1 text-center">Tabla de Estudiantes</h1>
                <div class="mb-5 mt-5">
                    <input type="text" class="form-control" id="filtroSolicitud"
                        placeholder="Filtrar por Cédula del Estudiantes" onkeyup="filtrarSolicitudes()">
                </div>
                <div class="mb-4 mt-3">
                    <a href="../../Crud/estudiantes/agregar_estudiantes.php" class="btn btn-primary">Agregar
                        Profesor</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <!-- tener que estar igual que la base de datos -->
                                <th>ID</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Cédula</th>
                                <th>Teléfono</th>
                                <th>Correo Electrónico</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                            <tr>
                                <!-- son las colupnas que saldran en la tabla-->
                                <td><?php echo $fila['id']; ?></td>
                                <td><?php echo $fila['nombres']; ?></td>
                                <td><?php echo $fila['apellidos']; ?></td>
                                <td><?php echo $fila['cedula']; ?></td>
                                <td><?php echo $fila['telefono']; ?></td>
                                <td><?php echo $fila['correo_electronico']; ?></td>
                                <td><?php echo $fila['date_creation']; ?></td>
                                <td>
                                    <a href="../../Crud/estudiantes/editar_estudiantes.php?id=<?php echo $fila['id']; ?>"
                                        class="btn btn-sm btn-primary">Editar</a>
                                    <a href="../../Crud/estudiantes/eliminar_estudiantes.php? id=<?php echo $fila['id']; ?>"
                                        class="btn btn-sm btn-danger">Eliminar</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
    </div>
    <!-- Scroll to Top Button-->
    <div class="scroll-to-top" onclick="scrollToTop()">
        <i class="fas fa-angle-up"></i>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
    <!-- Scripts adicionales aquí 
    -->
    <script>
    function filtrarSolicitudes() {
        var input = document.getElementById("filtroSolicitud");
        var filter = input.value.toUpperCase();
        var table = document.getElementsByTagName("table")[0];
        var rows = table.getElementsByTagName("tr");

        for (var i = 1; i < rows.length; i++) {
            var cells = rows[i].getElementsByTagName("td");
            var cedulaCell = cells[3]; // Cambiado a la columna de Cédula (index 3)
            if (cedulaCell) {
                var value = cedulaCell.textContent || cedulaCell.innerText;
                if (value.toUpperCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    }
    </script>

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
</body>

</html>