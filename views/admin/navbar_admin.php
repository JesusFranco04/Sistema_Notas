<div id="wrapper" class="d-flex">

        <!-- Barra Lateral -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center"
                href="http://localhost/sistema_notas/views/admin/index_admin.html">
                <div class="sidebar-brand-icon rotate-n-25">
                    <i class='bx bx-book-reader' style="font-size: 36px;"></i>
                </div>
                <div class="sidebar-brand-text mx-3" style="font-size: 14px;">SISTEMA DE GESTIÓN<span
                        class="acronym">-UEBF</span></div>
            </a>
            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOne"
                    aria-expanded="true" aria-controls="collapseOne">
                    <i class='bx bxs-home' style="font-size: 14px;"></i>
                    <span class="nav-text">Inicio</span>
                </a>
                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <div class="submenu-section">
                            <h6 class="collapse-header">Dashboard</h6>
                            <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/index_admin.html">
                                <i class='bx bx-file'></i> Página de Inicio
                            </a>
                        </div>
                        <div class="submenu-section">
                            <h6 class="collapse-header">Análisis
                                numérico</h6>
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/estadistica_admin.htm">
                                <i class='bx bx-stats'></i> Estadísticas
                            </a>
                        </div>
                    </div>
                </div>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider">
            <!-- Encabezado - Usuarios -->
            <div class="sidebar-heading">
                Usuarios
            </div>
            <!-- Elemento de Navegación - Gestión Usuarios -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class='bx bxs-user' style="font-size: 14px;"></i>
                    <span class="nav-text">Gestión Usuarios</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <!-- Sección de Usuarios Registrados -->
                        <div class="submenu-section">
                            <h6 class="collapse-header">Usuarios
                                Registrados</h6>
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/vistaprofe_admin.html">
                                <i class='bx bx-user' style="font-size: 18px; margin-right: 10px;"></i>
                                Profesores
                            </a>
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/vistaestudiante_admin.html">
                                <i class='bx bx-user-pin' style="font-size: 18px; margin-right: 10px;"></i>
                                Estudiantes
                            </a>
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/vistaspadres_admin.html">
                                <i class='bx bx-user-check' style="font-size: 18px; margin-right: 10px;"></i>
                                Padres
                            </a>
                        </div>

                        <!-- Sección de Usuarios por Agregar -->
                        <div class="submenu-section">
                            <h6 class="collapse-header">Agregar
                                Usuarios</h6>
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/agregarprofe_admin.html">
                                <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                                Agregar Profesor
                            </a>
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/agregarestudiante_admin.html">
                                <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                                Agregar Estudiante
                            </a>
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/agregarpadres_admin.html">
                                <i class='bx bx-plus' style="font-size: 18px; margin-right: 10px;"></i>
                                Agregar Padre
                            </a>
                        </div>
                    </div>
                </div>
            </li>
            <!-- Divider -->
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
                            <h6 class="collapse-header">Coordinación
                                Académica</h6>
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/calificacion_admin.html">
                                <i class='bx bx-file'></i> Calificaciones
                            </a>
                        </div>
                        <!-- Subsección de Cursos -->
                        <div class="submenu-section">
                            <a class="collapse-item" href="http://localhost/sistema_notas/views/admin/curso_admin.html">
                                <i class='bx bx-book-alt'></i> Cursos
                            </a>
                        </div>
                        <!-- Subsección de Materias -->
                        <div class="submenu-section">
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/materia_admin.html">
                                <i class='bx bx-book-content'></i> Materias
                            </a>
                        </div>
                        <!-- Subsección de Jornadas -->
                        <div class="submenu-section">
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/jornada_admin.html">
                                <i class='bx bx-time'></i> Jornadas
                            </a>
                        </div>
                        <!-- Subsección de Período Lectivo -->
                        <div class="submenu-section">
                            <a class="collapse-item"
                                href="http://localhost/sistema_notas/views/admin/periodo_admin.html">
                                <i class='bx bx-calendar'></i> Período
                                Lectivo
                            </a>
                        </div>
                    </div>
                </div>
            </li>
            <!-- Divider -->
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
                        <a class="collapse-item"
                            href="http://localhost/sistema_notas/views/admin/reportesnotas_admin.html">
                            <i class='bx bx-file' style="font-size: 14px;"></i> Notas
                        </a>
                    </div>
                </div>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider">
            <!-- Encabezado - Actividades -->
            <div class="sidebar-heading">
                Actividades
            </div>
            <!-- Elemento de Navegación - Solicitudes -->
            <li class="nav-item">
                <a class="nav-link" href="http://localhost/sistema_notas/views/admin/ver_soli.php">
                    <i class='bx bxs-comment-detail' style="font-size: 13px;"></i>
                    <span class="nav-text">Solicitudes</span>
                </a>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">
            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Allison
                                    apellidos</span>
                                <img class="img-profile rounded-circle" src="agenda.png">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    salir
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>