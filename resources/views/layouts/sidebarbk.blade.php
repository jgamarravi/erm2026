<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
    <!-- Logo de la Empresa -->
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link text-decoration-none">
            <i class="bi bi-cpu-fill text-primary me-2"></i>
            <span class="brand-text fw-bold text-white">ERM 2026</span>
        </a>
    </div>

    <!-- Contenedor de Navegación -->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">

                <!-- SECCIÓN 1: PANEL GENERAL -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p class="text-white">Dashboard</p>
                    </a>
                </li>
                @can('gestionar portada')
                    <li class="nav-item">
                        <a href="{{ route('admin.portada') }}"
                            class="nav-link {{ request()->routeIs('admin.portada') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-badge-fill text-teal"></i>
                            <p class="text-white">Portada</p>
                        </a>
                    </li>
                @endcan

                <!-- SECCIÓN 2: PROCESAMIENTO ELECTORAL (DIGITACIÓN Y REPORTES) -->
                <li class="nav-header text-uppercase fs-7 text-muted px-3 mt-3">Procesamiento Electoral</li>

                @role('Administrador|Supervisor|Digitador')
                    <li class="nav-item">
                        <a href="{{ route('admin.actas') }}"
                            class="nav-link {{ request()->routeIs('admin.actas') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-file-earmark-bar-graph-fill text-success"></i>
                            <p class="text-white">Digitación de Actas</p>
                        </a>
                    </li>
                @endrole
                @role('Administrador|Supervisor|Locutor')
                    <li class="nav-item">
                        <a href="{{ route('admin.encuestaradio') }}"
                            class="nav-link {{ request()->routeIs('admin.encuestaradio') ? 'active' : '' }}">
                            <svg xmlns="http://w3.org" width="16" height="16" fill="currentColor"
                                class="bi bi-clipboard-plus me-2" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7" />
                                <path
                                    d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z" />
                                <path
                                    d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z" />
                            </svg>
                            <p class="text-white">Lanzar encuesta</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.cronicas') }}"
                            class="nav-link {{ request()->routeIs('admin.encuestaradio') ? 'active' : '' }}">
                            <svg xmlns="http://w3.org" width="16" height="16" fill="currentColor"
                                class="bi bi-pen me-2" viewBox="0 0 16 16">
                                <path
                                    d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z" />
                            </svg>
                            <p class="text-white">Redactar Crónicas</p>
                        </a>
                    </li>
                @endrole

                <li class="nav-item">
                    <a href="{{ route('admin.cifra') }}"
                        class="nav-link {{ request()->routeIs('admin.cifra') ? 'active' : '' }}">
                        <svg xmlns="http://w3.org" width="16" height="16" fill="currentColor"
                            class="bi bi-printer me-2" viewBox="0 0 16 16">
                            <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1" />
                            <path
                                d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1" />
                        </svg>

                        <p class="text-white">Reporte Consolidado (D'Hondt)</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.autoridades') }}"
                        class="nav-link {{ request()->routeIs('admin.autoridades') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-badge-fill text-teal"></i>
                        <p class="text-white">Autoridades Electas</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.encuesta') }}"
                        class="nav-link {{ request()->routeIs('admin.encuesta') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-badge-fill text-teal"></i>
                        <p class="text-white">Imprimir Ficha</p>
                    </a>
                </li>
                <!-- SECCIÓN 3: FISCALIZACIÓN Y AUDITORÍA JURÍDICA (JEE) -->
                @role('Administrador|Supervisor')
                    <li class="nav-header text-uppercase fs-7 text-muted px-3 mt-3">Fiscalización Jurídica</li>

                    <li class="nav-item">
                        <a href="{{ route('admin.observadas') }}"
                            class="nav-link {{ request()->routeIs('admin.observadas') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-shield-exclamation text-warning"></i>
                            <p class="text-white">Resolución Actas Observadas</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.ubigeopartidos') }}"
                            class="nav-link {{ request()->routeIs('admin.ubigeopartidos') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-building-check text-warning"></i>
                            <p class="text-white">Partidos por Ubigeo</p>
                        </a>
                    </li>
                @endrole

                <!-- SECCIÓN 4: CONFIGURACIÓN Y ARCHIVOS DE SISTEMA (SÓLO ADMIN / SUPERVISOR) -->
                @role('Administrador|Supervisor|Clerk')
                    <li class="nav-header text-uppercase fs-7 text-muted px-3 mt-3">Archivos de Sistema</li>

                    <li
                        class="nav-item {{ request()->routeIs('admin.ubigeos') || request()->routeIs('admin.locales') || request()->routeIs('admin.candidatos') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ request()->routeIs('admin.ubigeos') || request()->routeIs('admin.locales') || request()->routeIs('admin.candidatos') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-box-seam"></i>
                            <p class="text-white">
                                Padrón Territorial
                                <i class="end-icon bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview pl-3">
                            <li class="nav-item">
                                <a href="{{ route('admin.ubigeos') }}"
                                    class="nav-link {{ request()->routeIs('admin.ubigeos') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-globe-americas"></i>
                                    <p class="text-white">Ubigeos Perú</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.locales') }}"
                                    class="nav-link {{ request()->routeIs('admin.locales') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-geo-alt"></i>
                                    <p class="text-white">Locales y Mesas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.candidatos') }}"
                                    class="nav-link {{ request()->routeIs('admin.candidatos') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-person-vcard-fill"></i>
                                    <p class="text-white">Partidos y Candidatos</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endrole

                <!-- SECCIÓN 5: CONTROL DE SEGURIDAD (EXCLUSIVO ADMINISTRADOR) -->
                @role('Administrador')
                    <li class="nav-header text-uppercase fs-7 text-muted px-3 mt-3">Seguridad del Sistema</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.usuarios') }}"
                            class="nav-link {{ request()->routeIs('admin.usuarios') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-lock-fill text-danger"></i>
                            <p class="text-white">Roles y Permisos</p>
                        </a>
                    </li>
                @endrole

            </ul>
        </nav>
    </div>
</aside>
