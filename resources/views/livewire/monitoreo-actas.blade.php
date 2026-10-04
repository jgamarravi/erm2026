<div class="container-fluid mt-3 font-sans-serif">

    <!-- ENCABEZADO FORMAL PARA EL REPORTE PDF (SÓLO VISIBLE AL IMPRIMIR) -->
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom border-dark border-2">
        <div class="row align-items-center">
            <div class="col-3 text-start">
                <div class="fw-bold border border-dark border-3 p-2 text-center font-monospace" style="font-size: 0.85rem; letter-spacing: 1px;">
                    SISTEMA<br>ERM 2026
                </div>
            </div>
            <div class="col-6">
                <h4 class="fw-bold m-0 text-dark tracking-wide" style="font-family: 'Times New Roman', Times, serif; font-size: 1.5rem;">JURADO NACIONAL DE ELECCIONES</h4>
                <h5 class="fw-bold text-secondary text-uppercase mb-0 mt-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">REPORTE DE AVANCE Y MONITOREO DE ACTAS</h5>
            </div>
            <div class="col-3 text-end font-monospace text-muted small">
                ESTADÍSTICA DE<br>DIGITACIÓN GENERAL
            </div>
        </div>
        
        <div class="mt-3 text-start p-3 bg-light rounded border border-secondary shadow-sm font-monospace" style="font-size: 0.85rem;">
            <strong>JURISDICCIÓN ELECTORAL EVALUADA:</strong> <span class="text-uppercase fw-bold text-primary">{{ $jurisdiccion_nombre }}</span><br>
            <strong>FECHA Y HORA DE EMISIÓN DE REPORTE:</strong> {{ now()->format('d/m/2026 h:i A') }}
        </div>
    </div>

    <!-- PANEL DE SELECTORES EN CASCADA (CON EL BOTÓN PDF INTEGRADO - OCULTO EN IMPRESIÓN) -->
    <div class="card p-3 shadow-sm mb-3 border-0 bg-dark text-white d-print-none rounded-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-warning m-0">📊 PANEL DE SEGUIMIENTO Y MONITOREO DE ACTAS - ERM 2026</h6>
            <!-- BOTÓN DE IMPRESIÓN OFICIAL -->
            <button type="button" class="btn btn-light btn-sm fw-bold px-3 py-1-5 shadow-sm border border-secondary text-uppercase hover-lift" 
                    onclick="window.print();" style="font-size: 0.72rem; border-radius: 6px;">
                🖨️ Exportar Monitoreo a PDF
            </button>
        </div>
        <div class="row g-2">
            <!-- REGION -->
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1" style="font-size:0.65rem;">Región Electoral</label>
                <select class="form-select form-select-sm text-dark select-custom" wire:model.live="region_electoral_sel">
                    @foreach($departamentos as $reg_nombre)
                        <option value="{{ $reg_nombre }}">{{ $reg_nombre }}</option>
                    @endforeach
                </select>
            </div>
            <!-- PROVINCIA -->
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1" style="font-size:0.65rem;">Provincia</label>
                <select class="form-select form-select-sm select-custom text-dark" wire:model.live="provincia_id" {{ !empty($region_electoral_sel) ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Provincia --</option>
                    @foreach($provincias as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <!-- DISTRITO -->
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1" style="font-size:0.65rem;">Distrito</label>
                <select class="form-select form-select-sm select-custom text-dark" wire:model.live="distrito_id" {{ !empty($provincia_id) ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Distrito --</option>
                    @foreach($distritos as $dist)
                        <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ... (Aquí dejas tus 3 tarjetas de colores de avance y tu tabla de desglose inferiores intactas) ... --}}


    <!-- BLOQUE GLOBAL DE GRÁFICOS DE PROCESAMIENTO -->
    <div class="row g-3 mb-4">
        <!-- 1. ACTAS COMPUTADAS (ÉXITO) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-bold text-muted font-monospace"
                        style="font-size: 0.72rem; letter-spacing: 0.3px;">✅ Actas Computadas</span>
                    <span
                        class="badge bg-success font-monospace px-2 py-1">{{ number_format($pct_computadas, 2) }}%</span>
                </div>
                <div class="d-flex align-items-baseline mb-2">
                    <span class="fs-2 fw-black text-dark font-monospace">{{ number_format($computadas) }}</span>
                    <span class="text-muted ms-2 style-font-mono" style="font-size: 0.8rem;">de
                        {{ number_format($total) }} mesas</span>
                </div>
                <div class="progress shadow-inner" style="height: 10px; background-color: #f1f3f5;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                        style="width: {{ $pct_computadas }}%"></div>
                </div>
            </div>
        </div>

        <!-- 2. ACTAS OBSERVADAS (CRÍTICO) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-danger h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-bold text-muted font-monospace"
                        style="font-size: 0.72rem; letter-spacing: 0.3px;">⚠️ Actas Observadas</span>
                    <span
                        class="badge bg-danger font-monospace px-2 py-1">{{ number_format($pct_observadas, 2) }}%</span>
                </div>
                <div class="d-flex align-items-baseline mb-2">
                    <span class="fs-2 fw-black text-danger font-monospace">{{ number_format($observadas) }}</span>
                    <span class="text-muted ms-2 style-font-mono" style="font-size: 0.8rem;">de
                        {{ number_format($total) }} mesas</span>
                </div>
                <div class="progress shadow-inner" style="height: 10px; background-color: #f1f3f5;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                        style="width: {{ $pct_observadas }}%"></div>
                </div>
            </div>
        </div>

        <!-- 3. ACTAS SIN DIGITAR (PENDIENTES) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-secondary h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-bold text-muted font-monospace"
                        style="font-size: 0.72rem; letter-spacing: 0.3px;">⏳ Pendientes (Sin Digitar)</span>
                    <span
                        class="badge bg-secondary text-white font-monospace px-2 py-1">{{ number_format($pct_sin_digitar, 2) }}%</span>
                </div>
                <div class="d-flex align-items-baseline mb-2">
                    <span class="fs-2 fw-black text-dark font-monospace">{{ number_format($sin_digitar) }}</span>
                    <span class="text-muted ms-2 style-font-mono" style="font-size: 0.8rem;">de
                        {{ number_format($total) }} mesas</span>
                </div>
                <div class="progress shadow-inner" style="height: 10px; background-color: #f1f3f5;">
                    <div class="progress-bar progress-bar-striped bg-secondary opacity-75"
                        style="width: {{ $pct_sin_digitar }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- CUADRO DE AVANCE DETALLADO POR LOCAL DE VOTACIÓN -->
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-secondary text-white fw-bold font-monospace py-2-5 border-0"
            style="background-color: #495057 !important;">
            🏢 DESGLOSE DE AVANCE DE CÓMPUTO POR LOCAL DE VOTACIÓN
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table
                    class="table table-sm table-striped table-hover table-bordered align-middle m-0 border-dark custom-monitoreo-table">
                    <thead class="table-light text-uppercase font-monospace fw-bold text-center border-dark border-2"
                        style="font-size: 0.72rem;">
                        <tr>
                            <th>Distrito</th>
                            <th class="text-start ps-3">Centro / Local de Votación</th>
                            <th width="12%">Total Mesas</th>
                            <th width="12%" class="table-success">Computadas</th>
                            <th width="12%" class="table-danger">Observadas</th>
                            <th width="18%">Porcentaje Procesado</th>
                        </tr>
                    </thead>
                    <tbody class="small font-sans">
                        @forelse($locales as $loc)
                            @php
                                $procesadas = $loc->mesas_computadas + $loc->mesas_observadas;
                                $pct_local = $loc->total_mesas > 0 ? ($procesadas / $loc->total_mesas) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="text-center font-monospace fw-semibold text-secondary text-uppercase">
                                    {{ $loc->distrito_nombre }}</td>
                                <td class="fw-bold text-dark text-uppercase ps-3 py-2">{{ $loc->local_nombre }}</td>
                                <td class="text-center font-monospace fw-bold bg-white text-dark">
                                    {{ number_format($loc->total_mesas) }}</td>
                                <td
                                    class="text-center font-monospace fw-bold text-success table-success border-dark border-opacity-10">
                                    {{ number_format($loc->mesas_computadas) }}</td>
                                <td
                                    class="text-center font-monospace fw-bold text-danger table-danger border-dark border-opacity-10">
                                    {{ number_format($loc->mesas_observadas) }}</td>
                                <td class="pe-3 font-monospace">
                                    <div class="d-flex align-items-center justify-content-between mb-1"
                                        style="font-size: 0.72rem;">
                                        <span class="fw-bold text-primary">{{ number_format($pct_local, 1) }}%</span>
                                        <span
                                            class="text-muted small">({{ $procesadas }}/{{ $loc->total_mesas }})</span>
                                    </div>
                                    <div class="progress border border-light"
                                        style="height: 8px; background-color: #f1f3f5; border-radius: 2px;">
                                        <div class="progress-bar {{ $pct_local == 100 ? 'bg-success' : 'bg-primary' }}"
                                            style="width: {{ $pct_local }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4 fw-bold">No se registran locales
                                    ni mesas asignadas bajo este Ubigeo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <!-- SECCIÓN DE ENLACES: Sólo se renderiza si existen locales cargados en la vista -->
                @if ($total > 0 && method_exists($locales, 'links'))
                    <div class="d-flex justify-content-center mt-3 d-print-none">
                        {{ $locales->links() }}
                    </div>
                @endif

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

        .custom-monitoreo-table td {
            vertical-align: middle !important;
        }

        .shadow-inner {
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .style-font-mono {
            font-family: var(--bs-font-monospace);
        }

        @media print {

            /* Forzado estricto de impresión de fondos cromáticos de Bootstrap */
            .progress-bar,
            .bg-success,
            .bg-danger,
            .bg-secondary,
            .table-success,
            .table-danger {
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
                /* Desvanece los selectores y el botón */
            }

            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            th,
            td {
                border: 1px solid #000000 !important;
                padding: 6px 4px !important;
            }

            tr {
                page-break-inside: avoid !important;
                /* Evita cortes feos de hojas a la mitad de un local */
            }
        }
    </style>
</div>
