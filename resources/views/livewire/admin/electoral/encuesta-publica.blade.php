<div
    style="background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
    <div class="button-container">

        <!-- Botón de Google Play -->
        @if ($plataforma == 'android' || $plataforma == 'escritorio')
            <button class="store-button">
                <div class="store-icon">
                    <svg xmlns="http://w3.org" viewBox="0 0 512 512" width="100%" height="100%">
                        <path fill="#00c6ff"
                            d="M16 28.5V483.5c0 10 5.4 18.9 13.5 23.9l243-243L29.5 4.6C21.4 9.6 16 18.5 16 28.5z" />
                        <path fill="#00e765"
                            d="M331.4 207.2L272.5 264.4l58.9 57.2L500 223c21-12 21-31.7 0-43.7L331.4 207.2z" />
                        <path fill="#ffdd00" d="M29.5 4.6l243 243 58.9-57.2L132.8 11.2c-41-23.7-79.6-11-103.3-6.6z" />
                        <path fill="#ff3a44"
                            d="M29.5 507.4c23.7 4.4 62.3 17.1 103.3-6.6l198.6-179.2-58.9-57.2-243 243z" />
                    </svg>
                </div>


                <div class="store-text">
                    <span class="top-text">DISPONIBLE EN</span>
                    <span class="main-text">Google Play</span>
                </div>
            </button>
        @endif
        <!-- Botón de App Store -->
        @if ($plataforma == 'ios' || $plataforma == 'escritorio')
            <button class="store-button">
                <div class="store-icon">
                    <!-- Se removió el fondo azul del SVG original para que luzca blanco sobre el botón negro tradicional -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-apple" aria-hidden="true"><path d="M12 6.528V3a1 1 0 0 1 1-1h0"></path><path d="M18.237 21A15 15 0 0 0 22 11a6 6 0 0 0-10-4.472A6 6 0 0 0 2 11a15.1 15.1 0 0 0 3.763 10 3 3 0 0 0 3.648.648 5.5 5.5 0 0 1 5.178 0A3 3 0 0 0 18.237 21"></path></svg>
                </div>
                <div class="store-text">
                    <span class="top-text">Consíguelo en el</span>
                    <span class="main-text">App Store</span>
                </div>
            </button>
        @endif
    </div>
    @if (!$encuesta)
        <p style="color: #100222; text-align: center; font-style: italic;">No hay encuestas de opinión activas en este
            momento.</p>
    @else
        <h4
            style="margin-top: 0; color: #ffffff; font-size: 1.15em; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 8px;">
            📊 Encuesta Stereo 92: {{ $encuesta->pregunta }}
        </h4>

        @if (!$yaVoto)
            <!-- FORMULARIO DE VOTACIÓN DE OYENTES -->
            <form wire:submit.prevent="votar">
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                    @foreach ($encuesta->opciones as $opc)
                        <label
                            style="display: flex; align-items: center; padding: 10px 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 6px; cursor: pointer; transition: background 0.2s;">
                            <input type="radio" wire:model="opcionSeleccionada" value="{{ $opc->id }}"
                                style="margin-right: 12px; transform: scale(1.2); accent-color: #ef4444;">
                            <span style="color: #cbd5e1; font-size: 0.95em;">{{ $opc->opcion }}</span>
                        </label>
                    @endforeach
                </div>
                @error('opcionSeleccionada')
                    <p style="color: #ef4444; font-size: 0.85em; font-weight: bold; margin-bottom: 10px;">⚠️
                        {{ $message }}</p>
                @enderror

                <button type="submit"
                    style="width: 100%; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: background 0.2s;">
                    📥 Enviar Mi Opinión
                </button>
            </form>
        @else
            <!-- PANTALLA DE RESULTADOS EN TIEMPO REAL -->
            <div style="display: flex; flex-direction: column; gap: 15px;">
                @foreach ($encuesta->opciones as $opc)
                    @php
                        $porcentaje = $totalVotos > 0 ? round(($opc->votos / $totalVotos) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div
                            style="display: flex; justify-content: space-between; font-size: 0.9em; margin-bottom: 4px; color: #cbd5e1;">
                            <span>{{ $opc->opcion }}</span>
                            <strong style="color: #f59e0b;">{{ $porcentaje }}% <span
                                    style="font-weight:normal; color:#64748b;">({{ $opc->votos }})</span></strong>
                        </div>
                        <div
                            style="width: 100%; background: rgba(255,255,255,0.1); height: 8px; border-radius: 4px; overflow: hidden;">
                            <div
                                style="background: linear-gradient(90deg, #f59e0b, #ef4444); width: {{ $porcentaje }}%; height: 100%; border-radius: 4px; transition: width 0.5s ease-out;">
                            </div>
                        </div>
                    </div>
                @endforeach

                <div
                    style="text-align: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px; margin-top: 5px; font-size: 0.8em; color: #64748b;">
                    🔒 Gracias por participar. Total de opiniones: <strong>{{ number_format($totalVotos) }}</strong>
                </div>
            </div>
        @endif
    @endif
    <style>
        /* Contenedor principal */
        .button-container {
            display: flex;
            gap: 15px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        /* Estilos base del botón */
        .store-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #111111;
            color: #ffffff;
            border: 1px solid #333333;
            border-radius: 8px;
            padding: 8px 16px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s ease;
            min-width: 60px;
        }

        .store-button:hover {
            background-color: #222222;
        }

        /* Contenedor del icono SVG */
        .store-icon {
            width: 28px;
            height: 28px;
            margin-right: 6px;
            display: flex;
            align-items: center;
        }

        /* Contenedor del texto */
        .store-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.2;
        }

        .store-text .top-text {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #aaaaaa;
        }

        .store-text .main-text {
            font-size: 16px;
            font-weight: 600;
        }
    </style>
</div>
