<!-- resources/views/livewire/ficha-paloteo.blade.php -->
<div class="container-fluid mt-3">

    <!-- BARRA DE SELECCIÓN DE ENTORNO (OCULTA AL PREPARAR EL REPORTE PDF) -->
    <div class="card p-3 shadow-sm mb-3 border-0 bg-dark text-white d-print-none rounded-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-info m-0">📋 CONFIGURADOR DE FICHAS DE INTENCIÓN DE VOTO (PALOTEO)</h6>
            <button type="button" class="btn btn-light btn-sm fw-bold px-3 shadow-sm border text-uppercase"
                onclick="window.print();" {{ $ubigeo_seleccionado ? '' : 'disabled' }} style="font-size:0.72rem;">
                🖨️ Imprimir Formato de Encuesta (PDF)
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size:0.65rem;">Región Electoral</label>
                <select class="form-select form-select-sm text-dark" wire:model.live="region_electoral_sel">
                    @foreach ($departamentos as $reg_nombre)
                        <option value="{{ $reg_nombre }}">{{ $reg_nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size:0.65rem;">Provincia</label>
                <select class="form-select form-select-sm text-dark" wire:model.live="provincia_id"
                    {{ !empty($region_electoral_sel) ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Provincia --</option>
                    @foreach ($provincias as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size:0.65rem;">Distrito</label>
                <select class="form-select form-select-sm text-dark" wire:model.live="distrito_id"
                    {{ !empty($provincia_id) ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Distrito --</option>
                    @foreach ($distritos as $dist)
                        <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- CONTENEDOR GENERAL DE LA HOJA ELECTORAL -->
    @if (!$ubigeo_seleccionado)
        <div class="card p-5 text-center shadow-sm border-0 bg-white rounded-3 d-print-none">
            <span class="fs-1 d-block mb-2">📝</span>
            <h5 class="text-secondary fw-bold text-uppercase">Ámbito en Espera</h5>
            <p class="text-muted small m-0">Seleccione la jurisdicción geográfica en los combos de arriba para generar e
                imprimir la plantilla física de conteo de votos de calle.</p>
        </div>
    @else
        <div
            class="card shadow-sm border border-secondary rounded-3 overflow-hidden page-sheet shadow-none border-print-dark">

            <!-- ENCABEZADO INSTITUTIONAL DE LA FICHA -->
            <div class="card-header bg-light border-bottom border-secondary py-3 text-center bg-white border-print-2">
                <h4 class="fw-black m-0 text-dark tracking-wide font-monospace" style="font-size: 1.35rem;">CUADERNO DE
                    INTENCIÓN DE VOTO DE CAMPO</h4>
                <h5 class="fw-bold text-secondary text-uppercase small m-0 mt-1"
                    style="font-size:0.75rem; letter-spacing:0.5px;">Estudio Estadístico de Tendencias de Muestreo
                    Electoral</h5>

                <div class="mt-3 text-start bg-light p-2-5 rounded border row g-2 font-monospace border-dark"
                    style="font-size: 0.82rem;">
                    <div class="col-md-8 border-end-md border-secondary">
                        <strong>UBICACIÓN GEOGRÁFICA DE LA ENCUESTA:</strong> <span
                            class="text-uppercase fw-bold text-dark d-block mt-1">{{ $nombre_completo_ubigeo }}</span>
                    </div>
                    <div class="col-md-4 ps-md-3">
                        <strong>UBIGEO ID:</strong> <span
                            class="fw-bold text-primary d-block mt-1">{{ $ubigeo_seleccionado }}</span>
                    </div>
                </div>
            </div>

            <!-- GRILLA MATRIZ TIPO PALOTEO -->
            <div class="card-body p-3 p-print-0">
                <table class="table table-bordered align-middle m-0 table-paloteo border-dark">
                    <thead
                        class="table-light text-center small text-uppercase font-monospace fw-bold border-dark border-2">
                        <tr class="align-middle">
                            <th rowspan="2" class="text-start ps-3 py-3 text-dark bg-white">Organización Política /
                                Opción</th>
                            <th colspan="2" class="bg-primary text-white py-2" width="34%">CÉDULA REGIONAL</th>
                            <th colspan="2" class="bg-info text-dark py-2" width="34%">CÉDULA MUNICIPAL</th>
                        </tr>
                        <tr class="font-sans fw-bold text-secondary" style="font-size: 0.72rem;">
                            <th width="17%">Gobernador</th>
                            <th width="17%">Consejero</th>
                            <th width="17%">Provincial</th>
                            <th width="17%">Distrital</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Bucle Dinámico de Partidos Políticos Autorizados --}}
                        @foreach ($partidos_maestros as $partido)
                            <tr style="height: 65px;">
                                <td class="fw-bold text-dark text-uppercase ps-3 text-truncate"
                                    style="max-width: 250px; font-size: 0.8rem;">
                                    {{ $partido->nombre }}
                                </td>

                                <!-- GOBERNADOR -->
                                <td
                                    class="text-center font-monospace {{ $col_g_activa ? 'bg-white' : 'bg-lock-cell text-muted small print-bg-lock' }}">
                                    @if (!$col_g_activa)
                                        No votos
                                    @endif
                                </td>

                                <!-- CONSEJERO -->
                                <td
                                    class="text-center font-monospace {{ $col_c_activa ? 'bg-white' : 'bg-lock-cell text-muted small print-bg-lock' }}">
                                    @if (!$col_c_activa)
                                        No votos
                                    @endif
                                </td>

                                <!-- PROVINCIAL -->
                                <td
                                    class="text-center font-monospace {{ $col_p_activa ? 'bg-white' : 'bg-lock-cell text-muted small print-bg-lock' }}">
                                    @if (!$col_p_activa)
                                        No votos
                                    @endif
                                </td>

                                <!-- DISTRITAL (AUTOMÁTICAMENTE SOMBREA SI ES UBIGEO CAPITAL) -->
                                <td
                                    class="text-center font-monospace {{ $col_d_activa ? 'bg-white' : 'bg-lock-cell text-muted small print-bg-lock' }}">
                                    @if (!$col_d_activa)
                                        No votos
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        {{-- Opciones de Votos de Control Estándar --}}
                        <tr style="height: 65px;" class="fw-bold">
                            <td class="ps-3 text-success text-uppercase" style="font-size: 0.8rem;">⬜ VOTOS EN BLANCO
                            </td>
                            <td class="{{ $col_g_activa ? 'bg-white' : 'bg-lock-cell print-bg-lock' }}"></td>
                            <td class="{{ $col_c_activa ? 'bg-white' : 'bg-lock-cell print-bg-lock' }}"></td>
                            <td class="{{ $col_p_activa ? 'bg-white' : 'bg-lock-cell print-bg-lock' }}"></td>
                            <td class="{{ $col_d_activa ? 'bg-white' : 'bg-lock-cell print-bg-lock' }}"></td>
                        </tr>
                        <tr style="height: 65px;" class="fw-bold">
                            <td class="ps-3 text-danger text-uppercase" style="font-size: 0.8rem;">💥 VOTOS NULOS /
                                VICIADOS</td>
                            <td class="{{ $col_g_activa ? 'bg-white' : 'bg-lock-cell print-bg-lock' }}"></td>
                            <td class="{{ $col_c_activa ? 'bg-white' : 'bg-lock-cell print-bg-lock' }}"></td>
                            <td class="{{ $col_p_activa ? 'bg-white' : 'bg-lock-cell print-bg-lock' }}"></td>
                            <td class="{{ $col_d_activa ? 'bg-white' : 'bg-lock-cell print-bg-lock' }}"></td>
                        </tr>
                    </tbody>
                </table>

                <!-- CUADRO DE METRICAS DEL ENCUESTADOR AL PIE DE PÁGINA -->
                <div class="mt-4 p-3 bg-light rounded border border-dark font-monospace row g-2 small text-uppercase"
                    style="font-size: 0.78rem;">
                    <div class="col-sm-4"><strong>({{ $frecuencia }}) Nombre Encuestador:</strong> ______________________</div>
                    <div class="col-sm-4"><strong>Firma Operario:</strong> ______________________</div>
                    <div class="col-sm-4 text-end"><strong>Total Fichas del Lote:</strong> [______]</div>
                </div>
            </div>
        </div>
    @endif
    <style>
        .bg-lock-cell {
            background-color: #f1f3f5 !important;
            color: #adb5bd !important;
            font-style: italic;
            font-weight: normal;
        }

        .py-2-5 {
            padding-top: 0.65rem !important;
            padding-bottom: 0.65rem !important;
        }

        .border-print-dark {
            border: 1px solid #000 !important;
        }

        @media print {

            /* Forzado estricto para inyectar la sombra gris tenue en las columnas bloqueadas al imprimir a papel */
            .print-bg-lock {
                background-color: #e9ecef !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color: #6c757d !important;
            }

            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }

            .d-print-none {
                display: none !important;
                // Desvanece los selectores en cascada superiores
            }

            .border-print-2 {
                border-bottom: 2px solid #000000 !important;
            }

            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            th,
            td {
                border: 1px solid #000000 !important;
                padding: 8px 4px !important;
            }

            tr {
                page-break-inside: avoid !important;
                /* Candado  anti-ruptura de hojas */
            }
        }
    </style>
</div>
