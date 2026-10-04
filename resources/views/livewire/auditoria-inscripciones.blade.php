<div class="container-fluid mt-3">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold">🔍 Padrón de Cobertura y Consistencia de Cédulas por Partido</h6>
            @if ($partido_nombre)
                <span class="badge bg-warning text-dark fw-bold font-monospace text-uppercase">Auditando:
                    {{ $partido_nombre }}</span>
            @endif
        </div>
        <div class="card-body p-3">
            @if (session()->has('message_delete'))
                <div class="alert alert-danger fw-bold py-2 mb-3">🗑️ {{ session('message_delete') }}</div>
            @endif

            <!-- PANEL DE FILTROS SUPERIORES -->
            <div class="row g-2 mb-3 bg-light p-3 rounded border">
                <!-- NEW: SELECTOR DE ORGANIZACIÓN POLÍTICA -->
                <div class="col-md-4">
                    <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                        style="font-size:0.65rem;">1. Seleccione Partido Político</label>
                    <select class="form-select form-select-sm fw-bold text-primary" wire:model.live="partido_id_sel">
                        <option value="">-- Seleccione Organización --</option>
                        @foreach ($partidos_maestros as $pm)
                            <option value="{{ $pm->id }}">{{ $pm->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- SELECTOR DE NIVEL DE CÉDULA -->
                <div class="col-md-4">
                    <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                        style="font-size:0.65rem;">2. Tipo de Elección / Cédula a Auditar</label>
                    <select class="form-select form-select-sm fw-bold text-dark" wire:model.live="filtro_tipo">
                        <option value="GOBERNADOR">GOBERNADOR REGIONAL</option>
                        <option value="CONSEJERO">CONSEJERO REGIONAL</option>
                        <option value="PROVINCIAL">ALCALDÍA PROVINCIAL</option>
                        <option value="DISTRITAL">ALCALDÍA DISTRITAL</option>
                    </select>
                </div>
                <!-- TEXTO DE BÚSQUEDA DE UBIGEO -->
                <div class="col-md-4">
                    <label class="form-label small text-muted text-uppercase fw-bold m-0 mb-1"
                        style="font-size:0.65rem;">3. Ubigeo ID o Nombre del Distrito</label>
                    <input type="text" class="form-control form-control-sm text-uppercase"
                        placeholder="Ej: paramonga, 150202..." wire:model.live.debounce.300ms="search_ubigeo">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover table-bordered align-middle m-0 border-dark">
                    <thead class="table-light text-center small text-uppercase fw-bold font-monospace">
                        <tr>
                            <th width="12%">Código Ubigeo</th>
                            <th class="text-start ps-3">Jurisdicción Territorio</th>
                            <th class="text-start ps-3">Partido Político Evaluado</th>
                            <th width="28%">Estado de Cobertura de la Cédula</th>
                            <th width="10%">Gestión</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($inscripciones as $ins)
                            <tr>
                                <!-- CÓDIGO UBIGEO -->
                                <td class="text-center font-monospace fw-bold bg-light text-secondary fs-6">
                                    {{ $ins->ubigeo_codigo }}</td>

                                <!-- JURISDICCIÓN -->
                                <td class="ps-3 py-2">
                                    <span class="text-muted small d-block font-monospace"
                                        style="font-size:0.68rem;">{{ $ins->region_electoral }}</span>
                                    <strong class="text-dark text-uppercase">{{ $ins->ubigeo_nombre }}</strong>
                                </td>

                                <!-- PARTIDO POLÍTICO FIJO DEL SELECTOR -->
                                <td class="fw-bold text-secondary text-uppercase ps-3">
                                    <div class="d-flex align-items-center">
                                        @if (!empty($partido_logo))
                                            <img src="{{ asset('storage/' . $partido_logo) }}" alt=""
                                                class="me-2 rounded border"
                                                style="width: 22px; height: 22px; object-fit: contain;">
                                        @endif
                                        <span class="text-dark">{{ $partido_nombre }}</span>
                                    </div>
                                </td>

                                <!-- INDICADORES SEMAFÓRICOS EN CASCADA -->
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-1">
                                        <span
                                            class="badge {{ $ins->tiene_gobernador ? 'bg-success' : 'bg-light text-muted border opacity-40' }} font-monospace px-2 py-1"
                                            style="font-size: 0.68rem;">
                                            {{ $ins->tiene_gobernador ? '✓ GOBERNADOR' : '✖ GOBERNADOR' }}
                                        </span>
                                        <span
                                            class="badge {{ $ins->tiene_consejero ? 'bg-primary' : 'bg-light text-muted border opacity-40' }} font-monospace px-2 py-1"
                                            style="font-size: 0.68rem;">
                                            {{ $ins->tiene_consejero ? '✓ CONSEJERO' : '✖ CONSEJERO' }}
                                        </span>
                                        <span
                                            class="badge {{ $ins->tiene_provincial ? 'bg-info text-dark' : 'bg-light text-muted border opacity-40' }} font-monospace px-2 py-1"
                                            style="font-size: 0.68rem;">
                                            {{ $ins->tiene_provincial ? '✓ PROVINCIAL' : '✖ PROVINCIAL' }}
                                        </span>
                                        <span
                                            class="badge {{ $ins->tiene_distrital ? 'bg-dark' : 'bg-light text-muted border opacity-40' }} font-monospace px-2 py-1"
                                            style="font-size: 0.68rem;">
                                            {{ $ins->tiene_distrital ? '✓ DISTRITAL' : '✖ DISTRITAL' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- BOTÓN DE CONTROL ADMINISTRATIVO -->
                                <!-- BOTÓN DE CONTROL ADMINISTRATIVO TOTALMENTE INTEGRADO -->
                                <td class="text-center">
                                    <button type="button"
                                        class="btn btn-danger btn-sm py-1 px-3 fw-bold text-uppercase shadow-sm text-center"
                                        style="font-size: 0.72rem; letter-spacing: 0.3px;"
                                        wire:click="eliminarAsignacion('{{ $ins->ubigeo_codigo }}', {{ $ins->partido_id }})"
                                        wire:confirm="🚨 ALERTA JEE: ¿Está seguro de revocar la postulación de tipo [{{ str_replace('_', ' ', $filtro_tipo) }}] para esta organización política en esta circunscripción? Esta acción alterará las mesas de sufragio asociadas inmediatamente.">
                                        Revocar
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4 font-monospace fw-bold">
                                    ✖ No se registran ámbitos territoriales con cédula aprobada bajo los criterios
                                    seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $inscripciones->links() }}
            </div>
        </div>
    </div>
</div>
