<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Estadísticas | Sistema de Gestión UEBF</title>
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
    // Incluye el archivo navbar_admin.php solo una vez desde el mismo directorio
    include_once 'navbar_admin.php';
    ?>
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Estadistica</h1>
        </div>
        <div class="row">
            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total(Profesores)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    require '../../Crud/config.php';
                                    $connection = mysqli_connect("localhost","root","12345","Sistema_Gestion");
                                    $query="SELECT id FROM profesores ORDER BY id";
                                    $query_run = mysqli_query($connection, $query);
                                    $row = mysqli_num_rows($query_run);

                                    echo '<h1> '.$row.'</h>';

                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total(Estudiantes)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    require '../../Crud/config.php';
                                    $connection = mysqli_connect("localhost","root","12345","Sistema_Gestion");
                                    $query="SELECT id FROM estudiantes ORDER BY id";
                                    $query_run = mysqli_query($connection, $query);
                                    $row = mysqli_num_rows($query_run);

                                    echo '<h1> '.$row.'</h>';

                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total(Usuarios)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    require '../../Crud/config.php';
                                    $connection = mysqli_connect("localhost","root","12345","Sistema_Gestion");
                                    $query="SELECT id FROM usuarios ORDER BY id";
                                    $query_run = mysqli_query($connection, $query);
                                    $row = mysqli_num_rows($query_run);

                                    echo '<h1> '.$row.'</h>';

                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
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