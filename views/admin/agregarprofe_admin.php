<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Agregar Profesor | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    
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

        <h1 class="h3 mb-4 text-gray-800">Aqui se va a ir un FORMULARIO DE ASIGACION ACADEMICA del profesor</h1>

        <div class="input-group mb-3">
            <input type="text" class="form-control" id="input-caja" placeholder="Ingrese texto"
                aria-label="Caja de texto" aria-describedby="button-generate" disabled>
            <button class="btn btn-primary" type="button" id="button-generate" onclick="generarClave()">Generar
                Clave</button>
        </div>
        <div class="container">
        <h2>Subir Archivo</h2>
        <form action="../../Crud/img.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="archivo">Selecciona un archivo:</label>
                <input type="file" class="form-control-file" id="archivo" name="archivo" onchange="mostrarInfoArchivo()">
            </div>
            <div id="info-archivo"></div>
            <button type="submit" class="btn btn-primary mt-2" name="submit">Subir Archivo</button>
        </form>
    </div>
    </div>


    </div>


    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('accordionSidebar').classList.toggle('collapsed');
        });
    </script>
    <script>
        function generarClave() {
            const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let clave = '';
            for (let i = 0; i < 12; i++) {
                const randomIndex = Math.floor(Math.random() * caracteres.length);
                clave += caracteres[randomIndex];
            }
            const inputCaja = document.getElementById('input-caja');
            inputCaja.value = clave;
            inputCaja.disabled = true;

            document.getElementById('button-generate').disabled = true;
        }
    </script>
    <script>
        function mostrarInfoArchivo() {
            const input = document.getElementById('archivo');
            const infoArchivo = document.getElementById('info-archivo');

            
            if (input.files.length > 0) {
                const archivo = input.files[0];
                const tamaño = archivo.size / 1024; 
                const tipo = archivo.type || 'Tipo desconocido';

               
                infoArchivo.innerHTML = `
                    <p><strong>Nombre:</strong> ${archivo.name}</p>
                    <p><strong>Tipo:</strong> ${tipo}</p>
                    <p><strong>Tamaño:</strong> ${tamaño.toFixed(2)} KB</p>
                `;
            } else {
        
                infoArchivo.innerHTML = '';
            }
        }
    </script>
</body>

</html>