<div class="container-fluid pt-3">
    <!-- SECCIÓN DE NOTIFICACIONES FLASH -->
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Operación Denegada</h5>
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Registro Actualizado</h5>
            {{ session('success') }}
        </div>
    @endif

    <!-- TARJETA Superior: Búsqueda del Ámbito Territorial -->
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold text-primary"><i class="fas fa-search-location mr-2"></i> Selección
                de Jurisdicción Distrital</h3>
        </div>
        <div class="card-body">
            <div class="row" wire:key="filtros-gestion-partidos">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Región / Departamento</label>
                        <select wire:model.live="region_id" class="form-control">
                            <option value="">-- Seleccione Región --</option>
                            @foreach($regiones as $r)
                                <option value="{{ $r->region_electoral }}">{{ $r->region_electoral }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Provincia</label>
                        <select wire:model.live="provincia_id" class="form-control" {{ !$region_id ? 'disabled' : '' }}>
                            <option value="">-- Seleccione Provincia --</option>
                            @foreach($provincias as $p)
                                <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Distrito Objetivo</label>
                        <select wire:model.live="distrito_id" class="form-control" {{ !$provincia_id ? 'disabled' : '' }}>
                            <option value="">-- Seleccione Distrito --</option>
                            @foreach($distritos as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nombre'] }} ({{ $d['es_capital'] ? 'Capital' : 'Distrito' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN INTERACTIVA DE ASIGNACIÓN -->
    <div class="row">
        @if($distrito_id)
            <!-- COLUMNA IZQUIERDA: CREAR / ASOCIAR NUEVA AGRUPACIÓN -->
            <div class="col-md-4" wire:key="formulario-vincular-partido">
                <div class="card card-success shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Inscribir Partido
                            Habilitado</h3>
                    </div>
                    <form wire:submit.prevent="asignarPartido">
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label>Seleccionar Organización Política</label>
                                <select wire:model="partido_seleccionado_id" class="form-control" required>
                                    <option value="">-- Seleccione Partido Comercial --</option>
                                    @foreach($partidos_disponibles as $pd)
                                        <option value="{{ $pd->id }}">Pos. {{ $pd->orden_cedula }} - {{ $pd->nombre }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Solo se listan las agrupaciones nacionales que aún no
                                    compiten en este distrito.</small>
                            </div>
                        </div>
                        <div class="card-footer bg-light d-flex justify-content-end">
                            <button type="submit" class="btn btn-success font-weight-bold shadow-xs">
                                <i class="fas fa-link mr-1"></i> Habilitar en Distrito
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- COLUMNA DERECHA: TABLA LISTADO DE PARTIDOS EN CARRERA (CON OPCIÓN ELIMINAR) -->
            <div class="col-md-8" wire:key="tabla-partidos-inscritos">
                <div class="card card-secondary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-list-ol mr-2"></i> Partidos Políticos en
                            Competencia Local</h3>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-striped table-hover m-0 table-bordered text-sm">
                            <thead class="bg-light text-center text-muted text-xs uppercase">
                                <tr>
                                    <th style="width: 10%;">Cédula</th>
                                    <th class="text-left" style="width: 75%;">Organización Política</th>
                                    <th style="width: 15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partidosAsignados as $pa)
                                    <tr>
                                        <td class="text-center align-middle font-weight-bold text-muted bg-light">
                                            {{ $pa->orden_cedula }}
                                        </td>
                                        <td class="align-middle font-weight-bold text-dark">
                                            @if($pa->logo_url)
                                                <img src="{{ asset('storage/'.$pa->logo_url) }}" class="img-circle mr-2 border"
                                                    style="width:26px; height:26px; object-fit:contain;" alt="">
                                            @endif
                                            {{ $pa->nombre }}
                                        </td>
                                        <!-- ELIMINAR / DETACH RELACIÓN -->
                                        <td class="text-center align-middle">
                                            <button type="button" wire:click="desasignarPartido({{ $pa->id }})"
                                                wire:confirm="¿Está seguro de que desea retirar este partido político de este distrito? Al hacerlo, no aparecerá en el ingreso de actas de esta zona."
                                                class="btn btn-xs btn-danger font-weight-bold px-2 py-1 shadow-sm">
                                                <i class="fas fa-trash-alt mr-1"></i> Retirar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center p-4 text-muted">
                                            <i class="fas fa-users-slash fa-2x mb-2 text-gray"></i> <br>
                                            No se registran agrupaciones políticas habilitadas en este distrito. El módulo de
                                            actas saldrá vacío para esta zona.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- PANTALLA INFORMATIVA INICIAL -->
            <div class="col-12" wire:key="pantalla-espera-gestion">
                <div class="card card-light border p-5 text-center text-secondary shadow-sm"
                    style="background-color: #f8f9fa;">
                    <div class="p-4">
                        <i class="fas fa-users-cog fa-3x text-muted mb-3"></i>
                        <h5 class="font-weight-bold text-dark">Configurador de Cédulas Distritales</h5>
                        <p class="text-muted text-sm mx-auto mb-0" style="max-width: 500px;">
                            Seleccione una Región, Provincia y un Distrito en las barras superiores para desplegar las
                            agrupaciones políticas autorizadas para competir y configurar el padrón de actas de esa
                            jurisdicción.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>