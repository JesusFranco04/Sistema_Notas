<?php
session_start();
?>
<div id="wrapper" class="d-flex">

    <!-- Barra Lateral -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <!-- Barra Lateral - Marca -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center"
            href="http://localhost/sistema_notas/views/admin/index_admin.php">
            <div class="sidebar-brand-icon rotate-n-25">
                <i class='bx bx-book-reader' style="font-size: 36px;"></i>
            </div>
            <div class="sidebar-brand-text mx-3" style="font-size: 14px;">SISTEMA DE GESTIÓN<span
                    class="acronym">-UEBF</span></div>
        </a>
        <!-- Divisor -->
        <hr class="sidebar-divider my-0">
        <!-- Elemento de Navegación - Panel de Control -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOne"
                aria-expanded="true" aria-controls="collapseOne">
                <i class='bx bxs-home' style="font-size: 14px;"></i>
                <span class="nav-text">Inicio</span>
            </a>
            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <div class="submenu-section">
                        <h6 class="collapse-header">Panel de Control</h6>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/index_admin.php">
                            <i class='bx bx-file'></i> Página de Inicio
                        </a>
                    </div>
                    <div class="submenu-section">
                        <h6 class="collapse-header">Análisis Numérico</h6>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/estadistica_admin.php">
                            <i class='bx bx-stats'></i> Estadísticas
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
                aria-expanded="true" aria-controls="collapseTwo">
                <i class='bx bxs-user' style="font-size: 14px;"></i>
                <span class="nav-text">Gestión de Usuarios</span>
            </a>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <!-- Sección de Usuarios Registrados -->
                    <div class="submenu-section">
                        <h6 class="collapse-header">Usuarios Registrados</h6>
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/vistaprofe_admin.php">
                            <i class='bx bx-user' style="font-size: 18px; margin-right: 10px;"></i>
                            Profesores
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/vistaestudiante_admin.php">
                            <i class='bx bx-user-pin' style="font-size: 18px; margin-right: 10px;"></i>
                            Estudiantes
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/vistaspadres_admin.php">
                            <i class='bx bx-user-check' style="font-size: 18px; margin-right: 10px;"></i>
                            Padres
                        </a>
                    </div>

                    <!-- Sección de Usuarios por Agregar -->
                    <div class="submenu-section">
                        <h6 class="collapse-header">Agregar Usuarios</h6>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/agregarprofe_admin.php">
                            <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                            Agregar Profesor
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/agregarestudiante_admin.php">
                            <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                            Agregar Estudiante
                        </a>
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/agregarpadres_admin.php">
                            <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                            Agregar Padre
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
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/calificacion_admin.php">
                            <i class='bx bx-file'></i> Calificaciones
                        </a>
                    </div>
                    <!-- Subsección de Cursos -->
                    <div class="submenu-section">
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/curso_admin.php">
                            <i class='bx bx-book-alt'></i> Cursos
                        </a>
                    </div>
                    <!-- Subsección de Materias -->
                    <div class="submenu-section">
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/materia_admin.php">
                            <i class='bx bx-book-content'></i> Materias
                        </a>
                    </div>
                    <!-- Subsección de Jornadas -->
                    <div class="submenu-section">
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/jornada_admin.php">
                            <i class='bx bx-time'></i> Jornadas
                        </a>
                    </div>
                    <!-- Subsección de Período Lectivo -->
                    <div class="submenu-section">
                        <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/periodo_admin.php">
                            <i class='bx bx-calendar'></i> Período Lectivo
                        </a>
                    </div>
                </div>
            </div>
        </li>
        <!-- Divisor -->
        <hr class="sidebar-divider">
        <!-- Encabezado - Informes -->
        <div class="sidebar-heading">
            Informes
        </div>
        <!-- Elemento de Navegación - Reportes -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFour"
                aria-expanded="true" aria-controls="collapseFour">
                <i class='bx bxs-report' style="font-size: 14px;"></i>
                <span class="nav-text">Reportes</span>
            </a>
            <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded submenu-section">
                    <h6 class="collapse-header">Documentos</h6>
                    <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/reportesnotas_admin.php">
                        <i class='bx bx-file' style="font-size: 14px;"></i> Notas
                    </a>
                </div>
            </div>
        </li>
        <!-- Divisor -->
        <hr class="sidebar-divider">
        <!-- Encabezado - Actividades -->
        <div class="sidebar-heading">
            Actividades
        </div>
        <!-- Elemento de Navegación - Solicitudes -->
        <li class="nav-item">
            <a class="nav-link" href="http://localhost/sistema_notas/views/admin/solicitud_admin.php">
                <i class='bx bxs-comment-detail' style="font-size: 13px;"></i>
                <span class="nav-text">Solicitudes</span>
            </a>
        </li>
        <!-- Divisor -->
        <hr class="sidebar-divider d-none d-md-block">
        <!-- Activador de la Barra Lateral (Barra Lateral) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Contenido Principal -->
        <div id="content">

            <!-- Barra Superior -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                <!-- Activador de la Barra Lateral (Barra Superior) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <!-- Barra de Navegación Superior -->
                <ul class="navbar-nav ml-auto">
                    <!-- Elemento de Navegación - Menú Desplegable de Búsqueda (Visible Solo XS) -->
                    <li class="nav-item dropdown no-arrow d-sm-none">
                        <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-search fa-fw"></i>
                        </a>
                        <!-- Menú Desplegable - Mensajes -->
                        <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                            aria-labelledby="searchDropdown">
                            <form class="form-inline mr-auto w-100 navbar-search">
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light border-0 small"
                                        placeholder="Buscar..." aria-label="Buscar" aria-describedby="basic-addon2">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button">
                                            <i class="fas fa-search fa-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>
                    <?php echo $_SESSION["rol"] ?>
                    <div class="topbar-divider d-none d-sm-block"></div>
                    <!-- Elemento de Navegación - Información del Usuario -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span
                                class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION["nombres"]." ".$_SESSION["apellidos"]  ?></span>
                            <img class="img-profile rounded-circle" src="http://localhost<?php echo $_SESSION['archivo']; ?>" alt="Perfil"></img>
                        </a>
                        <!-- Menú Desplegable - Información del Usuario -->
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Perfil
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href='login.php'data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Salir
                            </a>
                        </div>
                    </li>

                </ul>

            </nav>