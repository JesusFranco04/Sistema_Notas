<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Sistema de Gestión - UEBF</title>

    <!-- Custom fonts for this template-->
    <link rel="stylesheet" href="vendor/fontawesome-free/css/all.min.css" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link rel="stylesheet" href="css/sb-admin-2.min.css">
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-6 d-none d-lg-block"
                        style="background-image: url('imagenes/foto_inicio.png'); background-size: 90% auto; background-position: center; background-repeat: no-repeat;">
                    </div>
                    <div class="col-lg-6">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Enviar Solicitud</h1>
                            </div>
                            <form class="user" action="../Sistema_Notas/Crud/solicitud.php" method="POST">
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="text" class="form-control form-control-user" id="cedula"
                                            name="cedula" placeholder="Digite su Cédula" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="tel" class="form-control form-control-user" id="telefono"
                                            name="telefono" placeholder="Digite su Teléfono" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="text" class="form-control form-control-user" id="nombres"
                                            name="nombres" placeholder="Digite sus Nombres" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-control-user" id="apellidos"
                                            name="apellidos" placeholder="Digite sus Apellidos" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-user" id="correo_electronico"
                                        name="correo_electronico" placeholder="Digite su Correo Electrónico" required>
                                </div>
                                <div class="form-group">
                                    <select class="form-control" id="rol" name="rol" required>
                                        <option value="">Selecciona Rol</option>
                                        <option value="administrador" id="rol" name="rol">Administrador</option>
                                        <option value="profesor" id="rol" name="rol">Profesor</option>
                                        <option value="padre_familia" id="rol" name="rol">Padre de Familia</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Enviar Solicitud
                                </button>
                                <a href="login_family.html" class="btn btn-secondary btn-user btn-block">Cancelar</a>
                            </form>

                            <hr>
                            <div class="text-center">
                                <a class="small" href="login_family.html">Ya tienes cuenta? Inicia Sesion</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function validarFormulario() {
            var cedula = document.getElementById("cedula").value;
            var telefono = document.getElementById("telefono").value;
            var nombres = document.getElementById("nombres").value;
            var apellidos = document.getElementById("apellidos").value;
            var correo_electronico = document.getElementById("correo_electronico").value;
            var rol = document.getElementById("rol").value;

            if (cedula == "" || telefono == "" || nombres == "" || apellidos == "" || correo_electronico == "" || rol ==
                "") {
                alert("Por favor, complete todos los campos.");
                return false;
            }
            return true;
        }
    </script>

    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

</body>

</html>