<div class="container-fluid pt-3">
    <!-- SECCIÓN DE NOTIFICACIONES FLASH -->
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Error de Control</h5>
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Dictamen Registrado</h5>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <!-- COLUMNA IZQUIERDA: LISTADO DE ACTAS OBSERVADAS / IMPUGNADAS -->
        <div class="col-md-5">
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>
                        Actas en Controversia</h3>
                </div>
                <div class="card-body">
                    <!-- FILTROS DE BÚSQUEDA RÁPIDA -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" wire:model.live="buscar" class="form-control form-control-sm"
                                placeholder="Buscar por número de mesa...">
                        </div>
                        <div class="col-md-6">
                            <select wire:model.live="filtro_estado" class="form-control form-control-sm">
                                <option value="todas_invalidas">-- Ver Todas (Incompletas) --</option>
                                <option value="observada">Solo Observadas</option>
                                <option value="impugnada">Solo Impugnadas</option>
                            </select>
                        </div>
                    </div>

                    <!-- TABLA DE CONTROVERSIA -->
                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover table-bordered table-sm text-sm m-0">
                            <thead class="bg-light sticky-top" style="z-index: 1;">
                                <tr class="text-center text-muted">
                                    <th>Mesa</th>
                                    <th>Jurisdicción / Local</th>
                                    <th>Incidencia</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($actas as $a)
                                    <tr
                                        class="{{ $acta_id_seleccionada == $a->id ? 'table-primary font-weight-bold' : '' }}">
                                        <td class="text-center align-middle font-weight-bold text-primary"
                                            style="font-size: 110%;">
                                            {{ $a->mesaSufragio->numero_mesa ?? 'N/A' }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="d-block text-truncate"
                                                style="max-width: 180px;"><strong>Distrito:</strong>
                                                {{ $a->mesaSufragio->centroVotacion->ubigeo->nombre ?? 'N/A' }}</span>
                                            <small class="text-muted text-xs d-block text-truncate"
                                                style="max-width: 180px;"><i class="fas fa-school mr-1"></i>
                                                {{ $a->mesaSufragio->centroVotacion->nombre ?? 'N/A' }}</small>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if ($a->estado === 'observada')
                                                <span class="badge badge-warning px-2 py-1"><i
                                                        class="fas fa-eye mr-1"></i> OBSERVADA</span>
                                            @else
                                                <span class="badge badge-danger px-2 py-1"><i
                                                        class="fas fa-gavel mr-1"></i> IMPUGNADA</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" wire:click="seleccionarActa({{ $a->id }})"
                                                class="btn btn-xs btn-outline-dark font-weight-bold shadow-sm">
                                                <i class="fas fa-search-plus mr-1"></i> Auditar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-4 text-muted">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i> <br>
                                            ¡Excelente! No se registran actas observadas ni impugnadas pendientes.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: PANEL DE AUDITORÍA Y RESOLUCIÓN EN VIVO -->
        <div class="col-md-7">
            @if ($acta_id_seleccionada && $acta_auditoria)
                <div class="card card-primary card-outline shadow"
                    wire:key="panel-auditoria-acta-{{ $acta_id_seleccionada }}">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title font-weight-bold text-primary"><i class="fas fa-microscope mr-2"></i> Mesa
                            N° {{ $acta_auditoria->mesaSufragio->numero_mesa }} - Auditoría de Votos</h3>
                        <button type="button" wire:click="cerrarAuditoria" class="close ml-auto" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <!-- Cabecera de Datos Generales -->
                        <div class="p-3 bg-light border-bottom">
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Local:</small>
                                    <strong
                                        class="text-sm">{{ $acta_auditoria->mesaSufragio->centroVotacion->nombre }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Ámbito:</small>
                                    <strong>{{ $acta_auditoria->mesaSufragio->centroVotacion->ubigeo->nombre }}</strong>
                                    <small
                                        class="text-xs text-muted d-block">({{ $acta_auditoria->mesaSufragio->centroVotacion->ubigeo->region_electoral }})</small>
                                </div>
                                <div class="col-md-4 text-right">
                                    <small class="text-muted d-block">Total Firmas en Padrón:</small>
                                    <span class="badge badge-primary p-2 text-md font-weight-bold"
                                        style="font-size: 110%;">
                                        <i class="fas fa-users mr-1"></i>
                                        {{ number_format($acta_auditoria->total_votantes_acta) }} Votantes
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- TABLA DE CONTROL DE VOTOS INGRESADOS -->
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-striped table-bordered text-center m-0 text-sm">
                                <thead class="bg-light text-muted text-xs">
                                    <tr>
                                        <th class="text-left" style="width: 40%;">Organización / Tipo</th>
                                        <th>Gob.</th>
                                        <th>Cons.</th>
                                        <th>Prov.</th>
                                        <th>Dist.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($acta_auditoria->detalles as $det)
                                        <tr>
                                            <td class="text-left align-middle font-weight-bold text-dark">
                                                {{ $det->partidoPolitico->nombre }}
                                            </td>
                                            <td>{{ $det->votos_gobernador }}</td>
                                            <td>{{ $det->votos_consejero }}</td>
                                            <td>{{ $det->votos_provincial }}</td>
                                            <td>{{ $det->votos_distrital }}</td>
                                        </tr>
                                    @endforeach
                                    <!-- Fila Blancos -->
                                    <tr class="table-warning" style="background-color: #fff9e6;">
                                        <td class="text-left font-weight-bold text-warning">VOTOS EN BLANCO</td>
                                        <td>{{ $acta_auditoria->blancos_gobernador }}</td>
                                        <td>{{ $acta_auditoria->blancos_consejero }}</td>
                                        <td>{{ $acta_auditoria->blancos_provincial }}</td>
                                        <td>{{ $acta_auditoria->blancos_distrital }}</td>
                                    </tr>
                                    <!-- Fila Nulos -->
                                    <tr class="table-danger" style="background-color: #fde8e8;">
                                        <td class="text-left font-weight-bold text-danger">VOTOS NULOS</td>
                                        <td>{{ $acta_auditoria->nulos_gobernador }}</td>
                                        <td>{{ $acta_auditoria->nulos_consejero }}</td>
                                        <td>{{ $acta_auditoria->nulos_provincial }}</td>
                                        <td>{{ $acta_auditoria->nulos_distrital }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- PANEL DE RESOLUCIÓN Y DICTAMEN -->
                        <div class="p-3 border-top bg-light">
                            <form wire:submit.prevent="resolverActa">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-danger"><i class="fas fa-balance-scale mr-1"></i>
                                                Dictamen Técnico Jurídico</label>
                                            <select wire:model="nuevo_estado" class="form-control font-weight-bold"
                                                required>
                                                <option value="observada">Mantener como OBSERVADA</option>
                                                <option value="impugnada">Mantener como IMPUGNADA</option>
                                                <option value="procesada">Subsanar Acta y Validar Cómputo (CERRAR)
                                                </option>
                                            </select>
                                            @error('nuevo_estado')
                                                <span
                                                    class="text-danger text-xs font-weight-bold">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-2">
                                            <label>Sustento de la Resolución (Resolución del JEE / Cuadre Legal)</label>
                                            <textarea wire:model="sustento_resolucion" class="form-control text-sm" rows="3"
                                                placeholder="Escriba los considerandos o el número de resolución del Ente Electoral que fundamenta este cambio..."></textarea>
                                            @error('sustento_resolucion')
                                                <span
                                                    class="text-danger text-xs font-weight-bold">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" wire:click="cerrarAuditoria"
                                        class="btn btn-default btn-sm mr-2 font-weight-bold">Cancelar</button>
                                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold shadow-sm">
                                        <i class="fas fa-gavel mr-1"></i> Aplicar Dictamen Definitivo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <!-- PANTALLA INFORMATIVA INICIAL -->
                <div class="card card-light border shadow-sm p-5 text-center text-secondary d-flex flex-column justify-content-center"
                    style="height: 100%; min-height: 400px; background-color: #f8f9fa;">
                    <div>
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="font-weight-bold text-dark">Módulo de Auditoría de Actas</h5>
                        <p class="text-muted text-sm mx-auto mb-0" style="max-width: 400px;">
                            Seleccione un acta en controversia de la lista izquierda para desplegar sus votos, verificar
                            inconsistencias de firmas y registrar la resolución oficial del JEE.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
