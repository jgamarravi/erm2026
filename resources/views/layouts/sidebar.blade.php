<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
    <!-- Logotipo / Encabezado -->
    <div class="flex items-center space-x-2 px-3 py-4 border-b border-gray-700/50 mb-4">
        <!-- Ícono de Chip -->
        <svg class="w-5 h-5 text-blue-500" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 00-1 1v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1v-1a1 1 0 00-1-1z" />
        </svg>
        <span class="font-bold text-white text-lg tracking-wide">ERM 2026</span>
    </div>

    <!-- Menú de Navegación -->
    <nav class="space-y-4">

        <!-- SECCIÓN 1: PANEL PRINCIPAL -->
        <div x-data="{ open: {{ request()->is('admin/dashboard*', 'admin/autoridades*', 'admin/auditoria*', 'admin/resultados*') ? 'true' : 'false' }} }" >
            <button @click="open = !open"
                class="w-full flex items-center justify-between text-xs font-semibold tracking-wider text-gray-400 uppercase px-3 py-2 hover:text-white transition-colors duration-200">
                <span>Panel Principal</span>
                <svg :class="open ? 'transform rotate-180' : ''" class="w-3 h-3 transition-transform duration-200"
                    width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <ul x-show="open" x-cloak x-collapse class="mt-1 space-y-1 pl-4">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/dashboard*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                        <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.resultados') }}"
                        class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/resultados*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                        <svg xmlns="http://w3.org" width="16" height="16" fill="currentColor"
                            class="bi bi-bar-chart-line-fill me-2 text-warning" viewBox="0 0 16 16">
                            <path
                                d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1zm1 12h2V2h-2zm-3 0V7H7v7zm-5 0v-3H2v3z" />
                        </svg>
                        <span>Ver Resultados</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.autoridades') }}"
                        class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/autoridades*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                        <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Autoridades Electas</span>
                    </a>
                </li>
                @can('revisar actas')
                    <li>
                        <a href="{{ route('admin.auditoria') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/auditoria*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Auditoria</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        <!-- SECCIÓN 2: PROCESAMIENTO & MEDIOS -->
        <div x-data="{ open: {{ request()->is('admin/digitacion*', 'admin/portada*', 'admin/cronicas*', 'admin/imprimir/polling*', 'admin/encuesta*') ? 'true' : 'false' }} }">
            @hasanyrole('Administrador|Supervisor|Locutor|Digitador|Clerk')
                <button @click="open = !open"
                    class="w-full flex items-center justify-between text-xs font-semibold tracking-wider text-gray-400 uppercase px-3 py-2 hover:text-white transition-colors duration-200">
                    <span>Gestiones Radio</span>
                    <svg :class="open ? 'transform rotate-180' : ''" class="w-3 h-3 transition-transform duration-200"
                        width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            @endhasanyrole


            <ul x-show="open" x-cloak x-collapse class="mt-1 space-y-1 pl-4">
                @can('digitar actas')
                    <li>
                        <a href="{{ route('admin.digitacion') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/digitacion*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Digitacion</span>
                        </a>
                    </li>
                @endcan
                @can('gestionar portada')
                    <li>
                        <a href="{{ route('admin.portada') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/portada*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 5a1 1 0 011-1h14a1 1 0 011 1v12a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13h16M16 17v-4M8 13V9" />
                            </svg>
                            <span>Configurar Portada</span>
                        </a>
                    </li>
                @endcan
                @can('gestionar encuesta')
                    <li>
                        <a href="{{ route('admin.encuestaradio') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/encuestaradio*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            </svg>
                            <span>Lanzar Encuestas</span>
                        </a>
                    </li>
                @endcan
                @can('redactar cronica')
                    <li>
                        <a href="{{ route('admin.cronicas') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/cronicas*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1M19 20a2 2 0 002-2V8a2 2 0 00-2-2h-2m2 14h-2m0 0V6M14 12H8m6 4H8m2-8H8" />
                            </svg>
                            <span>Crónicas de Opinión</span>
                        </a>
                    </li>
                @endcan
                @can('imprimir polling')
                    <li>
                        <a href="{{ route('admin.polling') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/imprimir/polling*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Imprimir Encuesta</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
        <!-- SECCIÓN 3: ARCHIVOS DE TRABAJO -->
        <div x-data="{ open: {{ request()->is('admin/ubigeos*', 'admin/locales*', 'admin/candidatos*', 'admin/inscribir/partido*') ? 'true' : 'false' }} }">
            @hasanyrole('Administrador|Supervisor|Clerk')
                <button @click="open = !open"
                    class="w-full flex items-center justify-between text-xs font-semibold tracking-wider text-gray-400 uppercase px-3 py-2 hover:text-white transition-colors duration-200">
                    <span>Configuraciones</span>
                    <svg :class="open ? 'transform rotate-180' : ''" class="w-3 h-3 transition-transform duration-200"
                        width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            @endhasanyrole

            <ul x-show="open" x-cloak x-collapse class="mt-1 space-y-1 pl-4">
                @can('gestionar ubigeo')
                    <li>
                        <a href="{{ route('admin.ubigeos') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/ubigeos*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Escaños & Ubigeo</span>
                        </a>
                    </li>
                @endcan
                @can('gestionar colegio')
                    <li>
                        <a href="{{ route('admin.locales') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/locales*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Locales & Mesas</span>
                        </a>
                    </li>
                @endcan
                @can('gestionar partido')
                    <li>
                        <a href="{{ route('admin.candidatos') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/candidatos*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Partidos & Candidatos</span>
                        </a>
                    </li>
                @endcan
                @can('gestionar partido')
                    <li>
                        <a href="{{ route('admin.inscribir_partido') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/inscribir/partido*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <!-- Estructura institucional (Partido/Comité) -->
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 21h19.5m-18-10.5h16.5M2.25 13.5h19.5M4.5 10.5V6a2.25 2.25 0 0 1 2.25-2.25h10.5A2.25 2.25 0 0 1 19.5 6v4.5M10.5 13.5v7.5m3-7.5v7.5" />
                                <!-- Pin de Ubigeo flotante -->
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 1.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z" />
                            </svg>
                            <span>Partidos & Ubigeos</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        <!-- SECCIÓN 4: SUPERVISION / ADMINISTRACIÓN -->
        <div x-data="{ open: {{ request()->is('admin/usuarios*', 'admin/actas-observadas*') ? 'true' : 'false' }} }">
            @hasanyrole('Administrador|Supervisor')
                <button @click="open = !open"
                    class="w-full flex items-center justify-between text-xs font-semibold tracking-wider text-gray-400 uppercase px-3 py-2 hover:text-white transition-colors duration-200">
                    <span>Administración</span>
                    <svg :class="open ? 'transform rotate-180' : ''" class="w-3 h-3 transition-transform duration-200"
                        width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            @endhasanyrole

            <ul x-show="open" x-cloak x-collapse class="mt-1 space-y-1 pl-4">
                @can('gestionar usuarios')
                    <li>
                        <a href="{{ route('admin.usuarios') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/usuarios*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Gestionar Usuarios</span>
                        </a>
                    </li>
                @endcan
                @can('revisar actas')
                    <li>
                        <a href="{{ route('admin.observadas') }}"
                            class="flex items-center space-x-2 px-3 py-2 text-sm rounded transition-colors {{ request()->is('admin/observadas*') ? 'text-white font-medium bg-cyan-600 shadow-md' : 'text-gray-300 hover:text-white hover:bg-gray-700/30' }}">
                            <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Revisión Actas</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </nav>
</aside>
