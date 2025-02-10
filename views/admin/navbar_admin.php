<div id="wrapper" class="d-flex">

    <!-- Barra Lateral -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <!-- Barra Lateral - Marca -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center"
            href="http://localhost/sistema_notas/views/admin/index_admin.php" title="Ir al inicio">
            <div class="sidebar-brand-icon rotate-n-25">
                <i class='bx bx-book-reader' style="font-size: 36px;" aria-hidden="true"></i>
            </div>
            <div class="sidebar-brand-text mx-3" style="font-size: 14px;">SISTEMA DE GESTIÓN<span
                    class="acronym">-UEBF</span></div>
        </a>
        <!-- Divisor -->
        <hr class="sidebar-divider my-0">
        <!-- Elemento de Navegación - Panel de Control -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOne"
                aria-expanded="false" aria-controls="collapseOne" title="Accede al Panel de Control">
                <i class='bx bxs-home' style="font-size: 14px;" aria-hidden="true"></i>
                <span class="nav-text">Inicio</span>
            </a>
            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <div class="submenu-section">
                        <h6 class="collapse-header">Panel de Control</h6>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/index_admin.php">
                            <i class='bx bx-file' aria-hidden="true"></i> Página de Inicio
                        </a>
                    </div>
                    <div class="submenu-section">
                        <h6 class="collapse-header">Análisis Numérico</h6>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/estadistica_admin.php">
                            <i class='bx bx-stats' aria-hidden="true"></i> Estadísticas
                        </a>
                    </div>
                </div>
            </div>
        </li>
        <!-- Divisor -->
        <hr class="sidebar-divider">
        <!-- Encabezado - Usuarios -->
        <div class="sidebar-heading">
            Usuarios
        </div>
        <!-- Elemento de Navegación - Gestión de Usuarios -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                aria-expanded="false" aria-controls="collapseTwo" title="Gestión de Usuarios">
                <i class='bx bxs-user' style="font-size: 14px;" aria-hidden="true"></i>
                <span class="nav-text">Gestión de Usuarios</span>
            </a>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <!-- Sección de Usuarios Registrados -->
                    <div class="submenu-section">
                        <h6 class="collapse-header">Usuarios Registrados</h6>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/usuario.php">
                            <i class='bx bx-user' style="font-size: 18px; margin-right: 10px;" aria-hidden="true"></i>
                            Usuarios
                        </a>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/administradores.php">
                            <i class='bx bx-user' style="font-size: 18px; margin-right: 10px;" aria-hidden="true"></i>
                            Administradores
                        </a>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/profesores.php">
                            <i class='bx bx-user' style="font-size: 18px; margin-right: 10px;"></i>
                            Profesores
                        </a>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/estudiantes.php">
                            <i class='bx bx-user-pin' style="font-size: 18px; margin-right: 10px;"></i>
                            Estudiantes
                        </a>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/padres.php">
                            <i class='bx bx-user-check' style="font-size: 18px; margin-right: 10px;"></i>
                            Representantes
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/Crud/admin/padre_x_estudiante/padre_estudiante.php">
                            <i class='bx bx-user-check' style="font-size: 18px; margin-right: 10px;"></i>
                            Asignación de Padre
                        </a>
                    </div>
                </div>
            </div>
        </li>
        <!-- Divisor -->
        <hr class="sidebar-divider">
        <!-- Encabezado - Académico -->
        <div class="sidebar-heading">
            Académico
        </div>
        <!-- Elemento de Navegación - Gestión Académica -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThree"
                aria-expanded="true" aria-controls="collapseThree">
                <i class='bx bxs-book' style="font-size: 14px;"></i>
                <span class="nav-text">Gestión Educativa</span>
            </a>
            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <!-- Subsección de Calificaciones -->
                    <div class="submenu-section">
                        <h6 class="collapse-header">Coordinación Académica</h6>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/nivel_admin.php">
                            <i class='bx bx-layer'></i> Niveles
                        </a>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/subnivel.php">
                            <i class='bx bx-layer'></i> Subniveles
                        </a>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/especialidades.php">
                            <i class='bx bx-book-content'></i> Especialidades
                        </a>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/materia_admin.php">
                            <i class='bx bx-book-content'></i> Materias
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/calificacion_admin.php">
                            <i class='bx bx-file'></i> Calificaciones
                        </a>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/curso_admin.php">
                            <i class='bx bxs-graduation'></i> Cursos
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/gestionar_academico.php">
                            <i class='bx bxs-calendar'></i> Ciclos Académicos
                        </a>
                        <!--<a class="collapse-item" href="http://localhost/sistema_notas/views/admin/subir_nivel.php">
                            <i class='bx bxs-graduation'></i> Niveles Académicos
                        </a>-->
                    </div>
                    <!-- Sección de Usuarios por Agregar -->
                    <div class="submenu-section">
                        <h6 class="collapse-header">Agregar Académicos</h6>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/Crud/admin/paralelo/agregar_paralelo.php">
                            <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                            Agregar Paralelos
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/Crud/admin/jornada/agregar_jornada.php">
                            <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                            Agregar Jornadas
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/Crud/admin/periodo_academico/agregar_periodo.php">
                            <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                            Agregar Periodos
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/Crud/admin/historial_academico/agregar_historial.php">
                            <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                            Agregar Historiales
                        </a>
                    </div>
                </div>
            </div>
        </li>

        <!-- Divisor -->
        <hr class="sidebar-divider d-none d-md-block">
        <!-- Activador de la Barra Lateral (Barra Lateral) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>
    <!-- Fin de la Barra Lateral -->

    <!-- Contenido de la Página -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Contenido Principal -->
        <div id="content">

            <!-- Barra de Navegación Superior -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                <!-- Barra de Navegación - Alternador de la Barra Lateral (Barra Superior) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3"
                    aria-label="Alternar barra lateral">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </button>


                <!-- Menú de la Barra de Navegación - Contenido (Barra Superior) -->
                <ul class="navbar-nav ml-auto">

                    <!-- Ítem de la Barra de Navegación - Enlace de Uso Libre (Barra Superior) -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Menú del usuario">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?php
                                if (isset($_SESSION['nombres'], $_SESSION['apellidos'], $_SESSION['rol'])) {
                                    // Mostrar el nombre completo
                                    echo "<span class='user-name'>" . $_SESSION['nombres'] . " " . $_SESSION['apellidos'] . "</span>";
                                    // Línea vertical separadora
                                    echo "<span class='divider mx-2'></span>";
                                    // Mostrar el rol en un badge
                                    echo "<span class='badge badge-pill badge-info text-white'>" . $_SESSION['rol'] . "</span>";
                                    // Icono de usuario
                                    echo "<i class='bx bx-user-circle ml-2' aria-hidden='true'></i>";
                                }
                                ?>
                            </span>
                        </a>

                        <!-- Menú desplegable - Contenido (Barra Superior) -->
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="http://localhost/sistema_notas/views/admin/perfil.php">
                                <i class="bx bx-user" style="font-size: 14px; margin-right: 10px;"
                                    aria-hidden="true"></i>
                                Perfil
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="bx bx-log-out-circle" style="font-size: 14px; margin-right: 10px;"
                                    aria-hidden="true"></i>
                                Cerrar Sesión
                            </a>
                        </div>
                        <!-- Fin del Menú desplegable - Contenido (Barra Superior) -->

                    </li>
                    <!-- Fin del Ítem de la Barra de Navegación - Enlace de Uso Libre (Barra Superior) -->

                </ul>
                <!-- Fin del Menú de la Barra de Navegación - Contenido (Barra Superior) -->

            </nav>
            <!-- Fin del Encabezado de la Barra de Navegación - Inicio de Sesión (Barra Superior) -->

            <!-- Logout Modal -->
            <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="logoutModalLabel">Confirmar cierre de sesión</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ¿Estás seguro de que deseas cerrar tu sesión actual? Haz clic en "Cerrar Sesión" para
                            confirmar.
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal" aria-label="Cancelar">
                                Cancelar
                            </button>
                            <a class="btn btn-primary" href="http://localhost/sistema_notas/login.php"
                                title="Cerrar sesión">
                                Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fin del Logout Modal -->