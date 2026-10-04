<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radio Stereo 92 FM - Huacho</title>
    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    @livewireStyles

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Cabecera Principal */
        header {
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-bottom: 3px solid #e11d48;
            /* Rojo del Logo */
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-img {
            height: 55px;
            width: auto;
            object-fit: contain;
        }

        /* Contenedor del Reproductor de Streaming */
        .player-container {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            padding: 6px 18px;
            border-radius: 30px;
            border: 1px solid #cbd5e1;
            gap: 15px;
        }

        .contenedor {
            width: 100%;
            max-width: 900px;
            /* Ajusta según tu diseño */
            overflow: hidden;
            /* Evita que el float se desborde del contenedor */
        }

        .contenedor p {
            margin-top: 0;
            font-size: 1.05em;
            color: #475569;
            line-height: 1.6;
            /* Quita el espacio por defecto que empuja el texto abajo */
        }

        .imagen-flotante {
            float: left;
            /* Mueve la imagen a la izquierda y hace que el texto la rodee */
            width: 150px;
            /* Ancho de tu imagen */
            height: auto;
            /* Mantiene la proporción de la imagen */
            margin-right: 15px;
            /* Separación horizontal con el texto */
            margin-bottom: 8px;
            /* Separación vertical con el texto de abajo */
        }

        .btn-player {
            background: #e11d48;
            color: white;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1em;
            transition: background 0.2s, transform 0.1s;
            box-shadow: 0 2px 6px rgba(225, 29, 72, 0.3);
        }

        .btn-player:hover {
            background: #be123c;
            transform: scale(1.05);
        }

        .player-info {
            display: flex;
            flex-direction: column;
        }

        .player-status {
            font-size: 0.75em;
            font-weight: 700;
            color: #e11d48;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .player-title {
            font-size: 0.9em;
            font-weight: 600;
            color: #334155;
        }

        /* Animación de ondas de audio */
        .audio-waves {
            display: flex;
            align-items: flex-end;
            gap: 2px;
            height: 12px;
            width: 14px;
        }

        .wave-bar {
            width: 2px;
            background: #e11d48;
            height: 3px;
            border-radius: 1px;
        }

        .playing .wave-bar {
            animation: bounce 0.8s ease-in-out infinite alternate;
        }

        .playing .wave-bar:nth-child(2) {
            animation-delay: 0.2s;
        }

        .playing .wave-bar:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {
            100% {
                height: 12px;
            }
        }

        /* Botones de Estilo de Sesión Estándar */
        .btn {
            padding: 10px 24px;
            border-radius: 25px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.9em;
            text-transform: uppercase;
        }

        .btn-login {
            color: #e11d48;
            border: 2px solid #e11d48;
            margin-right: 10px;
            background: none;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #fff1f2;
        }

        .btn-register {
            background: #e11d48;
            color: #ffffff;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);
        }

        .btn-register:hover {
            background: #be123c;
            transform: translateY(-1px);
        }

        /* Sección Hero Central */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            max-width: 1140px;
            margin: 0 auto;
            width: 100%;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 40px;
            align-items: center;
            width: 100%;
        }

        .content-left h1 {
            font-size: 3.2em;
            line-height: 1.15;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 15px;
        }

        .content-left .highlight {
            color: #e11d48;
        }

        .tagline {
            font-size: 1.6em;
            color: #d97706;
            /* Dorado del Logo */
            font-weight: 700;
            margin-bottom: 15px;
            font-style: italic;
        }

        .description {
            font-size: 1.05em;
            color: #475569;
            line-height: 1.6;
        }

        .content-right {
            display: flex;
            justify-content: center;
        }

        @media (max-width: 968px) {
            header {
                padding: 15px 20px;
                justify-content: center;
            }

            .player-container {
                order: 3;
                width: 100%;
                justify-content: center;
            }

            .hero-grid {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 35px;
            }

            .content-left h1 {
                font-size: 2.3em;
            }

            .content-right {
                max-width: 100% !important;
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body>

    <!-- CABECERA DE LA PORTADA -->
    <header>
        <div class="logo-container">
            <img src="{{ asset('storage/logos/logoStereo.png') }}" alt="Logo Stereo 92" class="logo-img">
            <div class="tagline">¡Más Radio!</div>
        </div>

        <!-- REPRODUCTOR DE STREAMING AUDIO LIVE -->
        <div class="player-container">
            <!-- URL de transmisión remota del servidor Icecast/Shoutcast -->
            <audio id="radioStream" src="https://servidor.com" preload="none"></audio>

            <button id="playerToggle" class="btn-player" style="display:none;" onclick="toggleRadio()">▶️</button>


            <!-- Botón de apertura de ventana emergente Pop-up -->
            <button id="btnPopupRadio" onclick="abrirPopupRadio()"
                style="margin-left: 10px; padding: 4px 10px; background: #3252e0; color: white; border: none; border-radius: 15px; font-size: 0.75em; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2;""
                title="Sigue escuchando mientras navegas el panel">
                ⚡En vivo
            </button>
        </div>

        @if (Route::has('login'))
            <nav class="flex items-center justify-end gap-4">
                @auth
                    <a href="{{ url('/admin/dashboard') }}"
                        class="inline-block px-5 py-1.5 border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] rounded-sm text-sm leading-normal">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="btn btn-info inline-block px-5 py-1.5 text-[#1b1b18] border border-transparent hover:border-[#19140035] rounded-sm text-sm leading-normal">
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="btn btn-info inline-block px-5 py-1.5 border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] rounded-sm text-sm leading-normal">
                            Register
                        </a>
                    @endif
                @endauth
            </nav>
        @endif
    </header>

    <!-- CUERPO PRINCIPAL DE LA PLATAFORMA -->
    <main style="flex: 1; padding: 40px 20px; max-width: 1140px; margin: 0 auto; width: 100%;">
        <div class="hero-grid"
            style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 40px; align-items: start; width: 100%;">

            <!-- COLUMNA IZQUIERDA: MENSAJE E IMAGEN DINÁMICOS -->
            <div class="contenedor" style="display: flex; flex-direction: column; gap: 15px;">

                <h1 style="font-size: 3.2em; line-height: 1.15; font-weight: 700; color: #0f172a; margin: 0;">
                    La señal líder de <br><span class="highlight" style="color: #e11d48;">Huacho</span>
                </h1>
                <div class="contenedor">
                    @php
                        $configuracionMensaje = \App\Models\Admin\Configuracion::where(
                            'clave',
                            'mensaje_bienvenida',
                        )->first();
                        $textoBienvenidaFinal =
                            $configuracionMensaje && $configuracionMensaje->valor
                                ? $configuracionMensaje->valor
                                : 'Conectando a toda la provincia con el ritmo y la voz que nos identifica.';

                        $configuracionImagen = \App\Models\Admin\Configuracion::where(
                            'clave',
                            'imagen_portada',
                        )->first();
                        $imagenPortada = $configuracionImagen ? $configuracionImagen->valor : null;
                    @endphp
                    @if ($imagenPortada)
                        <img src="{{ asset('storage/' . $imagenPortada) }}" alt="Promoción Stereo 92"
                            class="imagen-flotante"
                            style="width: 40%; height: auto; display: block; object-fit: cover;">
                    @endif


                    <p class="description">
                        {{ $textoBienvenidaFinal }}
                    </p>
                </div>

                <!-- Imagen Publicitaria Promocional -->
            </div>

            <!-- COLUMNA DERECHA: LA ENCUESTA ALINEADA ARRIBA -->
            <div class="content-right"
                style="width: 100%; max-width: 360px; margin-left: auto; justify-self: end; position: sticky; top: 20px;">
                <livewire:admin.electoral.encuesta-publica />
            </div>

        </div>
    </main>
    <div style="width: 100%; clear: both;">
        <livewire:admin.electoral.noticias-publicas />
    </div>
    <!-- LISTADO INYECTADO: Crónicas de noticias, Modales de Lectura y Botón de WhatsApp -->


    <!-- PIE DE PÁGINA -->
    <footer
        style="text-align: center; padding: 20px; color: #64748b; font-size: 0.85em; border-top: 1px solid #e2e8f0; background: #ffffff;">
        <p>&copy; {{ date('Y') }} Radio Stereo 92 FM - Huacho, Perú. | ¡Más Radio!</p>
    </footer>

    @livewireScripts

    <script>
        function toggleRadio() {
            const audio = document.getElementById('radioStream');
            const button = document.getElementById('playerToggle');
            const waves = document.getElementById('waveContainer');
            const statusText = document.getElementById('statusText');

            if (audio.paused) {
                audio.load();
                audio.play().then(() => {
                    button.innerHTML = '⏸️';
                    if (waves) waves.classList.add('playing');
                    if (statusText) {
                        statusText.innerHTML = 'Al Aire';


                    }
                }).catch(error => {
                    console.error("Error al reproducir el streaming:", error);
                    alert("El servidor de audio no está disponible en este momento.");
                });
            } else {
                audio.pause();
                button.innerHTML = '▶️';
                if (waves) waves.classList.remove('playing');
                if (statusText) statusText.innerHTML = 'Pausado';
            }
        }

        // 🔥 SOLUCIÓN PARA ADMINLTE: Reactivar los menús desplegables al cambiar de página
        document.addEventListener("turbo:load", function() {
            // Si notas que los menús colapsables de AdminLTE dejan de funcionar al navegar,
            // descomenta la siguiente línea para forzar a AdminLTE a reinicializarse:
            if (typeof $.fn.Treeview !== 'undefined') {
                $('[data-widget="treeview"]').Treeview('init');
            }
        });

        function abrirPopupRadio() {
            // 1. Pausamos el audio de la página principal por si estaba encendido
            const audioPrincipal = document.getElementById('radioStream');
            if (!audioPrincipal.paused) {
                toggleRadio(); // Llama a tu función anterior para apagarlo limpiamente
            }

            // 2. Configuramos las dimensiones exactas de la mini-ventana flotante
            const ancho = 380;
            const alto = 150;
            const posicionLeft = (screen.width / 2) - (ancho / 2);
            const posicionTop = (screen.height / 2) - (alto / 2);

            // 3. Abrimos la ruta de la radio en modo popup independiente
            window.open(
                '/radio-player-envivo',
                'Stereo92Player',
                `width=${ancho},height=${alto},left=${posicionLeft},top=${posicionTop},resizable=false,scrollbars=no,status=no,toolbar=no,menubar=no`
            );
        }
    </script>
 
</body>

</html>
