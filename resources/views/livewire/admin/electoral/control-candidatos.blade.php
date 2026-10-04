<div>
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="text-dark"><i class="bi bi-person-vcard me-2"></i>Inscripción de Partidos y Candidatos - ERM 2026
            </h4>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Registro de Agrupaciones Políticas con Posición en Cédula -->
        <!-- Columna Izquierda: Registro de Agrupaciones Políticas con Logotipo -->
        <div class="col-md-4">
            <div class="card card-dark shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="card-title m-0 fw-bold"><i class="bi bi-flag-fill me-2"></i>Nueva Organización</h6>
                </div>
                <div class="card-body">
                    @if (session()->has('success_partido'))
                        <div class="alert alert-success py-2 small border-0 mb-3">{{ session('success_partido') }}</div>
                    @endif
                    <form wire:submit.prevent="guardarPartido">
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Nombre del Partido / Movimiento:</label>
                            <input type="text" wire:model="nombre_partido" class="form-control"
                                placeholder="Ej: PARTIDO NACIONAL ELECTORAL">
                            @error('nombre_partido')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-7 mb-3">
                                <label class="form-label font-weight-bold">Siglas:</label>
                                <input type="text" wire:model="siglas_partido" class="form-control"
                                    placeholder="Ej: PNE">
                                @error('siglas_partido')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-5 mb-3">
                                <label class="form-label font-weight-bold">Pos. Cédula:</label>
                                <input type="number" wire:model="orden_cedula" class="form-control text-center fw-bold"
                                    min="1">
                                @error('orden_cedula')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- INPUT PARA SUBIR LOGO CON PREVISUALIZACIÓN -->
                        <div class="mb-3 bg-light p-2 rounded border">
                            <label class="form-label font-weight-bold small"><i class="bi bi-image me-1"></i> Logotipo /
                                Símbolo:</label>
                            <input type="file" wire:model="logo_partido" class="form-control form-control-sm"
                                accept="image/*">
                            @error('logo_partido')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror

                            <!-- Barra de progreso de carga de Livewire -->
                            <div wire:loading wire:target="logo_partido" class="text-primary small mt-1">
                                <i class="bi bi-arrow-clockwise animate-spin me-1"></i> Subiendo archivo...
                            </div>

                            <!-- Previsualización temporal antes de guardar -->
                            @if ($logo_partido)
                                <div class="mt-2 text-center">
                                    <small class="text-muted d-block mb-1">Vista Previa:</small>
                                    <img src="{{ $logo_partido->temporaryUrl() }}" class="img-thumbnail rounded-circle"
                                        style="width: 60px; height: 60px; object-fit: cover;">
                                </div>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit"
                                class="btn {{ $modo_edicion ? 'btn-warning' : 'btn-dark' }} w-100 fw-bold">
                                <i class="bi {{ $modo_edicion ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                                {{ $modo_edicion ? 'Actualizar Cambios' : 'Registrar Organización' }}
                            </button>
                            @if ($modo_edicion)
                                <button type="button" wire:click="cancelarEdicion"
                                    class="btn btn-secondary">Cancelar</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado de Organizaciones con Logotipos Oficiales -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title m-0 text-dark fw-bold"><i class="bi bi-list-ol me-2"></i>Orden de Cédula
                        Oficial (ONPE)</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($partidos as $p)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2"
                                wire:key="partido-list-{{ $p->id }}">
                                <div class="d-flex align-items-center">
                                    <span
                                        class="badge bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center"
                                        style="width: 24px; height: 24px; font-size: 12px;">
                                        {{ $p->orden_cedula }}
                                    </span>

                                    @if ($p->logo_url)
                                        <img src="{{ asset('storage/' . $p->logo_url) }}"
                                            class="rounded-circle border me-3 shadow-sm"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center border me-3 shadow-sm fw-bold"
                                            style="width: 40px; height: 40px; font-size: 14px;">
                                            {{ substr($p->siglas, 0, 2) }}
                                        </div>
                                    @endif

                                    <div>
                                        <strong class="text-dark">{{ $p->siglas }}</strong>
                                        <small class="text-muted d-block"
                                            style="font-size: 11px;">{{ $p->nombre }}</small>
                                    </div>
                                </div>

                                <!-- BOTONES DE CONTROL EDITAR / ELIMINAR -->
                                <div class="d-flex gap-1">
                                    <button type="button" wire:click="editarPartido({{ $p->id }})"
                                        class="btn btn-sm btn-outline-primary py-0 px-1" title="Editar Nombre/Siglas">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" wire:click="eliminarPartido({{ $p->id }})"
                                        onclick="return confirm('¿Seguro de eliminar este partido? Se borrarán todos sus candidatos y actas registradas.')"
                                        class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <span
                                        class="badge bg-secondary rounded-pill ms-1">{{ $p->candidatos_count }}</span>
                                </div>
                            </li>

                        @empty
                            <li class="list-group-item text-center text-muted small py-3">No hay organizaciones
                                políticas registradas.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>


        <!-- Columna Derecha: Inscripción y Control de Candidatos -->
        <div class="col-md-8">
            <div class="card card-primary card-outline shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title m-0 fw-bold text-primary"><i
                            class="bi bi-person-plus-fill me-2"></i>Formulario de Postulación Oficial</h6>
                </div>
                <div class="card-body">
                    @if (session()->has('success_candidato'))
                        <div class="alert alert-success py-2 small border-0 mb-3">{{ session('success_candidato') }}
                        </div>
                    @endif
                    <form wire:submit.prevent="guardarCandidato">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">DNI del Candidato:</label>
                                <input type="text" wire:model="dni" class="form-control" placeholder="8 dígitos"
                                    maxlength="8">
                                @error('dni')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Nombres:</label>
                                <input type="text" wire:model="nombres" class="form-control">
                                @error('nombres')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Apellidos:</label>
                                <input type="text" wire:model="apellidos" class="form-control">
                                @error('apellidos')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Partido Político:</label>
                                <select wire:model="partido_id" class="form-select">
                                    <option value="">-- Seleccione Partido --</option>
                                    @foreach ($partidos as $part)
                                        <option value="{{ $part->id }}">{{ $part->siglas }} -
                                            {{ $part->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('partido_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Cargo al que Postula:</label>
                                <select wire:model.live="cargo" class="form-select">
                                    <option value="">-- Seleccione Cargo --</option>
                                    <option value="GOBERNADOR">GOBERNADOR REGIONAL</option>
                                    <option value="CONSEJERO">CONSEJERO REGIONAL</option>
                                    <option value="ALCALDE_PROVINCIAL">ALCALDE PROVINCIAL</option>
                                    <option value="ALCALDE_DISTRITAL">ALCALDE DISTRITAL</option>
                                    <option value="REGIDOR">REGIDOR MUNICIPAL</option>
                                </select>
                                @error('cargo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Bloque de Ubicación Condicional según Nivel del Cargo -->
                        <div class="row bg-light p-3 rounded mb-3 border">
                            <div class="col-12"><small
                                    class="text-muted d-block mb-2 text-uppercase fw-bold">Definición
                                    de Circunscripción Territorial:</small></div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Región / Departamento:</label>
                                <select wire:model.live="selectedDep" class="form-select form-select-sm"
                                    {{ empty($cargo) ? 'disabled' : '' }}>
                                    <option value="">-- Seleccione Región --</option>
                                    @foreach ($departamentos as $dep)
                                        <!-- Sintaxis corregida para arreglos estructurados de 26 regiones -->
                                        <option value="{{ $dep['id'] }}">{{ $dep['nombre'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Provincia:</label>
                                <select wire:model.live="selectedProv" class="form-select form-select-sm"
                                    {{ in_array($cargo, ['', 'GOBERNADOR', 'CONSEJERO']) || empty($provincias) ? 'disabled' : '' }}>
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($provincias as $prov)
                                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Distrito:</label>
                                <select wire:model.live="selectedDist" class="form-select form-select-sm"
                                    {{ in_array($cargo, ['', 'GOBERNADOR', 'CONSEJERO', 'ALCALDE_PROVINCIAL']) || empty($distritos) ? 'disabled' : '' }}>
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($distritos as $dist)
                                        <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mt-2">
                                @error('ubigeo_final')
                                    <small class="text-danger d-block fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit"
                                class="btn {{ $modo_edicion_candidato ? 'btn-warning text-dark' : 'btn-primary' }} w-100 fw-bold">
                                <i
                                    class="bi {{ $modo_edicion_candidato ? 'bi-pencil-square' : 'bi-person-check-fill' }} me-1"></i>
                                {{ $modo_edicion_candidato ? 'Actualizar Candidato' : 'Inscribir Candidato en la Lista Única' }}
                            </button>
                            @if ($modo_edicion_candidato)
                                <button type="button" wire:click="cancelarEdicionCandidato"
                                    class="btn btn-secondary">Cancelar</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Control de Últimos Candidatos Cargados -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="card-title m-0 fw-bold"><i class="bi bi-table me-2"></i>Mantenimiento de Listas de
                        Candidatos</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped m-0 align-middle small text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>DNI</th>
                                    <th class="text-start">Candidato</th>
                                    <th>Organización</th>
                                    <th>Cargo Postulado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todosLosCandidatos as $cand)
                                    <tr wire:key="cand-row-{{ $cand->id }}">
                                        <td class="fw-bold text-dark">{{ $cand->dni }}</td>
                                        <td class="text-start">
                                            <span class="fw-bold d-block text-primary">{{ $cand->apellidos }}</span>
                                            <small class="text-muted text-uppercase">{{ $cand->nombres }}</small>
                                        </td>
                                        <td><span class="badge bg-dark">{{ $cand->partido->siglas }}</span></td>
                                        <td><span
                                                class="badge bg-info text-dark fw-bold">{{ str_replace('_', ' ', $cand->cargo) }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button"
                                                    wire:click="editarCandidato({{ $cand->id }})"
                                                    class="btn btn-xs btn-outline-primary px-2"
                                                    title="Modificar Datos">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button"
                                                    wire:click="eliminarCandidato({{ $cand->id }})"
                                                    onclick="return confirm('¿Está seguro de retirar este candidato de las elecciones?')"
                                                    class="btn btn-xs btn-outline-danger px-2" title="Retirar Lista">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No hay candidatos
                                            registrados en competencia todavía.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="card-footer clearfix bg-white border-top d-flex justify-content-end py-2">
                        {{ $todosLosCandidatos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
