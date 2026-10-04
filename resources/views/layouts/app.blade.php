<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- 1. CORREGIDO: Google Fonts (Source Sans Pro) -->
    <link rel="stylesheet" href="https://googleapis.com">

    <!-- 2. CORREGIDO: Iconos de FontAwesome (Esencial para que se vean los iconos que pusimos) -->
    <link rel="stylesheet" href="https://cloudflare.com">
<style>
    [x-cloak] { display: none !important; }
</style>

    <!-- Estilos compilados por Vite (AdminLTE y Bootstrap-Icons) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <!-- Envoltura Principal de AdminLTE 4 -->
    <div class="app-wrapper">

        <!-- Barra de Navegación Superior -->
        <nav class="app-header navbar navbar-expand bg-body shadow-sm">
            <div class="container-fluid">
                <!-- Botón para colapsar Menú Izquierdo -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                </ul>

                <!-- Contenedor del Menú de Usuario Derecho -->
                <ul class="navbar-nav ms-auto px-3">
                    @livewire('navigation-menu')
                </ul>
            </div>
        </nav>


        <!-- Barra Lateral Izquierda (Asegura estas clases para el menú oscuro) -->
        @include('layouts.sidebar')

        <!-- Contenedor del Contenido de Trabajo Derecho -->
        <main class="app-main p-4">
            <div class="container-fluid">
                {{ $slot }}

            </div>
        </main>

    </div>
    <!-- jQuery (Requerido por AdminLTE 3) -->
    <script src="https://jquery.com"></script>

    @livewireScripts

</body>

</html>
