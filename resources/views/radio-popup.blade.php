<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stereo 92 FM - Sintonía Continua</title>
    <link href="https://googleapis.com" rel="stylesheet">
    <style>
        body {
            margin: 0; padding: 15px; background: #0f172a; color: white;
            font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 15px;
            overflow: hidden; height: 100vh; box-sizing: border-box;
        }
        .logo-box img { height: 50px; object-fit: contain; }
        .info-box { flex: 1; }
        .title { font-size: 0.95em; font-weight: bold; margin: 0; }
        .tagline { font-size: 0.75em; color: #d97706; font-style: italic; margin: 2px 0 8px 0; }
        audio { width: 100%; height: 30px; accent-color: #e11d48; }
    </style>
</head>
<body>

    <div class="logo-box">
        <img src="{{ asset('storage/logos/logoStereo.png') }}" alt="Logo">
    </div>

    <div class="info-box">
        <p class="title">Radio Stereo 92 FM</p>
        <p class="tagline">🎵 ¡Más Radio! — Huacho</p>
        
        <!-- Reproductor de streaming configurado con inicio automático -->
        <audio src="https://sechin.grupocentroserver.com/listen/radiostereo92/radio.mp3" controls autoplay></audio>
    </div>

</body>
</html>
