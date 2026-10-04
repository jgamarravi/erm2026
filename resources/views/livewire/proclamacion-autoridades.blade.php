<!-- resources/views/livewire/proclamacion-autoridades.blade.php -->
<div class="container-fluid mt-3">

    <!-- PANEL DE SELECTORES EN CASCADA JNE (OCULTO EN PDF) -->
    <div class="card p-3 shadow-sm mb-3 border-0 bg-dark text-white d-print-none rounded-3">
        <h6 class="fw-bold text-success mb-2">📜 PANEL DE PROCLAMACIÓN NOMINAL DE AUTORIDADES</h6>
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size: 0.65rem;">Dignidad / Cargo</label>
                <select class="form-select form-select-sm fw-bold text-dark" wire:model.live="tipo_cedula">
                    <option value="GOBERNADOR">GOBERNADOR REGIONAL</option>
                    <option value="CONSEJERO">CONSEJEROS REGIONALES ELECTOS</option>
                    <option value="PROVINCIAL">ALCALDE Y REGIDORES PROVINCIALES</option>
                    <option value="DISTRITAL">ALCALDE Y REGIDORES DISTRITALES</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size: 0.65rem;">Región Electoral</label>
                <select class="form-select form-select-sm text-dark" wire:model.live="region_electoral_sel">
                    @foreach ($departamentos as $reg_nombre)
                        <option value="{{ $reg_nombre }}">{{ $reg_nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size: 0.65rem;">Provincia</label>
                <select class="form-select form-select-sm text-dark" wire:model.live="provincia_id"
                    {{ !empty($region_electoral_sel) && $tipo_cedula !== 'GOBERNADOR' ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Provincia --</option>
                    @foreach ($provincias as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                    style="font-size: 0.65rem;">Distrito</label>
                <select class="form-select form-select-sm text-dark" wire:model.live="distrito_id"
                    {{ !empty($provincia_id) && !in_array($tipo_cedula, ['GOBERNADOR', 'CONSEJERO', 'PROVINCIAL']) ? '' : 'disabled' }}>
                    <option value="">-- Seleccione Distrito --</option>
                    @foreach ($distritos as $dist)
                        <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- MANEJO DE ALERTA DE AMBITO TERRITORIAL -->
    @if ($bloqueado)
        <div
            class="card p-5 text-center shadow-sm border-0 bg-white border-start border-4 border-warning mt-4 rounded-3">
            <span class="fs-1 d-block mb-2">⚖️</span>
            <h5 class="fw-bold text-dark text-uppercase mb-1">Falta Delimitación Geográfica</h5>
            <p class="text-muted small m-0 px-md-5">Por favor, seleccione el nivel de Provincia o Distrito en la barra
                superior para extraer la nómina de candidatos ganadores según las curules asignadas.</p>
        </div>
    @else
        <!-- CUADRO DE CREDENCIALES COMPACTO -->
        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
            <div
                class="card-header bg-success text-white py-2-5 fw-bold font-monospace d-flex justify-content-between align-items-center d-print-none">
                <span>📋 RELACIÓN DE AUTORIDADES PROCLAMADAS CON RESOLUCIÓN JNE</span>
                <button type="button" class="btn btn-light btn-sm fw-bold px-3 shadow-sm border"
                    onclick="window.print();" style="font-size:0.72rem;">
                    🖨️ Imprimir Credenciales Oficiales
                </button>
            </div>
            <div class="card-body p-3 p-print-0">
                <div class="table-responsive">
                    <table
                        class="table table-sm table-hover table-striped table-bordered align-middle m-0 border-dark custom-proclamacion-table">
                        <thead class="table-dark small text-uppercase fw-bold text-center border-dark font-monospace">
                            <tr>
                                <th width="12%">N° Documento</th>
                                <th class="text-start ps-3">Apellidos y Nombres de la Autoridad Electa</th>
                                <th class="text-start ps-3">Organización Política (Lista)</th>
                                <th width="22%">Cargo a Juramentar</th>
                            </tr>
                        </thead>
                        <tbody class="small font-sans">
                            @forelse($autoridades as $auth)
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-dark bg-white fs-6 py-2">
                                        {{ $auth->dni }}</td>
                                    <td class="fw-bold text-dark text-uppercase ps-3 py-2"
                                        style="letter-spacing: 0.3px;">{{ $auth->apellidos }}, {{ $auth->nombres }}
                                    </td>
                                    <td class="text-secondary fw-semibold text-uppercase ps-3 py-2">
                                        <div class="d-flex align-items-center">
                                            @if (!empty($auth->logo_url))
                                                <img src="{{ asset('storage/'.$auth->logo_url) }}"
                                                    alt="Logo {{ $auth->partido }}"
                                                    class="img-fluid rounded border border-dark border-opacity-10 me-2"
                                                    style="width: 20px; height: 24px; object-fit: contain;">
                                            @endif
                                            <span style="font-size: 0.8rem;">{{ $auth->partido }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span
                                            class="d-print-none badge bg-success px-3 py-1-5 shadow-sm text-uppercase font-monospace border border-dark border-opacity-10"
                                            style="font-size: 0.72rem; letter-spacing: 0.3px;">
                                            ⚖️ {{ str_replace('_', ' ', $auth->cargo) }}
                                        </span>
                                        <span
                                            class="d-none d-print-inline fw-bold text-dark text-uppercase font-monospace">
                                            {{ str_replace('_', ' ', $auth->cargo) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4 fw-bold">
                                        ❌ No se registran candidatos ganadores computados para emitir actas nominales en
                                        este Ubigeo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- SECCIÓN DE FIRMAS EXCLUSIVA PARA EL FORMATO DE IMPRESIÓN PDF -->
                <div class="d-none d-print-block mt-5 pt-5">
                    <div class="row text-center mt-5 font-monospace" style="font-size: 0.75rem;">
                        <div class="col-4">
                            <hr class="mx-4 border-dark">
                            <span class="fw-bold d-block text-uppercase">Presidente del JEE</span>
                            <small class="text-muted small">Cierre de Acta Nominal</small>
                        </div>
                        <div class="col-4">
                            <hr class="mx-4 border-dark">
                            <span class="fw-bold d-block text-uppercase">Segundo Miembro</span>
                            <small class="text-muted small">Cierre de Acta Nominal</small>
                        </div>
                        <div class="col-4">
                            <hr class="mx-4 border-dark">
                            <span class="fw-bold d-block text-uppercase">Tercer Miembro</span>
                            <small class="text-muted small">Cierre de Acta Nominal</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <style>
        .py-2-5 {
            padding-top: 0.65rem !important;
            padding-bottom: 0.65rem !important;
        }

        .py-1-5 {
            padding-top: 0.38rem !important;
            padding-bottom: 0.38rem !important;
        }

        .custom-proclamacion-table td {
            vertical-align: middle !important;
        }

        @media print {
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }

            .d-print-none {
                display: none !important;
                /* Desaparecen los selectores de arriba en el PDF */
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

            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</div>
