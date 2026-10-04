<!-- resources/views/livewire/resultados-dashboard.blade.php -->
<div class="container-fluid mt-3 font-sans-serif">

    <!-- A. ENCABEZADO FORMAL INSTITUCIONAL JNE (SÓLO VISIBLE EN IMPRESIÓN / REPORTE PDF) -->
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom border-dark border-2">
        <div class="row align-items-center">
            <div class="col-3 text-start">
                <div class="fw-black border border-dark border-3 p-2 text-center"
                    style="font-size: 0.85rem; letter-spacing: 1px;">
                    SISTEMA<br>ERM 2026
                </div>
            </div>
            <div class="col-6">
                <h4 class="fw-black m-0 text-dark tracking-wide"
                    style="font-family: 'Times New Roman', Times, serif; font-size: 1.5rem;">JURADO NACIONAL DE
                    ELECCIONES</h4>
                <h5 class="fw-bold text-secondary text-uppercase mb-0 mt-1"
                    style="font-size: 0.8rem; letter-spacing: 0.5px;">REPORTE OFICIAL DE FISCALIZACIÓN Y PROCLAMACIÓN
                </h5>
            </div>
            <div class="col-3 text-end font-monospace text-muted small">
                ACTA CONSOLIDADA<br>CONFIDENCIAL
            </div>
        </div>

        <div class="mt-4 text-start p-3 bg-light rounded border border-secondary shadow-sm row g-2"
            style="font-size: 0.85rem;">
            <div class="col-md-6 border-end-md border-secondary">
                <span class="text-uppercase text-muted fw-bold d-block small mb-1">📍 Ámbito Territorial Evaluado</span>
                <span class="fw-black text-dark text-uppercase fs-6">{{ $jurisdiccion_nombre }}</span>
            </div>
            <div class="col-md-6 ps-md-3">
                <span class="text-uppercase text-muted fw-bold d-block small mb-1">📋 Dignidad Computada</span>
                <span class="fw-black text-dark text-uppercase fs-6">{{ str_replace('_', ' ', $tipo_cedula) }}</span>
            </div>
            <div class="col-12 mt-2 pt-2 border-top text-end text-muted font-monospace" style="font-size: 0.75rem;">
                <strong>Fecha/Hora de Certificación de Datos:</strong> {{ now()->format('d/m/2026 h:i A') }}
            </div>
        </div>
    </div>

    <!-- B. PANEL DE FILTROS SUPERIORES ELECTORALES (OCULTO EN REPORTE PDF) -->
    <div class="card p-3 shadow-sm mb-3 border-0 bg-dark text-white d-print-none rounded-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
            <div class="d-flex align-items-center">
                <div class="bg-warning text-dark px-2 py-1 rounded me-2 font-monospace fw-black small">ONPE</div>
                <h6 class="fw-bold m-0 text-warning" style="letter-spacing: 0.3px;">Consolidado Nacional de Actas de
                    Escrutinio - ERM 2026</h6>
            </div>
            <button type="button"
                class="btn btn-light btn-sm fw-bold px-3 py-1-5 shadow-sm border border-secondary text-uppercase hover-lift"
                onclick="window.print();" style="font-size: 0.72rem; border-radius: 6px;">
                🖨️ Exportar Documento PDF / Imprimir
            </button>
        </div>
        <div class="row g-2">
            <!-- TIPO DE CÉDULA -->
            <div class="col-md-3">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size: 0.65rem;">Tipo Cédula</label>
                <select class="form-select form-select-sm fw-bold text-dark select-custom"
                    wire:model.live="tipo_cedula">
                    <option value="GOBERNADOR">GOBERNADOR REGIONAL</option>
                    <option value="CONSEJERO">CONSEJERO REGIONAL (D'Hondt)</option>
                    <option value="PROVINCIAL">ALCALDÍA PROVINCIAL (D'Hondt)</option>
                    <option value="DISTRITAL">ALCALDÍA DISTRITAL (D'Hondt)</option>
                </select>
            </div>
            <!-- REGION / DEPARTAMENTO -->
            <div class="col-md-3">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size: 0.65rem;">Región / Departamento</label>
                <select class="form-select form-select-sm select-custom text-dark"
                    wire:model.live="region_electoral_sel">
                    <option value="">-- Seleccione Region --</option>
                    @foreach ($departamentos as $reg_nombre)
                        <option value="{{ $reg_nombre }}">{{ $reg_nombre }}</option>
                    @endforeach
                </select>
            </div>
            <!-- PROVINCIA -->
            <div class="col-md-3">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size: 0.65rem;">Provincia</label>
                <select class="form-select form-select-sm select-custom text-dark" wire:model.live="provincia_id"
                    {{ !empty($region_electoral_sel) && $tipo_cedula !== 'GOBERNADOR' ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Provincia --</option>
                    @foreach ($provincias as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <!-- DISTRITO -->
            <div class="col-md-3">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size: 0.65rem;">Distrito</label>
                <select class="form-select form-select-sm select-custom text-dark" wire:model.live="distrito_id"
                    {{ !empty($provincia_id) && !in_array($tipo_cedula, ['GOBERNADOR', 'CONSEJERO', 'PROVINCIAL']) ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Distrito --</option>
                    @foreach ($distritos as $dist)
                        <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <!-- C. GESTIÓN DE BLOQUEOS Y MUESTRAS CONDICIONALES BLADE -->
    @if ($mostrar_alerta_seleccion)
        <div
            class="card p-5 text-center shadow-sm border-0 bg-white rounded-3 mt-4 border-start border-4 border-warning">
            <span class="fs-1 mb-2 d-block">⚖️</span>
            <h5 class="text-dark fw-black text-uppercase mb-2" style="letter-spacing: 0.3px;">Restricción de Demarcación
                Territorial</h5>
            <p class="text-muted m-0 small px-md-5 lh-base">
                Para auditar elecciones de escala municipal de tipo
                <strong>[{{ str_replace('_', ' ', $tipo_cedula) }}]</strong>, la JNE exige segmentar los datos de forma
                explícita. Seleccione la
                <strong>{{ $tipo_cedula === 'DISTRITAL' ? 'Distrito específico' : 'Provincia específica' }}</strong>
                para ejecutar el procesamiento.
            </p>
        </div>
    @else
        <!-- DISYUNTIVA DE RENDERIZADO VISUAL CONTRATADO -->
        @if ($es_macro_consejeros)

            <!-- VISTA A: CONSOLIDADO COMPLETO DE TODAS LAS PROVINCIAS EN TARJETAS PARALELAS -->
            <div class="row g-3">
                @foreach ($consejeros_regionales_lista as $bloque)
                    <div class="col-md-6 col-print-12">
                        <div
                            class="card shadow-sm border border-secondary border-opacity-20 rounded-3 h-100 overflow-hidden">
                            <div
                                class="card-header bg-dark text-white py-2 fw-bold font-monospace d-flex justify-content-between align-items-center border-0">
                                <span class="text-warning">📍 PROVINCIA: {{ $bloque['provincia_nombre'] }}</span>
                                <span class="badge bg-primary">Vacantes: {{ $bloque['vacantes'] }}</span>
                            </div>
                            <div class="card-body p-3 bg-white">
                                @forelse($bloque['resultados'] as $p_voto)
                                    @php
                                        $curules = $bloque['escaños'][$p_voto->partido] ?? 0;
                                    @endphp
                                    <div class="mb-3 bar-print-container">
                                        <div
                                            class="d-flex justify-content-between small font-monospace mb-1 align-items-center">
                                            <span class="fw-bold text-dark text-uppercase text-truncate"
                                                style="max-width: 55%;">{{ $p_voto->partido }}</span>
                                            <div>
                                                <span class="badge bg-dark text-warning font-monospace px-1-5"
                                                    style="font-size: 0.68rem;">{{ number_format($p_voto->porcentaje_provincial, 2) }}%</span>
                                                <span class="badge bg-light text-muted border font-monospace px-1-5"
                                                    style="font-size: 0.68rem;">{{ number_format($p_voto->total_votos) }}
                                                    Votos</span>
                                                <span class="badge bg-primary print-color-adjust ms-1"
                                                    style="background-color: #0d6efd !important; font-size: 0.72rem;">
                                                    🎖️ {{ $curules }} Escaños
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress border border-light shadow-sm"
                                            style="height: 14px; background-color: #f1f3f5 !important; border-radius: 4px;">
                                            <div class="progress-bar {{ $curules > 0 ? 'bg-success' : 'bg-secondary opacity-50' }} print-color-adjust"
                                                style="width: {{ $p_voto->porcentaje_provincial }}%; background-color: {{ $curules > 0 ? '#198754' : '#6c757d' }} !important;">
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4 small font-monospace">❌ Sin actas de
                                        consejeros computadas en esta provincia.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- VISTA B: CUADRO REGULAR INDIVIDUAL DE AUDITORÍA PERIMETRAL -->
            <div class="row g-3">
                <!-- D. CUADRO IZQUIERDO: DETALLE DE ESCRUTINIO -->
                <div class="col-lg-7 col-print-12">
                    <div class="card shadow-sm border-0 border-print-0 h-100 rounded-3 overflow-hidden">
                        <div class="card-header bg-secondary text-white py-2-5 fw-bold font-monospace d-print-none d-flex justify-content-between align-items-center border-0"
                            style="background-color: #5a6268 !important;">
                            <div class="d-flex align-items-center">
                                <span class="me-2">📋</span>
                                <span>CUADRO GENERAL DE MÉRITO ELECTORAL</span>
                            </div>
                            @if (in_array($tipo_cedula, ['CONSEJERO', 'PROVINCIAL', 'DISTRITAL']) && $escaños_disponibles > 0)
                                <span
                                    class="badge bg-warning text-dark fw-bold px-2 py-1 shadow-sm font-monospace border border-dark border-opacity-10"
                                    style="font-size: 0.72rem;">Vacantes por BD: {{ $escaños_disponibles }}</span>
                            @endif
                        </div>
                        <div class="card-body p-3 p-print-0">
                            <table
                                class="table table-sm table-hover table-bordered align-middle m-0 border-dark custom-table">
                                <thead
                                    class="table-light text-uppercase small font-monospace fw-bold text-center border-dark border-2">
                                    <tr class="bg-light text-secondary">
                                        <th width="8%" class="py-2">Pos.</th>
                                        <th class="text-start ps-3 py-2">Organización Política (Lista Autorizada)</th>
                                        <th width="24%" class="py-2">Votos Válidos</th>
                                        <th width="20%" class="py-2">Support %</th>
                                    </tr>
                                </thead>
                                <tbody class="small font-sans">
                                    @php $pos = 1; @endphp
                                    @forelse($partidos as $p)
                                        <tr class="{{ $pos === 1 ? 'table-success table-success-custom' : '' }}">
                                            <td class="text-center font-monospace fw-bold text-muted py-2">
                                                {{ $pos === 1 ? '🥇' : '#' . $pos }}
                                            </td>

                                            <!-- CELDA DE PARTIDO RE-DISEÑADA CON LOGOTIPO INSTITUCIONAL -->
                                            <td class="fw-bold text-dark text-uppercase py-2 ps-3">
                                                <div class="d-flex align-items-center">
                                                    @if (!empty($p->logo_url))
                                                        <img src="{{ asset('storage/'.$p->logo_url) }}"
                                                            alt="Logo {{ $p->partido }}"
                                                            class="img-fluid rounded border border-secondary border-opacity-20 me-2 print-color-adjust"
                                                            style="width: 24px; height: 24px; object-fit: contain;">
                                                    @else
                                                        <div class="bg-light border text-center text-muted rounded me-2 font-monospace fw-bold"
                                                            style="width: 24px; height: 24px; font-size: 0.65rem; line-height: 22px;">
                                                            S/L
                                                        </div>
                                                    @endif
                                                    <span>{{ $p->partido }}</span>
                                                </div>
                                            </td>

                                            <td class="text-end font-monospace fw-bold bg-white text-dark pe-3 py-2">
                                                {{ number_format($p->total_votos) }}
                                            </td>
                                            <td class="text-end font-monospace text-primary fw-bold pe-3 py-2"
                                                style="font-size: 0.95rem;">
                                                {{ $total_validos > 0 ? number_format(($p->total_votos / $total_validos) * 100, 2) : 0.00 }}%
                                            </td>
                                        </tr>
                                        @php $pos++; @endphp

                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4 fw-bold">No se
                                                registran datos o actas digitadas bajo la demarcación seleccionada.</td>
                                        </tr>
                                    @endforelse

                                    <tr
                                        class="table-light font-monospace text-muted fw-bold text-end border-top border-secondary border-2 align-middle">
                                        <td colspan="2" class="py-2 pe-3"
                                            style="font-size: 0.75rem; letter-spacing: 0.3px;">TOTAL VOTOS VÁLIDOS
                                            CONSOLIDADOS:</td>
                                        <td
                                            class="text-dark fw-black pe-3 py-2 font-monospace fs-6 bg-white border-dark">
                                            {{ number_format($total_validos) }}</td>
                                        <td
                                            class="text-dark fw-black pe-3 py-2 font-monospace fs-6 bg-white border-dark">
                                            100.00%</td>
                                    </tr>
                                    <tr class="font-monospace text-success bg-white fw-bold align-middle">
                                        <td colspan="2"
                                            class="ps-3 py-2 text-uppercase font-sans text-muted fw-bold"
                                            style="font-size: 0.75rem;">⬜ VOTOS EN BLANCO</td>
                                        <td class="text-end pe-3 py-2 bg-light text-secondary font-monospace fw-bold">
                                            {{ number_format($especiales->blancos ?? 0) }}</td>
                                        <td class="text-end pe-3 py-2 font-monospace small text-secondary fw-normal">
                                            {{ $total_emitidos > 0 ? number_format((($especiales->blancos ?? 0) / $total_emitidos) * 100, 2) : 0.00 }}%
                                            <sub class="d-print-none opacity-50">Emitidos</sub>
                                        </td>
                                    </tr>
                                    <tr class="font-monospace text-danger bg-white fw-bold align-middle">
                                        <td colspan="2"
                                            class="ps-3 py-2 text-uppercase font-sans text-muted fw-bold"
                                            style="font-size: 0.75rem;">💥 VOTOS NULOS / VICIADOS</td>
                                        <td class="text-end pe-3 py-2 bg-light text-secondary font-monospace fw-bold">
                                            {{ number_format($especiales->nulos ?? 0) }}</td>
                                        <td class="text-end pe-3 py-2 font-monospace small text-secondary fw-normal">
                                            {{ $total_emitidos > 0 ? number_format((($especiales->nulos ?? 0) / $total_emitidos) * 100, 2) : 0.00 }}%
                                            <sub class="d-print-none opacity-50">Emitidos</sub>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- E. CUADRO DERECHO: COMPONENTES GRÁFICOS Y PROCLAMACIONES INDIVIDUALES -->
                <div class="col-lg-5 col-print-12 mt-print-4">
                    <div class="card shadow-sm border-0 border-print-0 h-100 rounded-3 overflow-hidden">
                        @if ($tipo_cedula === "GOBERNADOR")
                            <div class="card-header bg-success text-white py-2-5 fw-bold font-monospace border-0"
                                style="background-color: #198754 !important;">
                                🏆 GANADOR DE FÓRMULA REGIONAL UNIPERSONAL
                            </div>
                            <div
                                class="card-body p-4 bg-white text-center d-flex flex-column justify-content-center border border-print-0 rounded-bottom">
                                @if ($partidos->isNotEmpty())
                                    @php
                                        $ganador = $partidos->first();
                                        $porcentaje_valido =
                                            $total_validos > 0 ? ($ganador->total_votos / $total_validos) * 100 : 0;
                                    @endphp
                                    <span class="fs-1 d-block mb-1">🥇</span>
                                    <h5 class="fw-black text-dark text-uppercase m-0 mb-3 border-bottom pb-2"
                                        style="letter-spacing: 0.5px;">{{ $ganador->partido }}</h5>
                                    <div
                                        class="bg-light p-3 rounded-3 border border-success border-opacity-20 mb-3 font-monospace shadow-inner">
                                        <span class="text-muted small text-uppercase d-block fw-bold mb-1"
                                            style="font-size:0.68rem;">Votos Computados Consolidados</span>
                                        <span
                                            class="fs-2 fw-black text-success">{{ number_format($ganador->total_votos) }}</span>
                                    </div>
                                    @if ($porcentaje_valido >= 30.0)
                                        <div
                                            class="alert alert-success py-2-5 font-monospace small m-0 fw-bold border-2 border-success text-success bg-white shadow-sm print-color-adjust rounded-3">
                                            🎉 PROCLAMADO ELECTO EN PRIMERA VUELTA<br>
                                            <span class="fs-7 fw-normal text-muted d-block mt-1">Superó el umbral del
                                                30.00% ({{ number_format($porcentaje_valido, 2) }}%)</span>
                                        </div>
                                    @else
                                        <div
                                            class="alert alert-warning py-2-5 font-monospace small m-0 fw-bold border-2 border-warning text-warning bg-white shadow-sm print-color-adjust rounded-3">
                                            🔄 REGLA DE BALOTAJE / SEGUNDA VUELTA<br>
                                            <span class="fs-7 fw-normal text-muted d-block mt-1">No alcanzó el 30.00%
                                                ({{ number_format($porcentaje_valido, 2) }}%)</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @else
                            <div class="card-header bg-primary text-white py-2-5 fw-bold font-monospace text-uppercase border-0"
                                style="background-color: #0d6efd !important;">
                                📊 DISTRIBUCIÓN DE VOTOS Y REPRESENTANTES (D'HONDT)
                            </div>
                            <div
                                class="card-body p-3 bg-white d-flex flex-column justify-content-center border border-print-0 rounded-bottom">
                                @if ($total_validos > 0)
                                    <div class="row g-3">
                                        @foreach ($partidos as $index => $p)
                                            @php
                                                $porcentaje_barra = ($p->total_votos / $total_validos) * 100;
                                                $curules = $escaños[$p->partido] ?? 0;
                                            @endphp
                                            <div class="col-12 bar-print-container">
                                                <div
                                                    class="d-flex justify-content-between font-monospace small mb-1 align-items-center">
                                                    <span class="fw-bold text-truncate text-uppercase text-secondary"
                                                        style="max-width: 58%; font-size: 0.8rem;">{{ $p->partido }}</span>
                                                    <div class="text-end">
                                                        <span
                                                            class="badge bg-dark font-monospace text-warning py-1 px-1-5 me-1"
                                                            style="font-size: 0.7rem;">{{ number_format($porcentaje_barra, 2) }}%</span>
                                                        <span
                                                            class="badge bg-primary fs-7 px-2 py-1-5 fw-bold text-uppercase border border-dark border-opacity-10 print-color-adjust"
                                                            style="background-color: #0d6efd !important; font-size: 0.72rem;">
                                                            🎖️ {{ $curules }} Regidores / Consejeros
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="progress border border-light shadow-sm"
                                                    style="height: 16px; background-color: #f1f3f5 !important; border-radius: 4px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $index === 0 ? 'bg-success' : 'bg-info' }} print-color-adjust"
                                                        role="progressbar"
                                                        style="width: {{ $porcentaje_barra }}%; background-color: {{ $index === 0 ? '#198754' : '#0dcaf0' }} !important;">
                                                    </div>
                                                </div>
                                                <div class="font-monospace text-muted d-block text-end mt-1"
                                                    style="font-size: 0.68rem;">{{ number_format($p->total_votos) }}
                                                    Votos Válidos Obtenidos
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    @endif

    <!-- F. SECCIÓN DE FIRMAS PARA EL REPORTE PDF IMPRESO -->
    <div class="d-none d-print-block mt-5 pt-5">
        <div class="row text-center mt-5 font-monospace" style="font-size: 0.78rem;">
            <div class="col-4">
                <hr class="mx-4 border-dark border-1">
                <span class="fw-black d-block text-uppercase text-dark">Presidente del J.E.E.</span>
                <small class="text-muted-50">Certificación y Fe Electoral</small>
            </div>
            <div class="col-4">
                <hr class="mx-4 border-dark border-1">
                <span class="fw-black d-block text-uppercase text-dark">Segundo Miembro</span>
                <small class="text-muted-50">Certificación y Fe Electoral</small>
            </div>
            <div class="col-4">
                <hr class="mx-4 border-dark border-1">
                <span class="fw-black d-block text-uppercase text-dark">Tercer Miembro</span>
                <small class="text-muted-50">Certificación y Fe Electoral</small>
            </div>
        </div>
    </div>


    <style>
        .fw-black {
            font-weight: 900 !important;
        }

        .select-custom {
            border-radius: 6px !important;
            border: 1px solid #ced4da !important;
            font-weight: 500;
        }

        .py-2-5 {
            padding-top: 0.65rem !important;
            padding-bottom: 0.65rem !important;
        }

        .px-1-5 {
            padding-left: 0.45rem !important;
            padding-right: 0.45rem !important;
        }

        .py-1-5 {
            padding-top: 0.38rem !important;
            padding-bottom: 0.38rem !important;
        }

        .table-success-custom {
            background-color: #eafaf1 !important;
        }

        .custom-table th {
            font-size: 0.72rem !important;
            letter-spacing: 0.3px;
        }

        .custom-table td {
            vertical-align: middle !important;
        }

        .hover-lift {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .hover-lift:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08) !important;
        }

        @media print {
            .print-color-adjust {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }

            .d-print-none {
                display: none !important;
            }

            .border-print-0 {
                border: none !important;
                box-shadow: none !important;
            }

            .p-print-0 {
                padding: 0 !important;
            }

            .col-print-12 {
                width: 100% !important;
                float: none !important;
                display: block !important;
            }

            .mt-print-4 {
                margin-top: 2rem !important;
            }

            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            th,
            td {
                border: 1px solid #000000 !important;
                padding: 6px 4px !important;
                color: #000000 !important;
            }

            .progress {
                border: 1px solid #000000 !important;
                background-color: #f8f9fa !important;
            }

            .progress-bar {
                background-color: #198754 !important;
            }

            .bar-print-container,
            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</div>
