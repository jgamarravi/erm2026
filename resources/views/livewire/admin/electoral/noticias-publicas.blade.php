<section style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 50px 20px; width: 100%;">
    <div style="max-width: 1140px; margin: 0 auto; width: 100%;">
        <h3 style="color: #0f172a; font-size: 1.8em; font-weight: 700; margin-bottom: 30px;">
            📰 Crónicas de Opinión
        </h3>

        <!-- La cuadrícula se adapta automáticamente a cuántas notas existan -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-bottom: 30px;">
            @foreach ($articulosNoticias as $articulo)
                <article
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">

                    @if ($articulo->imagen_path)
                        <div style="width: 100%; height: 200px; overflow: hidden; border-bottom: 2px solid #e11d48;">
                            <img src="{{ asset('storage/' . $articulo->imagen_path) }}"
                                style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    @else
                        <div
                            style="width: 100%; height: 200px; background: #e2e8f0; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size: 3em;">
                            📻</div>
                    @endif

                    <div
                        style="padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div
                                style="font-size: 0.8em; color: #d97706; font-weight: bold; margin-bottom: 5px; display: flex; justify-content: space-between;">
                                <span>📅 {{ $articulo->created_at->format('d/m/Y') }}</span>
                                <!-- NUEVO: Sello de Autoría en el Home -->
                                <span style="color: #475569;">✍️ Crónica por:
                                    <strong>{{ $articulo->locutora_firma ?? 'Stereo 92' }}</strong></span>
                            </div>
                            <h4 style="font-size: 1.2em; color: #0f172a; font-weight: 700; margin-bottom: 12px;">
                                {{ $articulo->titular }}
                            </h4>
                            <p style="color: #475569; font-size: 0.95em; line-height: 1.6;">
                                {{ Str::limit($articulo->contenido, 220, '...') }}
                            </p>
                        </div>

                        @if (strlen($articulo->contenido) > 220)
                            <div style="margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 10px;">
                                <a href="#nota-{{ $articulo->id }}"
                                    style="color: #e11d48; font-weight: bold; text-decoration: none; font-size: 0.9em;">
                                    Leer nota completa →
                                </a>
                            </div>
                            <!-- MODAL DE LECTURA ADAPTADO CON ESTADÍSTICAS DEL LOGO -->
                            <div id="nota-{{ $articulo->id }}" class="custom-modal-overlay">
                                <div class="custom-modal-box"
                                    style="max-width: 700px; padding: 30px; background: white; border-radius: 8px;">
                                    <a href="#" class="custom-modal-close">&times;</a>

                                    <!-- Cabecera de la noticia -->
                                    <h3
                                        style="color: #0f172a; margin-bottom: 5px; font-weight: bold; font-size: 1.5em; text-align: left;">
                                        {{ $articulo->titular }}
                                    </h3>
                                    <small
                                        style="color: #d97706; font-weight: bold; display: block; margin-bottom: 15px; text-transform: uppercase;">
                                        📅 Publicación: {{ $articulo->created_at->format('d/m/Y H:i') }}
                                    </small>

                                    <!-- Cuerpo del Artículo Redactado -->
                                    <div
                                        style="max-height: 40vh; overflow-y: auto; text-align: left; color: #334155; line-height: 1.6; white-space: pre-line; margin-bottom: 20px; padding-right: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
                                        {{ $articulo->contenido }}
                                    </div>

                                    <!-- REGLA COMPUESTA DINÁMICA: Si tiene encuesta vinculada, renderiza la votación acumulada de Huacho -->
                                    @if ($articulo->encuesta)
                                        @php
                                            $encuestaVinculada = $articulo->encuesta;
                                            $totalVotosNota = $encuestaVinculada->opciones->sum('votos');
                                        @endphp
                                        <div
                                            style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 15px; text-align: left;">
                                            <h5
                                                style="margin-top: 0; color: #e11d48; font-weight: bold; font-size: 0.95em; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                                📊 Sondeo de Opinión de Oyentes: "{{ $encuestaVinculada->pregunta }}"
                                            </h5>

                                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                                @foreach ($encuestaVinculada->opciones as $opc)
                                                    @php
                                                        $porcentaje =
                                                            $totalVotosNota > 0
                                                                ? round(($opc->votos / $totalVotosNota) * 100, 1)
                                                                : 0;
                                                    @endphp
                                                    <div>
                                                        <div
                                                            style="display: flex; justify-content: space-between; font-size: 0.85em; font-weight: bold; color: #475569; margin-bottom: 2px;">
                                                            <span>{{ $opc->opcion }}</span>
                                                            <span style="color: #e11d48;">{{ $porcentaje }}%
                                                                ({{ number_format($opc->votos) }} votos)
                                                            </span>
                                                        </div>
                                                        <!-- Barra Degradada del Logo -->
                                                        <div
                                                            style="width: 100%; background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                                                            <div
                                                                style="background: linear-gradient(90deg, #d97706, #e11d48); width: {{ $porcentaje }}%; height: 100%; border-radius: 4px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div
                                                style="font-size: 0.75em; color: #64748b; font-weight: bold; text-align: right; margin-top: 10px; font-style: italic;">
                                                🔒 Registro total auditado por Stereo 92 FM
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    <!-- ENLACES DE PAGINACIÓN INTERACTIVA DE LIVEWIRE -->
    <div style="margin-top: 20px;">
        {{ $articulosNoticias->links() }}
    </div>

    <!-- ============================================================== -->
    <!-- BOTÓN FLOTANTE DE WHATSAPP UNIFICADO DE FORMA INFALIBLE -->
    <!-- ============================================================== -->
    @php
        // 1. Extraemos los valores cargados por las locutoras desde AdminLTE
        $numWhatsapp =
            \App\Models\Admin\Configuracion::where('clave', 'whatsapp_numero')->first()?->valor ?? '51926634659';
        $msgInput =
            \App\Models\Admin\Configuracion::where('clave', 'whatsapp_mensaje_base')->first()?->valor ??
            'Hola Radio Stereo 92 FM, estoy en sintonía de "Más Radio" y quiero participar.';

        // 2. CORRECCIÓN CON BARRA DIAGONAL EN ENLACE CORTO DIRECTO (Ideal para móviles y PC)
        $urlWhatsappFinal = 'https://wa.me/' . trim($numWhatsapp) . '?text=' . urlencode($msgInput);
    @endphp

    <a href="{{ $urlWhatsappFinal }}" target="_blank" class="radio-whatsapp-float"
        title="Escríbenos a la cabina en vivo">
        <svg xmlns="http://w3.org" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"
            style="display: inline-block; vertical-align: middle;">
            <path
                d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.86 0 0 0 3.79.977h.004c4.368 0 7.926-3.559 7.93-7.93a7.9 7.9 0 0 0-2.33-5.615zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.6 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.6 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.69-3.146c-.202-.102-1.202-.593-1.385-.666-.183-.072-.317-.108-.45.092-.133.201-.517.665-.634.796-.117.13-.233.144-.435.042-.202-.102-.852-.313-1.624-.988-.6-.535-1.005-1.197-1.123-1.401-.118-.202-.012-.311.089-.413.092-.092.202-.216.302-.324.101-.109.135-.183.202-.326.067-.142.034-.267-.017-.369-.05-.102-.45-1.082-.617-1.486-.162-.395-.327-.341-.45-.347-.116-.006-.25-.007-.384-.007a.73.73 0 0 0-.529.244c-.183.202-.697.68-0.697 1.657s.71 1.931.81 2.064c.101.135 1.399 2.133 3.391 2.991.474.254.843.405 1.132.498.476.152.91.131 1.252.08.38-.057 1.202-.493 1.371-1.411.169-.92.169-1.71.119-1.813-.05-.102-.183-.164-.384-.266z" />
        </svg>
        <span style="margin-left: 8px;">Cabina en Vivo</span>
    </a>

    <!-- Estilos CSS Inyectados de forma forzada para el Botón -->
    <style>
        .radio-whatsapp-float {
            position: fixed !important;
            bottom: 25px !important;
            right: 25px !important;
            background-color: #25d366 !important;
            color: white !important;
            padding: 14px 24px !important;
            border-radius: 30px !important;
            font-weight: bold !important;
            font-size: 0.95em !important;
            text-decoration: none !important;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4) !important;
            z-index: 999999 !important;
            /* Capa superior absoluta sobre AdminLTE o cualquier grilla */
            display: inline-flex !important;
            align-items: center !important;
            transition: all 0.3s ease !important;
        }

        .radio-whatsapp-float:hover {
            background-color: #1ebd56 !important;
            transform: translateY(-3px) !important;
            color: white !important;
            text-decoration: none !important;
        }

        /* Oculto por defecto */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.6);
            /* Fondo oscuro translúcido */
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            z-index: 9999;
        }

        /* Caja contenedora interna */
        .custom-modal-box {
            position: relative;
            transform: translateY(-20px);
            transition: transform 0.25s ease;
        }

        /* Botón de cerrar (X) */
        .custom-modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 2rem;
            color: #94a3b8;
            text-decoration: none;
            line-height: 1;
        }

        .custom-modal-close:hover {
            color: #e11d48;
        }

        /* LA MAGIA: Cuando el enlace es pulsado y el ID se activa en la URL */
        .custom-modal-overlay:target {
            opacity: 1;
            pointer-events: auto;
        }

        .custom-modal-overlay:target .custom-modal-box {
            transform: translateY(0);
        }
    </style>

</section>
