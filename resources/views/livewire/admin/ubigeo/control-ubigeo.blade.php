<div>
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0 text-dark"><i class="bi bi-geo-alt-fill me-2"></i>Escrutinio Jurisdiccional - ERM 2026</h4>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Selectores Encadenados -->
        <div class="col-md-8 mb-4">
            <div class="card card-dark shadow-sm">
                <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
                    <i class="bi bi-search"></i>
                    <h5 class="card-title mb-0">Ubicación del Elector</h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row">
                        <!-- Select: Departamento / Región Electoral Blindado -->
                        <div class="col-md-4 mb-3" wire:key="select-ubigeos-regiones-2026">
                            <label class="form-label fw-bold">Departamento / Región:</label>
                            <select wire:change="cambiarDepartamento($event.target.value)"
                                class="form-select fw-bold text-primary">
                                <option value="">-- Seleccione Región --</option>
                                @foreach ($departamentos as $dep)
                                    @if (is_array($dep))
                                        <option value="{{ $dep['id'] }}">{{ $dep['nombre'] }}</option>
                                    @else
                                        <option value="{{ $dep->id }}">
                                            {{ $dep->id === '140000' ? 'LIMA PROVINCIAS (GOBIERNOS REGIONALES)' : 'REGIÓN ' . $dep->nombre }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>



                        <!-- Select: Provincia -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Provincia:</label>
                            <select wire:change="cambiarProvincia($event.target.value)" class="form-select"
                                {{ empty($provincias) ? 'disabled' : '' }}>
                                <option value="">-- Seleccione --</option>
                                @foreach ($provincias as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Select: Distrito -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Distrito:</label>
                            <select wire:change="cambiarDistrito($event.target.value)" class="form-select"
                                {{ empty($distritos) ? 'disabled' : '' }}>
                                <option value="">-- Seleccione --</option>
                                @foreach ($distritos as $dist)
                                    <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer Informativo -->
                <div class="card-footer bg-light border-top py-3" style="min-height: 60px;">
                    @if (!empty($regionElectoralActual))
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">UBIGEO: <strong>{{ $ubigeoFinal }}</strong></span>
                            <span class="badge bg-success p-2 fs-6">
                                <i class="bi bi-shield-check me-1"></i> Cédula Electoral: {{ $regionElectoralActual }}
                            </span>
                        </div>
                    @else
                        <span class="text-muted small italic">Esperando selección de distrito...</span>
                    @endif
                </div>
            </div>

            <!-- Alertas de Reglas de Votación ONPE (Cuerpo Central) -->
            @if (!empty($ubigeoFinal))
                <div class="card shadow-sm mt-3 animate__animated animate__fadeIn">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">Asignación Normativa de Cargos:</h6>
                        <div class="row text-center mt-3">
                            <div class="col-6 border-end">
                                <span class="text-muted d-block small text-uppercase font-weight-bold">Regidores del
                                    Municipio</span>
                                <h2 class="text-primary fw-bold m-0"><i
                                        class="bi bi-people-fill me-2"></i>{{ $regidoresActual }}</h2>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block small text-uppercase font-weight-bold">Consejeros
                                    Electorales</span>
                                <h2 class="text-info fw-bold m-0"><i
                                        class="bi bi-person-lines-fill me-2"></i>{{ $consejerosActual }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna Derecha: Panel de Modificación de Cuotas JNE -->
        <!-- Columna Derecha: Formularios de Cuotas Segmentados -->
        <div class="col-md-4">

            <!-- FORMULARIO PROVINCIAL: CONSEJEROS (Se activa al elegir la Provincia) -->
            @if ($modoEdicionProvincia)
                <div class="card card-outline card-info shadow-sm mb-3 animate__animated animate__fadeIn">
                    <div class="card-header bg-light">
                        <h6 class="card-title m-0 fw-bold text-info"><i class="bi bi-pencil-square me-2"></i>Cuota de
                            Consejeros (Provincia)</h6>
                    </div>
                    <div class="card-body">
                        @if (session()->has('success_prov'))
                            <div class="alert alert-success py-2 small border-0 mb-3 fw-bold">
                                {{ session('success_prov') }}</div>
                        @endif
                        <form wire:submit.prevent="guardarConsejerosProvinciales">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold small text-muted">Consejeros para la
                                    Provincia:</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-info text-white"><i
                                            class="bi bi-person-lines-fill"></i></span>
                                    <input type="number" wire:model="nuevoConsejeros"
                                        class="form-control text-center fw-bold fs-5 text-info" min="0"
                                        max="10">
                                </div>
                                @error('nuevoConsejeros')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                                
                            </div>
                            <button type="submit" class="btn btn-info text-white w-100 fw-bold shadow-sm" disabled><i
                                    class="bi bi-shield-fill-check me-1"></i> Región  {{ $consejerosRegion }} Consejeros</button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- FORMULARIO DISTRITAL: REGIDORES (Se activa al elegir el Distrito) -->
            @if ($modoEdicionDistrito)
                <div class="card card-outline card-warning shadow-sm animate__animated animate__fadeIn">
                    <div class="card-header bg-light">
                        <h6 class="card-title m-0 fw-bold text-warning"><i class="bi bi-pencil-square me-2"></i>Cuota de
                            Regidores (Distrito)</h6>
                    </div>
                    <div class="card-body">
                        @if (session()->has('success_dist'))
                            <div class="alert alert-success py-2 small border-0 mb-3 fw-bold">
                                {{ session('success_dist') }}</div>
                        @endif
                        <form wire:submit.prevent="guardarRegidoresDistritales">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold small text-muted">Regidores para el
                                    Municipio:</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-warning text-dark"><i
                                            class="bi bi-people-fill"></i></span>
                                    <input type="number" wire:model="nuevoRegidores"
                                        class="form-control text-center fw-bold fs-5 text-primary" min="5"
                                        max="50">
                                </div>
                                @error('nuevoRegidores')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-warning text-dark w-100 fw-bold shadow-sm"><i
                                    class="bi bi-save me-1"></i> Actualizar Regidores</button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Mensaje por defecto cuando el formulario está limpio -->
            @if (!$modoEdicionProvincia && !$modoEdicionDistrito)
                <div class="card card-outline card-primary shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h6 class="card-title m-0 fw-bold text-primary"><i class="bi bi-info-circle-fill me-2"></i>Mapeo
                            Técnico JNE</h6>
                    </div>
                    <div class="card-body bg-white">
                        <p class="text-muted small mb-0">
                            Filtre la circunscripción en los selectores de la izquierda. El sistema habilitará de manera
                            inteligente las cuotas de Consejeros al marcar la Provincia y las de Regidores al marcar el
                            Distrito correspondiente de forma síncrona.
                        </p>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
