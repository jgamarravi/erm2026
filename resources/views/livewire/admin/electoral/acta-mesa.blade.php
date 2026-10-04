<div>
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="text-dark"><i class="bi bi-file-earmark-check-fill me-2"></i>Digitación de Actas de Escrutinio</h4>
        </div>
    </div>

    <!-- Buscador e Información de la Mesa -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-dark shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="card-title m-0 fw-bold"><i class="bi bi-search me-2"></i>Punto de Control</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Número de Mesa ONPE (6 dígitos):</label>
                        <input type="text" wire:model.live="numeroMesaBusqueda"
                            class="form-control form-control-lg text-center fw-bold" placeholder="Ej: 025142"
                            maxlength="6">
                        @if (session()->has('error_mesa')) <small
                            class="text-danger fw-bold mt-1 d-block">{{ session('error_mesa') }}</small> @enderror
                </div>

                @if ($mesaActual)
                    <div class="p-3 bg-light rounded border border-primary mb-3">
                        <h6 class="fw-bold text-primary mb-2"><i class="bi bi-info-circle-fill me-1"></i> Ficha de
                            la Mesa</h6>
                        <small class="text-muted d-block text-uppercase">Local:</small>
                        <span class="d-block fw-bold mb-1 text-dark">{{ $mesaActual->centro->nombre }}</span>
                        <small class="text-muted d-block text-uppercase">Carga Electoral:</small>
                        <span class="badge bg-dark fs-6">{{ $mesaActual->electores_habiles }} ciudadanos</span>
                    </div>

                    <!-- SEMÁFORO DE ESTADO LEGAL DEL ACTA EN TIEMPO REAL -->
                    <div class="p-3 bg-white rounded border shadow-sm animate__animated animate__fadeIn"
                        wire:key="semaforo-estado-mesa shadow-sm">
                        <small class="text-muted d-block text-uppercase font-weight-bold mb-2"><i
                                class="bi bi-gavel me-1"></i> Condición de la Mesa:</small>

                        @if ($estadoVerificacionMesa === 'BORRADOR')
                            <div class="alert alert-info py-2 small border-0 text-dark m-0 fw-bold">
                                <i class="bi bi-pencil-fill me-1"></i> EN DIGITACIÓN (BORRADOR)
                            </div>
                        @elseif($estadoVerificacionMesa === 'CONFORME')
                            <div class="alert alert-success py-2 small border-0 text-dark m-0 fw-bold">
                                <i class="bi bi-check-circle-fill me-1 text-success"></i> CONFORME (ACTA OFICIAL)
                            </div>
                        @elseif($estadoVerificacionMesa === 'OBSERVADA')
                            <div
                                class="alert alert-danger py-2 small border-0 text-dark m-0 fw-bold animate__animated animate__flash">
                                <i class="bi bi-exclaim-triangle-fill me-1 text-danger"></i> ACTA OBSERVADA
                                (DESCUADRADA)
                            </div>
                        @elseif($estadoVerificacionMesa === 'IMPUGNADA')
                            <div class="alert alert-warning py-2 small border-0 text-dark m-0 fw-bold">
                                <i class="bi bi-shield-fill-exclamation me-1 text-warning"></i> IMPUGNADA POR
                                PERSONERO
                            </div>
                        @elseif($estadoVerificacionMesa === 'SOLICITUD_NULIDAD')
                            <div class="alert alert-purple py-2 small border-0 text-white m-0 fw-bold"
                                style="background-color: #6f42c1;">
                                <i class="bi bi-slash-circle-fill me-1"></i> SOLICITUD DE NULIDAD (JEE)
                            </div>
                        @elseif($estadoVerificacionMesa === 'SIN_FIRMAS')
                            <div class="alert alert-secondary py-2 small border-0 text-dark m-0 fw-bold">
                                <i class="bi bi-file-earmark-text-fill me-1"></i> ERROR MATERIAL (SIN FIRMAS)
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Formulario de carga de votos -->
    <div class="col-md-8">
        @if ($mesaActual)
            <div class="card card-primary card-outline shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="card-title m-0 fw-bold text-primary">
                        <i class="bi bi-pencil-square me-2"></i>Escrutinio Activo:
                        <span class="badge bg-dark ms-2">{{ str_replace('_', ' ', $tipo_eleccion) }}</span>
                    </h6>

                    <!-- Liberamos el select eliminando el disabled para permitir la navegación de lectura en actas cerradas -->
                    <select wire:model.live="tipo_eleccion"
                        class="form-select form-select-sm w-auto fw-bold text-primary">
                        <option value="GOBERNADOR">GOBERNADOR</option>
                        <option value="CONSEJERO">CONSEJERO</option>
                        <option value="ALCALDE_PROVINCIAL">ALCALDE PROVINCIAL</option>
                        @if (!$esCapitalMesa)
                            <option value="ALCALDE_DISTRITAL">ALCALDE DISTRITAL</option>
                        @endif
                    </select>

                </div>

                <div class="card-body">
                    <!-- Alertas de Validación Estricta -->
                    @error('votos_excedidos') <div class="alert alert-danger border-0 small font-weight-bold"><i
                            class="bi bi-x-circle-fill me-2"></i>{{ $message }}</div> @enderror
                    @error('votos_vacios') <div class="alert alert-warning border-0 small font-weight-bold"><i
                            class="bi bi-exclaim-triangle-fill me-2"></i>{{ $message }}</div> @enderror
                    @if (session()->has('success_acta'))
                        <div class="alert alert-success border-0 small font-weight-bold"><i
                                class="bi bi-check-circle-fill me-2"></i>{{ session('success_acta') }}</div>
                    @endif
                    @if (session()->has('info_acta'))
                        <div class="alert alert-danger border-0 small font-weight-bold"><i
                                class="bi bi-lock-fill me-2"></i>{{ session('info_acta') }}</div>
                    @endif
                    @error('votos_descuadrados') <div class="alert alert-danger border-0 small font-weight-bold"><i
                            class="bi bi-calculator-fill me-2"></i>{{ $message }}</div> @enderror
                    @if (session()->has('warning_acta'))
                        <div class="alert alert-warning border-0 small font-weight-bold text-dark mb-2">
                            <i
                                class="bi bi-exclaim-triangle-fill me-2 text-danger"></i>{{ session('warning_acta') }}
                        </div>
                    @endif


                    <form wire:submit.prevent="procesarYFirmaActa">
                        <div class="row">
                            <!-- Votos por partido conectados a la matriz temporal -->
                            @foreach ($partidos as $partido)
                                <div class="col-md-6 mb-3 d-flex align-items-center justify-content-between border-bottom pb-2"
                                    wire:key="input-voto-{{ $tipo_eleccion }}-{{ $partido->id }}">
                                    <div class="d-flex align-items-center">
                                        <!-- MINIATURA DEL LOGO OFICIAL -->
                                        @if ($partido->logo_url)
                                            <img src="{{ asset('storage/' . $partido->logo_url) }}"
                                                class="rounded border me-2 shadow-sm"
                                                style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="rounded bg-secondary text-white d-flex align-items-center justify-content-center border me-2 shadow-sm fw-bold small"
                                                style="width: 32px; height: 32px;">
                                                {{ substr($partido->siglas, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span
                                                class="badge me-2 
    {{ $tipo_eleccion == 'GOBERNADOR'
        ? 'bg-primary'
        : ($tipo_eleccion == 'CONSEJERO'
            ? 'bg-info text-dark'
            : ($tipo_eleccion == 'ALCALDE_PROVINCIAL'
                ? 'bg-secondary'
                : 'bg-success')) }}">
                                                {{ $partido->siglas }}
                                            </span>


                                            <small class="text-secondary fw-bold">{{ $partido->nombre }}</small>
                                        </div>
                                    </div>
                                    <input type="number"
                                        wire:model="votosMemoria.{{ $tipo_eleccion }}.{{ $partido->id }}"
                                        class="form-control text-end fw-bold" style="width: 90px;" min="0"
                                        {{ $actaYaCerrada ? 'disabled' : '' }}>
                                </div>
                            @endforeach
                        </div>

                        <!-- Votos Especiales conectados a la matriz temporal por tipo con su propia llave de renderizado -->
                        <div class="row bg-light p-3 rounded border my-3"
                            wire:key="inputs-especiales-{{ $tipo_eleccion }}">
                            <div class="col-6">
                                <label class="form-label fw-bold text-muted"><i class="bi bi-circle me-1"></i> Votos
                                    en Blanco:</label>
                                <input type="number" wire:model="blancos.{{ $tipo_eleccion }}"
                                    class="form-control text-end fw-bold" min="0"
                                    {{ $actaYaCerrada ? 'disabled' : '' }}>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-danger"><i class="bi bi-x-square me-1"></i>
                                    Votos Nulos:</label>
                                <input type="number" wire:model="nulos.{{ $tipo_eleccion }}"
                                    class="form-control text-end fw-bold" min="0"
                                    {{ $actaYaCerrada ? 'disabled' : '' }}>
                            </div>
                        </div>


                        <!-- CONTROL DEL BOTÓN DE CIERRE ÚNICO -->

                        <!-- CONTROL DEL BOTÓN DE CIERRE ÚNICO Y DESBLOQUEO -->
                        @if ($actaYaCerrada)
                            <div
                                class="row bg-dark rounded p-3 align-items-center m-0 shadow-sm border border-secondary">
                                <div class="col-sm-8 text-center text-sm-start mb-2 mb-sm-0">
                                    <span class="text-white fw-bold d-block">
                                        <i class="bi bi-shield-lock-fill text-warning me-1"></i> ACTA TRANSMITIDA Y
                                        CONGELADA (OFICIAL)
                                    </span>
                                    <small class="text-muted">Modo de lectura total activo para auditar
                                        cifras.</small>
                                </div>
                                @can('unlock acta')
                                    <div class="col-sm-4 text-center text-sm-end">
                                        <!-- Botón de apertura manual controlado con JavaScript confirm -->
                                        <button type="button" wire:click="abrirActaParaModificacion"
                                            onclick="return confirm('¿Está completamente seguro de abrir esta acta? Esto romperá el candado de transmisión y habilitará la edición manual de datos.')"
                                            class="btn btn-outline-warning btn-sm fw-bold w-100">
                                            <i class="bi bi-unlock-fill me-1"></i> Desbloquear Acta
                                        </button>
                                    </div>
                                @endcan
                            </div>
                        @else
                            <div
                                class="p-2 bg-warning bg-opacity-10 text-center rounded mb-3 border border-warning small">
                                <i class="bi bi-exclaim-triangle-fill text-warning me-1"></i>
                                <strong>Nota:</strong>
                                Puede navegar en el selector superior para completar todos los cuerpos del acta. Al
                                presionar el botón inferior se validará la consistencia general y se cerrará la mesa
                                permanentemente.
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold fs-5 py-2 shadow-sm"><i
                                    class="bi bi-lock-fill me-1"></i> Validar, Firmar y Cerrar Acta
                                General</button>
                        @endif

                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center border-0 py-4 shadow-sm"><i
                    class="bi bi-file-earmark-bar-graph display-6 d-block mb-2 text-primary"></i>Ingrese un número
                de mesa válido a la izquierda para iniciar la captura unificada del escrutinio.</div>
        @endif
    </div>

</div>

<!-- TABLA RESPONSIVA DE HISTÓRICO EN LA PARTE INFERIOR -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h6 class="card-title m-0 fw-bold"><i class="bi bi-table me-2"></i>Historial de Actas Digitadas en
                    el Sistema</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped m-0 align-middle small text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Mesa</th>
                                <th>Local de Votación</th>
                                <th>Elección</th>
                                <th>Organización Política</th>
                                <th>Votos Válidos</th>
                                <th>Blancos</th>
                                <th>Nulos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historico as $acta)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $acta->mesa->numero_mesa }}</td>
                                    <td class="text-start"><small
                                            class="fw-bold d-block">{{ $acta->mesa->centro->nombre }}</small></td>
                                    <td><span
                                            class="badge bg-info text-dark fw-bold">{{ str_replace('_', ' ', $acta->tipo_eleccion) }}</span>
                                    </td>
                                    <td class="text-start"><span
                                            class="badge bg-dark me-1">{{ $acta->partido->siglas }}</span>
                                        <small>{{ $acta->partido->nombre }}</small>
                                    </td>
                                    <td class="fw-bold text-success">{{ $acta->votos_validos }}</td>
                                    <td class="text-muted">{{ $acta->votos_blancos }}</td>
                                    <td class="text-danger">{{ $acta->votos_nulos }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3 fs-6">No se han
                                        procesado
                                        actas en esta jornada electoral.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- BOTONES DE PAGINACIÓN INTERACTIVOS (ADMINLTE CSS) -->
                <div class="card-footer clearfix bg-white border-top d-flex justify-content-end">
                    {{ $historico->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
