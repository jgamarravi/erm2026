<div class="container-fluid pt-3">
    <!-- PANEL DE SELECTORES (Se ocultará automáticamente al imprimir en papel) -->
    <div class="card card-outline card-navy shadow-sm d-print-none">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-print mr-2 text-navy"></i> Emisión de Fichas de
                Paloteo</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Región / Departamento</label>
                        <select wire:model.live="region_id" class="form-control">
                            <option value="">-- Seleccione Región --</option>
                            @foreach ($regiones as $r)
                                <option value="{{ $r->region_electoral }}">{{ $r->region_electoral }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Provincia (Ámbito Provincial)</label>
                        <select wire:model.live="provincia_id" class="form-control" {{ !$region_id ? 'disabled' : '' }}>
                            <option value="">-- Seleccione Provincia --</option>
                            @foreach ($provincias as $p)
                                <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Distrito Específico</label>
                        <select wire:model.live="distrito_id" class="form-control"
                            {{ !$provincia_id ? 'disabled' : '' }}>
                            <option value="">-- Todos los Distritos (Muestra Cabecera) --</option>
                            @foreach ($distritos as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nombre'] }}
                                    ({{ $d['es_capital'] ? 'Capital' : 'Distrito' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if ($ubigeo_seleccionado)
                <div class="d-flex justify-content-end mt-2">
                    <button type="button" onclick="window.print();" class="btn btn-navy font-weight-bold shadow-sm">
                        <i class="fas fa-print mr-1"></i> Imprimir Ficha de Campo
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- PREVISUALIZACIÓN DE LA FICHA A4 (Estilizada para Paloteo Físico) -->
    @if ($ubigeo_seleccionado)
        <div class="card shadow mt-4 p-4 bg-white mx-auto border"
            style="max-width: 8.27in; min-height: 11.69in; font-family: 'Source Sans Pro', sans-serif;">

            <!-- Encabezado Ficha -->
            <div class="row align-items-center border-bottom pb-3 mb-4">
                <div class="col-8">
                    <h3 class="font-weight-bold m-0 text-dark" style="letter-spacing: -0.5px;">FORMULARIO DE PALOTEO DE
                        INTENCIÓN DE VOTO</h3>
                    <span class="text-uppercase text-muted font-weight-bold text-xs">Elecciones Regionales y Municipales
                        2026</span>
                </div>
                <div class="col-4 text-right">
                    <div class="border p-2 rounded bg-light text-center">
                        <small class="text-muted d-block text-xs">CÓDIGO UBIGEO INEI</small>
                        <strong class="text-md text-monospace text-primary">{{ $ubigeo_seleccionado->id }}</strong>
                    </div>
                </div>
            </div>

            <!-- Datos de Control Geográfico -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <span class="text-muted text-xs d-block">REGIONAL / DEPARTAMENTO:</span>
                    <strong class="text-dark">{{ $ubigeo_seleccionado->region_electoral }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted text-xs d-block">ÁMBITO / JURISDICCIÓN:</span>
                    <strong class="text-dark">{{ $ubigeo_seleccionado->nombre }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted text-xs d-block">TIPO DE ÁMBITO:</span>
                    <span
                        class="badge {{ $es_capital ? 'badge-warning text-dark' : 'badge-secondary' }} font-weight-bold">
                        {{ $es_capital ? 'CAPITAL PROVINCIAL' : 'DISTRITO COMÚN' }}
                    </span>
                </div>
            </div>

            <!-- TABLA DE CUADRÍCULAS PARA PALOTEO -->
            <div class="table-responsive">
                <table class="table table-bordered text-center m-0" style="border: 2px solid #212529 !important;">
                    <thead>
                        <tr class="bg-dark text-white text-xs uppercase"
                            style="border-bottom: 3px solid #000 !important;">
                            <th class="text-left align-middle" style="width: 30%; font-size: 11px;">Organización
                                Política</th>
                            <th class="align-middle" style="width: 17%; font-size: 11px;">Gobernador</th>
                            <th class="align-middle" style="width: 17%; font-size: 11px;">Consejero Reg.</th>
                            <th class="align-middle" style="width: 18%; font-size: 11px;">Alcalde Provincial</th>
                            <th class="align-middle" style="width: 18%; font-size: 11px;">Alcalde Distrital</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partidosCompetidores as $pc)
                            <tr style="height: 1.1in;">
                                <!-- Columna Partido -->
                                <td class="text-left align-middle px-3" style="border-right: 2px solid #212529;">
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted text-sm font-weight-bold mr-2">{{ $pc->orden_cedula }}</span>
                                        @if ($pc->logo_url)
                                            <img src="{{ asset('storage/'.$pc->logo_url) }}" class="img-thumbnail mr-2"
                                                style="max-height: 32px; max-width: 32px; object-fit: contain;">
                                        @endif
                                        <span class="font-weight-bold text-dark text-xs text-truncate"
                                            style="max-width: 150px;">{{ $pc->nombre }}</span>
                                    </div>
                                </td>
                                <!-- Cuadrícula Gobernador -->
                                <td style="border-right: 1px solid #dee2e6; position: relative;">
                                    <small class="text-muted position-absolute"
                                        style="bottom:2px; right:4px; font-size:9px;">TOTAL: ____</small>
                                </td>
                                <!-- Cuadrícula Consejero -->
                                <td style="border-right: 2px solid #212529; position: relative;">
                                    <small class="text-muted position-absolute"
                                        style="bottom:2px; right:4px; font-size:9px;">TOTAL: ____</small>
                                </td>
                                <!-- Cuadrícula Provincial -->
                                <td style="border-right: 1px solid #dee2e6; position: relative;">
                                    <small class="text-muted position-absolute"
                                        style="bottom:2px; right:4px; font-size:9px;">TOTAL: ____</small>
                                </td>
                                <!-- Cuadrícula Distrital (Bloqueada si es Capital) -->
                                <td class="{{ $es_capital ? 'bg-striped text-muted' : '' }}"
                                    style="position: relative; background-image: {{ $es_capital ? 'repeating-linear-gradient(45deg, #f2f2f2, #f2f2f2 10px, #ffffff 10px, #ffffff 20px)' : 'none' }};">
                                    @if ($es_capital)
                                        <small
                                            class="font-weight-bold text-center text-xs d-block mt-4 text-uppercase"></small>
                                    @else
                                        <small class="text-muted position-absolute"
                                            style="bottom:2px; right:4px; font-size:9px;">TOTAL: ____</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-5 text-muted font-italic">
                                    No se registran organizaciones políticas asignadas a esta jurisdicción en el
                                    Mantenimiento de Partidos por Ubigeo.
                                </td>
                            </tr>
                        @endforelse

                        <!-- FILAS DE CONTROL CIUDADANO COMPLEMENTARIOS -->
                        <tr style="height: 0.7in;">
                            <td class="text-left align-middle px-3 font-weight-bold text-xs"
                                style="border-right: 2px solid #212529;"><i class="far fa-circle mr-2 text-warning"></i>
                                VOTOS EN BLANCO</td>
                            <td style="border-right: 1px solid #dee2e6;"></td>
                            <td style="border-right: 2px solid #212529;"></td>
                            <td style="border-right: 1px solid #dee2e6;"></td>
                            <td class="{{ $es_capital ? 'bg-light' : '' }}"
                                style="background-image: {{ $es_capital ? 'repeating-linear-gradient(45deg, #f2f2f2, #f2f2f2 10px, #ffffff 10px, #ffffff 20px)' : 'none' }};">
                            </td>
                        </tr>
                        <tr style="height: 0.7in; border-bottom: 2px solid #212529 !important;">
                            <td class="text-left align-middle px-3 font-weight-bold text-xs"
                                style="border-right: 2px solid #212529;"><i
                                    class="fas fa-times-circle mr-2 text-danger"></i> VOTOS NULOS</td>
                            <td style="border-right: 1px solid #dee2e6;"></td>
                            <td style="border-right: 2px solid #212529;"></td>
                            <td style="border-right: 1px solid #dee2e6;"></td>
                            <td class="{{ $es_capital ? 'bg-light' : '' }}"
                                style="background-image: {{ $es_capital ? 'repeating-linear-gradient(45deg, #f2f2f2, #f2f2f2 10px, #ffffff 10px, #ffffff 20px)' : 'none' }};">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- SECCIÓN DE FIRMAS Y CONTROL DE AUDITORÍA -->
            <div class="row mt-5 pt-5">
                <div class="col-4 text-center">
                    <div style="border-top: 1px solid #000; width: 85%; margin: 0 auto;"></div>
                    <small class="text-muted font-weight-bold d-block text-xs mt-1">FIRMA DEL ENCUESTADOR {{ mt_rand(5, 13) }}</small>
                    <small class="text-muted d-block" style="font-size: 10px;">DNI: __________________</small>
                </div>
                <div class="col-4 text-center">
                    <div style="border-top: 1px solid #000; width: 85%; margin: 0 auto;"></div>
                    <small class="text-muted font-weight-bold d-block text-xs mt-1">SUPERVISOR DE CAMPO</small>
                    <small class="text-muted d-block" style="font-size: 10px;">Hora Término: _________</small>
                </div>
                <div class="col-4 text-center">
                    <div class="border p-2 bg-light text-left rounded mx-auto shadow-xs" style="width: 85%;">
                        <small class="text-muted text-xs font-weight-bold d-block"><i
                                class="fas fa-clipboard-check text-secondary mr-1"></i> METRAJE DE CAMPO:</small>
                        <small class="text-muted text-xs d-block mt-1">Muestras Efectivas: _______</small>
                        <small class="text-muted text-xs d-block">Rechazos / Vacíos: ________</small>
                    </div>
                </div>
            </div>

            <!-- NOTA DE TRANSPARENCIA PIE DE PÁGINA -->
            <div class="row mt-4 pt-3 border-top">
                <div class="col-12 text-center">
                    <small class="text-muted text-monospace" style="font-size: 9px;">
                        PROYECTO ERM 2026 - SISTEMA DE CONTROL DE INTENCIÓN DE VOTO S.A. | DOCUMENTO EXCLUSIVO PARA
                        TRABAJO DE CAMPO ESTADÍSTICO.
                    </small>
                </div>
            </div>
            <!-- BOTÓN FLOTANTE EXCLUSIVO PARA IMPRESIÓN (Se oculta en el papel) -->
            <div class="d-flex justify-content-end mt-4 d-print-none">
                <button type="button" onclick="window.print();"
                    class="btn btn-success font-weight-bold shadow-sm btn-lg">
                    <i class="fas fa-print mr-2"></i> Imprimir Ficha de Paloteo A4
                </button>
            </div>
        </div>
    @else
        <!-- PANTALLA INFORMATIVA INICIAL / ESPERA DE FILTROS -->
        <div class="row mt-4 d-print-none" wire:key="espera-paloteo">
            <div class="col-12">
                <div class="card card-light border p-5 text-center text-secondary shadow-sm"
                    style="background-color: #f8f9fa;">
                    <div class="p-4">
                        <i
                            class="fas fa-print fa-4x text-muted mb-3 animate__animated animate__pulse animate__infinite"></i>
                        <h4 class="font-weight-bold text-dark">Generador Dinámico de Planillas de Paloteo</h4>
                        <p class="text-muted text-sm mx-auto mb-3" style="max-width: 500px;">
                            Seleccione una Región, Provincia y Distrito en los controles superiores. El sistema
                            estructurará las grillas con los logotipos oficiales de los partidos inscritos listos para
                            el trabajo de campo.
                        </p>
                        <div class="d-flex justify-content-center text-xs text-left mx-auto"
                            style="max-width: 400px;">
                            <div class="bg-white p-3 border rounded shadow-xs w-100">
                                <span><i class="fas fa-info-circle text-info mr-1"></i> Las capitales provinciales
                                    inhabilitan automáticamente las cuadrículas distritales para evitar duplicados en el
                                    metraje general.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <style>
        @media print {

            /* 1. Ocultar componentes de navegación de AdminLTE de la pantalla */
            .app-sidebar,
            .main-header,
            .main-footer,
            .d-print-none,
            .card-header,
            .navbar,
            nav {
                display: none !important;
            }

            /* 2. Forzar que el contenedor principal use el 100% del papel */
            .content-wrapper,
            .content,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
            }

            /* 3. Limpiar bordes, sombras y fondos grises en la hoja física */
            .card {
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
            }

            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
        }
    </style>

</div>
