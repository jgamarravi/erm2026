<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white py-3">
        <h5 class="m-0 fw-bold">Configuración de Listas Habilitadas por Ubigeo</h5>
    </div>
    <div class="card-body p-4">
        @if (session()->has('message'))
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <div>✅ {{ session('message') }}</div>
            </div>
        @endif

        <form wire:submit.prevent="guardar">
            <div class="row g-3">
                <!-- PARTIDO -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Organización Política</label>
                    <select class="form-select" wire:model="partido_id">
                        <option value="">-- Seleccione Partido --</option>
                        @foreach($partidos as $p)
                            <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- TIPO DE ELECCIÓN -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tipo de Elección (Columna de Cédula)</label>
                    <select class="form-select" wire:model.live="tipo_eleccion">
                        <option value="GOBERNADOR">GOBERNADOR (Regional)</option>
                        <option value="CONSEJERO">CONSEJERO (Regional)</option>
                        <option value="PROVINCIAL">ALCALDE PROVINCIAL (Municipal)</option>
                        <option value="DISTRITAL">ALCALDE DISTRITAL (Municipal)</option>
                    </select>
                </div>

                <hr class="my-4 text-muted">
                <h6 class="fw-bold text-secondary mb-2">Ubicación Geográfica de la Postulación</h6>

                <!-- DEPARTAMENTO -->
                <div class="col-md-4">
                    <label class="form-label small text-uppercase fw-bold text-muted">Departamento / Región</label>
                    <select class="form-select border-secondary" wire:model.live="departamento_id">
                        <option value="">-- Seleccione Región --</option>
                        @foreach($departamentos as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->region_electoral }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- PROVINCIA (Se muestra para Provincial y Distrital) -->
                <div class="col-md-4">
                    <label class="form-label small text-uppercase fw-bold text-muted">Provincia</label>
                    <select class="form-select {{ in_array($tipo_eleccion, ['PROVINCIAL', 'DISTRITAL']) ? 'border-secondary' : 'bg-light' }}" 
                            wire:model.live="provincia_id" 
                            {{ in_array($tipo_eleccion, ['PROVINCIAL', 'DISTRITAL']) ? '' : 'disabled' }}>
                        <option value="">-- Seleccione Provincia --</option>
                        @foreach($provincias as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- DISTRITO (Solo se muestra activo para Distrital) -->
                <div class="col-md-4">
                    <label class="form-label small text-uppercase fw-bold text-muted">Distrito</label>
                    <select class="form-select {{ $tipo_eleccion === 'DISTRITAL' ? 'border-secondary' : 'bg-light' }}" 
                            wire:model="distrito_id" 
                            {{ $tipo_eleccion === 'DISTRITAL' ? '' : 'disabled' }}>
                        <option value="">-- Seleccione Distrito --</option>
                        @foreach($distritos as $dist)
                            <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
@if($tipo_eleccion === 'PROVINCIAL')
<div class="col-12 mt-2">
    <div class="form-check form-switch p-3 bg-light rounded border border-warning">
        <input class="form-check-input ms-0 me-2" type="checkbox" id="replicarDistritos" wire:model="activar_distritos_dependientes">
        <label class="form-check-label fw-bold text-dark" for="replicarDistritos">
            ⚙️ Automatización: Inscribir este partido en TODOS los distritos de esta provincia de forma masiva.
        </label>
        <span class="d-block text-muted small ms-4">Úselo solo si el partido logró inscribir candidatos en el 100% de las comunas distritales.</span>
    </div>
</div>
@endif

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                    📌 Habilitar en Cédula
                </button>
            </div>
        </form>
    </div>
</div>
