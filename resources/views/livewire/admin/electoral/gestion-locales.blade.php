<div>
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="text-dark"><i class="bi bi-building-gear me-2"></i>Estructura de Locales y Mesas - ERM 2026</h4>
        </div>
    </div>

    <!-- BARRA DE BÚSQUEDA GLOBAL DE MESAS ONPE -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="card-title m-0 fw-bold text-primary"><i class="bi bi-search me-2"></i>Localizador Rápido de Mesas
                de Sufragio</h6>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white"><i class="bi bi-hash"></i></span>
                        <input type="text" wire:model.live="buscarMesa" class="form-control form-control-lg"
                            placeholder="Ingrese los 6 dígitos de la mesa (Ej: 035241)" maxlength="6">
                        @if (!empty($buscarMesa))
                            <button class="btn btn-secondary" wire:click="limpiarBusqueda"
                                type="button">Limpiar</button>
                        @endif
                    </div>
                    <small class="text-muted d-block mt-1">La búsqueda se ejecutará automáticamente al completar los 6
                        caracteres.</small>
                </div>
                <div class="col-md-6">
                    @if (session()->has('search_error') && strlen($buscarMesa) === 6 && !$resultadoBusqueda)
                        <div class="alert alert-danger m-0 py-2 small border-0">
                            <i class="bi bi-exclaim-triangle-fill me-2"></i>{{ session('search_error') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Panel de Resultados Estilizado con AdminLTE 4 -->
            @if ($resultadoBusqueda)
                <div class="row mt-4 animate__animated animate__fadeIn">
                    <div class="col-12">
                        <div class="callout callout-success bg-light border-start border-4 border-success p-3 rounded">
                            <h5 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill me-2"></i>Ubicación
                                Encontrada de la Mesa N° {{ $resultadoBusqueda['mesa'] }}</h5>
                            <div class="row">
                                <div class="col-sm-4 mb-2">
                                    <small class="text-muted d-block text-uppercase">Local de Votación:</small>
                                    <strong class="fs-6 text-dark">{{ $resultadoBusqueda['local'] }}</strong>
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <small class="text-muted d-block text-uppercase">Dirección:</small>
                                    <span class="text-dark">{{ $resultadoBusqueda['direccion'] }}</span>
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <small class="text-muted d-block text-uppercase">Carga Electoral:</small>
                                    <span class="badge bg-dark">{{ $resultadoBusqueda['electores'] }} ciudadanos
                                        hábiles</span>
                                </div>
                            </div>
                            <div class="row border-top pt-2 mt-2">
                                <div class="col-12">
                                    <span class="text-muted small">Jurisdicción:</span>
                                    <span
                                        class="badge bg-secondary me-1">{{ $resultadoBusqueda['departamento'] }}</span>
                                    <span class="badge bg-secondary me-1">{{ $resultadoBusqueda['provincia'] }}</span>
                                    <span class="badge bg-secondary">{{ $resultadoBusqueda['distrito'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Buscador Jurisdiccional -->
    <div class="card card-dark shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Departamento:</label>
                    <select wire:model.live="selectedDep" class="form-select">
                        <option value="">-- Seleccione --</option>
                        @foreach ($departamentos as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Provincia:</label>
                    <select wire:model.live="selectedProv" class="form-select"
                        {{ empty($provincias) ? 'disabled' : '' }}>
                        <option value="">-- Seleccione --</option>
                        @foreach ($provincias as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Distrito:</label>
                    <select wire:model.live="selectedDist" class="form-select"
                        {{ empty($distritos) ? 'disabled' : '' }}>
                        <option value="">-- Seleccione --</option>
                        @foreach ($distritos as $dist)
                            <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($selectedDist))
        <div class="row">
            <!-- Columna Izquierda: Gestión de Locales -->
            <div class="col-md-6">
                <div
                    class="card {{ $modo_edicion_local ? 'card-warning' : 'card-primary' }} card-outline shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="card-title m-0 fw-bold">
                            <i class="bi {{ $modo_edicion_local ? 'bi-pencil-square' : 'bi-plus-circle' }} me-2"></i>
                            {{ $modo_edicion_local ? 'Modificar Centro de Votación' : 'Nuevo Centro de Votación' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if (session()->has('success_local'))
                            <div class="alert alert-success py-2 small border-0 mb-2">{{ session('success_local') }}
                            </div>
                        @endif
                        <form wire:submit.prevent="guardarLocal">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Local:</label>
                                <input type="text" wire:model="nombre_local" class="form-control"
                                    placeholder="Ej: I.E. ALFONSO UGARTE">
                                @error('nombre_local')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dirección Física:</label>
                                <input type="text" wire:model="direccion_local" class="form-control"
                                    placeholder="Ej: Av. Paseo de la República 3500">
                                @error('direccion_local')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit"
                                    class="btn {{ $modo_edicion_local ? 'btn-warning' : 'btn-primary' }} w-100 fw-bold">
                                    <i class="bi bi-save me-1"></i>
                                    {{ $modo_edicion_local ? 'Actualizar Local' : 'Registrar Local' }}
                                </button>
                                @if ($modo_edicion_local)
                                    <button type="button" wire:click="cancelarEdicionLocal"
                                        class="btn btn-secondary">Cancelar</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Locales -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="card-title m-0">Locales Registrados</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover m-0 align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Local</th>
                                        <th>Mesas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($locales as $loc)
                                        <tr class="{{ $selectedCentro == $loc->id ? 'table-primary' : '' }}">
                                            <td>
                                                <span
                                                    class="fw-bold d-block text-uppercase">{{ $loc->nombre }}</span>
                                                <small class="text-muted">{{ $loc->direccion }}</small>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $loc->mesas_count }}</span></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button wire:click="$set('selectedCentro', {{ $loc->id }})"
                                                        class="btn btn-xs btn-info text-white px-2"
                                                        title="Administrar Mesas">
                                                        <i class="bi bi-arrow-right-short fs-6"></i>
                                                    </button>
                                                    <button wire:click="editarLocal({{ $loc->id }})"
                                                        class="btn btn-xs btn-outline-primary px-1"
                                                        title="Editar Local">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button wire:click="eliminarLocal({{ $loc->id }})"
                                                        onclick="return confirm('¿Seguro de eliminar este local? Se borrarán todas sus mesas asignadas.')"
                                                        class="btn btn-xs btn-outline-danger px-1"
                                                        title="Eliminar Local">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No hay locales
                                                cargados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Gestión de Mesas -->
            <div class="col-md-6">
                @if (!empty($selectedCentro))
                    <div
                        class="card {{ $modo_edicion_mesa ? 'card-warning' : 'card-success' }} card-outline shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title m-0 fw-bold">
                                <i
                                    class="bi {{ $modo_edicion_mesa ? 'bi-pencil-square' : 'bi-plus-square' }} me-2"></i>
                                {{ $modo_edicion_mesa ? 'Modificar Mesa de Sufragio' : 'Nueva Mesa de Sufragio' }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if (session()->has('success_mesa'))
                                <div class="alert alert-success py-2 small border-0 mb-2">
                                    {{ session('success_mesa') }}</div>
                            @endif
                            <form wire:submit.prevent="guardarMesa">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label">N° Mesa:</label>
                                        <input type="text" wire:model="numero_mesa"
                                            class="form-control text-center fw-bold text-primary" placeholder="038542"
                                            maxlength="6">
                                        @error('numero_mesa')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Electores Hábiles:</label>
                                        <input type="number" wire:model="electores_habiles"
                                            class="form-control text-center">
                                        @error('electores_habiles')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit"
                                        class="btn {{ $modo_edicion_mesa ? 'btn-warning text-dark' : 'btn-success' }} w-100 fw-bold">
                                        <i class="bi bi-check-square me-1"></i>
                                        {{ $modo_edicion_mesa ? 'Actualizar Mesa' : 'Añadir Mesa al Local' }}
                                    </button>
                                    @if ($modo_edicion_mesa)
                                        <button type="button" wire:click="cancelarEdicionMesa"
                                            class="btn btn-secondary">Cancelar</button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de Mesas -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="card-title m-0">Mesas del Local Seleccionado</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover m-0 align-middle small text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>N° Mesa ONPE</th>
                                        <th>Electores</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($mesas as $m)
                                        <tr wire:key="mesa-item-{{ $m->id }}">
                                            <td class="fw-bold text-primary"><i
                                                    class="bi bi-card-list me-2"></i>{{ $m->numero_mesa }}</td>
                                            <td>{{ $m->electores_habiles }} ciudadanos</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button wire:click="editarMesa({{ $m->id }})"
                                                        class="btn btn-xs btn-outline-primary py-0 px-1"
                                                        title="Editar Mesa">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button wire:click="eliminarMesa({{ $m->id }})"
                                                        onclick="return confirm('¿Seguro de eliminar esta mesa?')"
                                                        class="btn btn-xs btn-outline-danger py-0 px-1"
                                                        title="Eliminar Mesa">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Este local no
                                                cuenta con mesas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info text-center border-0 py-4"><i
                            class="bi bi-arrow-left-circle-fill display-6 d-block mb-2"></i>Selecciona un Centro de
                        Votación de la tabla izquierda para gestionar sus mesas.</div>
                @endif
            </div>
        </div>
    @endif
</div>
