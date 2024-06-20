<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Administrador - Estadísticas</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Estilos personalizados -->
    <style>
        .sidebar-heading .collapse-header .bx {
            color: #ff8b97; /* Color rosa claro para los iconos en los encabezados de sección */
        }

        .bg-gradient-primary {
            background-color: #a2000e; /* Color rojo oscuro para el fondo de la barra lateral */
            background-image: none; /* Asegurar que no haya imagen de fondo (gradiente) */
        }
    </style>
</head>

<body>
    <?php
    // Incluye el archivo navbar_admin.php solo una vez desde el mismo directorio
    include_once 'navbar_admin.php';
    ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Estadísticas</h1>

                </div>
                <!-- Fin del Envoltorio de la Página -->
                
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
