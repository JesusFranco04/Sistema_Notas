<?php 

    session_start();
    if(!isset($_SESSION['usuario'])){
        echo '
        <script>alert("Por favor Identificate");
        window.location = "../templates/login_admin.php";
        </script>;
        ';
      session_destroy();
      die();
      
    }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <link
      rel="stylesheet"
      href="../css/Bootstrap/bootstrap.css"
    />
    <link
      rel="stylesheet"
      href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
    />
    <title>ADMINISTRADOR</title>
    <link rel="stylesheet" href="../css/index.css" />
  </head>
  <style>
    body {
      align-items: center;
      margin-top: 100px;
      justify-content: center;
      background: url(../imagenes/admin.jpg) no-repeat center center fixed;
      background-size: cover;
    }

  </style>
  <body>
  <nav class="navbar navbar-dark bg-primary fixed-top">
      <div class="container-fluid">
        <a class="navbar-brand">ADMINISTRADOR <i class='bx bx-user'></i></a>
        <button
          class="navbar-toggler bg-dark"
          type="button"
          data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasDarkNavbar"
          aria-controls="offcanvasDarkNavbar"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div
          class="offcanvas offcanvas-end text-bg-dark bg-primary"
          tabindex="-1"
          id="offcanvasDarkNavbar"
          aria-labelledby="offcanvasDarkNavbarLabel"
        >
          <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel"
              >Tablas de datos y registros</h5
            >
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="offcanvas"
              aria-label="Close"
            ></button>
          </div>
          <div class="offcanvas-body">
            <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
              <li class="nav-item">
                <a
                  class="nav-link text-white"
                  href="../templates/gestion_clientes.php"
                  ><i class='bx bx-user'></i> Gestion_Clientes</a
                >
              </li>
              <li class="nav-item">
                <a
                  class="nav-link text-white"
                  href="../templates/gestion_usuario.php"
                  ><i class='bx bx-user'></i> Gestion_Usuarios</a
                >
              </li>
              <li class="nav-item">
                <a
                  class="nav-link text-white"
                  href="../templates/gestion_contacto.php"
                  ><i class='bx bx-phone'></i> Gestion_Contacto</a
                >
              </li>
              <li class="nav-item">
                <a
                  class="nav-link text-white"
                  href="../templates/regis_admin.php"
                  ><i class='bx bx-user'></i> Registro de Administradores</a
                >
              </li>
            </ul>
          </div>
          <div class="offcanvas-footer">
            <form class="d-flex" role="search" action="../BD/cerraradmin.php">
              <button class="btn btn-success w-100" type="submit"
                ><i class='bx bx-exit'></i> Salir</button
              >
            </form>
          </div>
        </div>
      </div>
    </nav>

    <div class="container">
      <h1 class="mt-5 text-center">Tabla de Registro Clientes</h1>
      <div class="mb-5 mt-5">
        <input
          type="text"
          class="form-control"
          id="filtroCliente"
          placeholder="Filtrar Cliente"
          onkeyup="filtrarClientes()"
        />
      </div>

      <?php
      require_once '../BD/regis_cliente.php';
      ?>
    </div>

    <!-- Modal Actualizar -->
    <div
      class="modal fade"
      id="modalActualizar"
      tabindex="-1"
      aria-labelledby="modalActualizarLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content bg-success">
          <div class="modal-header">
                <h5 class="modal-title" id="modalActualizarLabel">
                  Actualizar Cliente
                </h5>
                <button
                  type="button"
                  class="close"
                  data-dismiss="modal"
                  aria-label="Close"
                >
                  <span aria-hidden="true">&times;</span>
                </button>
          </div>


  <!-- aqui se crea el formulario -->

          <form action="../BD/actualizar.php" method="post" id="formActualizar">
            <div class="modal-body bg-success text-center">
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group text-white text-center">
                    <label for="nombre">Nombres:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre" required>
                  </div>
                  <div class="form-group text-white text-center">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" oninput="validarTelefono(this)" placeholder="Ingrese su teléfono" required>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group text-white text-center">
                    <label for="apellido">Apellidos:</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Ingrese los apellidos" required>
                  </div>
                  <div class="form-group text-white text-center">
                    <label for="operadora">Operadora:</label>
                    <select class="form-control" id="operadora" name="operadora">
                      <option value="Claro">Claro</option>
                      <option value="Movistar">Movistar</option>
                      <option value="Tuenti">Tuenti</option>
                      <option value="Cnt">Cnt</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group text-white text-center">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Ingrese su correo" required>
                  </div>
                  <div class="form-group text-white text-center">
                    <label for="direccion">Dirección:</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Ingrese su dirección" required>
                  </div>
                </div>
              </div>
            </div>
            <input type="hidden" id="id" name="id" value="">

            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>    
          </form> 
      </div>
    </div>

    <!-- aqui termina el formulario -->

    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>

      function filtrarClientes() {
        var input = document.getElementById("filtroCliente");
        var filter = input.value.toUpperCase();
        var table = document.getElementsByTagName("table")[0];
        var rows = table.getElementsByTagName("tr");

        for (var i = 1; i < rows.length; i++) {
          var cells = rows[i].getElementsByTagName("td");
          var match = false;

          for (var j = 0; j < cells.length; j++) {
            var cell = cells[j];
            if (cell) {
              var value = cell.textContent || cell.innerText;
              if (value.toUpperCase().indexOf(filter) > -1) {
                match = true;
                break;
              }
            }
          }

          if (match) {
            rows[i].style.display = "";
          } else {
            rows[i].style.display = "none";
          }
        }
      }
    </script>


 <!-- aqui se funcionabilidad el formulario -->
  <script>
    $(document).ready(function() {
        $('#modalActualizar').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id_cliente = button.data('id_cliente');
            var nombre = button.data('nombre');
            var telefono = button.data('telefono');
            var apellido = button.data('apellido');
            var operadoraIndex = button.data('operadora-index');
            var email = button.data('email');
            var direccion = button.data('direccion');

            var modal = $(this);
            modal.find('#nombre').val(nombre);
            modal.find('#telefono').val(telefono);
            modal.find('#apellido').val(apellido);
            modal.find('#operadora').prop('selectedIndex', operadoraIndex);
            modal.find('#email').val(email);
            modal.find('#direccion').val(direccion);
        });
    });
</script>
<script>
function cargarDatos(id) {
  var nombre = document.getElementById('nombre');
  var telefono = document.getElementById('telefono');
  var apellido = document.getElementById('apellido');
  var operadora = document.getElementById('operadora');
  var email = document.getElementById('email');
  var direccion = document.getElementById('direccion');
  var form = document.getElementById('formActualizar');

  // Asignar el valor del ID al campo oculto
  form.id.value = id;

  // Asignar los demás valores a los campos del formulario
  var botonEditar = document.querySelector('button[data-id_cliente="' + id + '"]');
  nombre.value = botonEditar.getAttribute('data-nombre');
  telefono.value = botonEditar.getAttribute('data-telefono');
  apellido.value = botonEditar.getAttribute('data-apellido');
  operadora.selectedIndex = botonEditar.getAttribute('data-operadora-index');
  email.value = botonEditar.getAttribute('data-email');
  direccion.value = botonEditar.getAttribute('data-direccion');
}

  </script>
<!-- hasta aqui  -->

<script>
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
</script>
  </body>
</html>
