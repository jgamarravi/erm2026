<div class="container-fluid mt-3">

    <!-- BLOQUE OCULTO EN PANTALLA: SOLO COMPONE LA CABECERA OFICIAL AL IMPRIMIR -->
    <div class="d-none d-print-block text-center mb-4 border-bottom border-3 pb-3">
        <h4 class="fw-black m-0 tracking-wide text-uppercase">JURADO NACIONAL DE ELECCIONES</h4>
        <h5 class="fw-bold text-secondary text-uppercase small">JURADO ELECTORAL ESPECIAL DE PROCLAMACIÓN</h5>
        <div class="mt-3 small text-start p-3 bg-light rounded border">
            <strong>ACTA DE PROCLAMACIÓN DE AUTORIDADES OFICIALES ELECTAS - ERM 2026</strong><br>
            <span class="text-muted">Circunscripción Territorial:</span> <span
                class="fw-bold text-dark text-uppercase">{{ $jurisdiccion }}</span><br>
            <span class="text-muted">Dignidad Evaluada:</span> <span
                class="fw-bold text-dark text-uppercase">{{ str_replace('_', ' ', $tipo_cedula) }} Y SU CUERPO DE
                REGIDORES/CONSEJEROS</span>
        </div>
    </div>

    <!-- PANEL DE FILTRADO (OCULTO EN IMPRESIÓN) -->
    <div class="card p-3 shadow-sm mb-3 border-0 bg-dark text-white d-print-none">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-success m-0">📜 PROCLAMACIÓN OFICIAL DE AUTORIDADES ELECTAS</h6>
            <!-- BOTÓN QUE DISPARA LA IMPRESIÓN NATIVA -->
            <button type="button" class="btn btn-light btn-sm fw-bold px-3 shadow-sm" onclick="window.print();">
                🖨️ Imprimir Acta Formal JNE
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Dignidad / Cargo</label>
                <select class="form-select form-select-sm" wire:model.live="tipo_cedula">
                    <option value="GOBERNADOR">GOBERNADOR REGIONAL</option>
                    <option value="CONSEJERO">CONSEJEROS REGIONALES</option>
                    <option value="PROVINCIAL">ALCALDÍA PROVINCIAL (Alcalde + Regidores)</option>
                    <option value="DISTRITAL">ALCALDÍA DISTRITAL (Alcalde + Regidores)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Región Electoral</label>
                <select class="form-select form-select-sm" wire:model.live="region_electoral_sel">
                    @foreach ($departamentos as $reg_nombre)
                        <option value="{{ $reg_nombre }}">{{ $reg_nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Provincia</label>
                <select class="form-select form-select-sm" wire:model.live="provincia_id"
                    {{ !empty($region_electoral_sel) && $tipo_cedula !== 'GOBERNADOR' ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Provincia --</option>
                    @foreach ($provincias as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Distrito</label>
                <select class="form-select form-select-sm" wire:model.live="distrito_id"
                    {{ !empty($provincia_id) && !in_array($tipo_cedula, ['GOBERNADOR', 'CONSEJERO', 'PROVINCIAL']) ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Distrito --</option>
                    @foreach ($distritos as $dist)
                        <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- TABLA CENTRAL DE AUTORIDADES PROCLAMADAS -->
    <div class="card shadow-sm border-0 border-print-0">
        <div class="card-header bg-success text-white py-2 fw-bold font-monospace d-print-none">
            📋 RELACIÓN DE AUTORIDADES ELECTAS CON ASIGNACIÓN DE CURUL
        </div>
        <div class="card-body p-3 p-print-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered align-middle m-0 border-dark">
                    <thead class="table-light small text-uppercase fw-bold text-center border-dark">
                        <tr>
                            <th width="12%">DNI</th>
                            <th>Nombres y Apellidos Completos</th>
                            <th>Organización Política (Lista)</th>
                            <th width="25%">Cargo Electo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($autoridades as $auth)
                            <tr>
                                <td class="text-center font-monospace fw-bold bg-white text-dark">{{ $auth->dni }}
                                </td>
                                <td class="fw-bold text-dark text-uppercase py-2">{{ $auth->nombres }}
                                    {{ $auth->apellidos }}</td>
                                <td class="text-secondary fw-bold text-uppercase">{{ $auth->partido }}</td>
                                <td class="text-center fw-bold text-uppercase small">
                                    <span class="d-print-none badge bg-success px-3 py-1 shadow-sm">
                                        ⚖️ {{ str_replace('_', ' ', $auth->cargo) }}
                                    </span>
                                    <span class="d-none d-print-inline">
                                        {{ str_replace('_', ' ', $auth->cargo) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4 font-monospace">
                                    No se registran resultados computados o candidatos inscritos para emitir actas de
                                    proclamación en esta circunscripción.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- BLOQUE DE FIRMAS: SOLO VISIBLE EN FORMATO DE IMPRESIÓN IMPRESO -->
            <div class="d-none d-print-block mt-5 pt-5">
                <div class="row text-center mt-5">
                    <div class="col-4">
                        <hr class="mx-4 border-dark">
                        <small class="fw-bold text-uppercase d-block">Presidente</small>
                        <small class="text-muted small">JEE Proclamación</small>
                    </div>
                    <div class="col-4">
                        <hr class="mx-4 border-dark">
                        <small class="fw-bold text-uppercase d-block">Segundo Miembro</small>
                        <small class="text-muted small">JEE Proclamación</small>
                    </div>
                    <div class="col-4">
                        <hr class="mx-4 border-dark">
                        <small class="fw-bold text-uppercase d-block">Tercer Miembro</small>
                        <small class="text-muted small">JEE Proclamación</small>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- ESTILOS CSS INYECTADOS EXCLUSIVOS PARA IMPRESIÓN (PRINT PRINT) -->
    <style>
        @media print {
            body {
                background-color: #fff !important;
                color: #000 !important;
                font-family: 'Times New Roman', Times, serif;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 6px !important;
            }

            /* Forzar salto de página limpio si la lista de regidores es muy extensa */
            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</div>
