<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content>
    <meta name="author" content>
    <title>Administrador - Solicitudes</title>
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
        <div class="row">
            <div class="container">
                <h1 class="mt-5 text-center">Tabla
                    Solicitudes</h1>
                <div class="mb-5 mt-5">
                    <input type="text" class="form-control" id="filtroSolicitud"
                        placeholder="Filtrar Solicitudes a traves de su Cedula" onkeyup="filtrarSolicitudes()" />
                </div>

                <?php
                include '../../Crud/tabla_solicitud.php';
                ?>

            </div>

        </div>

        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>

        <!-- Salir seccion-->
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Seguro que
                            quieres salir?
                        </h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">Seleccionar "salir"
                        si quieres cerrar sesion.</div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                        <a class="btn btn-primary" href="login.html">salir</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function filtrarSolicitudes() {
                var input = document.getElementById("filtroSolicitud");
                var filter = input.value.toUpperCase();
                var table = document.getElementsByTagName("table")[0];
                var rows = table.getElementsByTagName("tr");

                for (var i = 1; i < rows.length; i++) {
                    var cells = rows[i].getElementsByTagName("td");
                    var cedulaCell = cells[1];

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
        <script>
            $(document).ready(function () {
                $('#modalActualizar').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget);
                    var cedula = button.data('cedula');
                    var telefono = button.data('telefono');
                    var nombres = button.data('nombres');
                    var apellidos = button.data('apellidos');
                    var correo = button.data('correo');
                    var rol = button.data('rol');

                    var modal = $(this);
                    modal.find('#cedula').val(cedula);
                    modal.find('#telefono').val(telefono);
                    modal.find('#nombres').val(nombres);
                    modal.find('#apellidos').val(apellidos);
                    modal.find('#correo_electronico').val(correo);
                    modal.find('#rol').val(rol);
                });
            });
        </script>
        <script>
            $(document).ready(function () {
                $('#modalActualizar').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget);
                    var id = button.data('id');
                    var cedula = button.data('cedula');
                    var telefono = button.data('telefono');
                    var nombres = button.data('nombres');
                    var apellidos = button.data('apellidos');
                    var correo = button.data('correo');
                    var rol = button.data('rol');

                    var modal = $(this);
                    modal.find('#cedula').val(cedula);
                    modal.find('#telefono').val(telefono);
                    modal.find('#nombres').val(nombres);
                    modal.find('#apellidos').val(apellidos);
                    modal.find('#correo_electronico').val(correo);
                    modal.find('#rol').val(rol);
                    modal.find('#id').val(id);
                });
            });

            function cargarDatos(id) {
                var cedula = document.getElementById('cedula');
                var telefono = document.getElementById('telefono');
                var nombres = document.getElementById('nombres');
                var apellidos = document.getElementById('apellidos');
                var correo = document.getElementById('correo_electronico');
                var rol = document.getElementById('rol');

                var form = document.getElementById('formActualizar');


                // Asignar el valor del ID al campo oculto
                form.id.value = id;

                // Asignar los demás valores a los campos del formulario
                var botonEditar = document.querySelector('button[data-id="' + id + '"]');
                cedula.value = botonEditar.getAttribute('data-cedula');
                telefono.value = botonEditar.getAttribute('data-telefono');
                nombres.value = botonEditar.getAttribute('data-nombres');
                apellidos.value = botonEditar.getAttribute('data-apellidos');
                correo.value = botonEditar.getAttribute('data-correo');
                rol.value = botonEditar.getAttribute('data-rol');
            }

            function validarTelefono(input) {
                // Obtener el valor ingresado en el campo de entrada
                var telefono = input.value;

                // Eliminar cualquier carácter que no sea un número
                telefono = telefono.replace(/\D/g, '');

                // Limitar el número de dígitos a 10
                telefono = telefono.slice(0, 10);

                // Actualizar el valor del campo de entrada con los dígitos válidos
                input.value = telefono;
            }

            function generarContrasena() {
                var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                var passwordLength = 8;
                var password = "";

                do {
                    password = "";
                    for (var i = 0; i < passwordLength; i++) {
                        var randomNumber = Math.floor(Math.random() * chars.length);
                        password += chars.substring(randomNumber, randomNumber + 1);
                    }
                } while (generatedPasswords.has(password));

                generatedPasswords.add(password);

                document.getElementById("generar_contrasena").value = password;
                document.getElementById("generarBtn").disabled = true;
            }
        </script>
        <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
        <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- Custom scripts for all pages-->
        <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
        <!-- Otros scripts -->
        <script>
            document.getElementById('sidebarToggle').addEventListener('click', function () {
                document.getElementById('accordionSidebar').classList.toggle('collapsed');
            });
        </script>
</body>

</html>