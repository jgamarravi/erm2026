<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Alertas de validaciones de Laravel -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Errores de Formulario</h5>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Alertas de sesión -->
            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error de Validación</h5>
                    {{ session('error') }}
                </div>
            @endif

            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Operación Exitosa</h5>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Formulario Principal -->
            <form wire:submit.prevent="guardarActa">
                <input type="hidden" wire:model="mesa_sufragio_id">

                <!-- TARJETA 1: DATOS GENERALES DE LA MESA -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-id-card mr-2"></i> Datos Generales de la Mesa</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Buscador de Mesa -->
                            <div class="col-md-3">
                                <div class="form-group position-relative">
                                    <label>Buscar Mesa de Sufragio</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                        </div>
                                        @if($mesa_sufragio_id)
                                            <input type="text" class="form-control font-weight-bold text-success bg-white" value="{{ $mesa_seleccionada_texto }}" readonly disabled>
                                            <div class="input-group-append">
                                                <button type="button" wire:click="limpiarMesa" class="btn btn-danger" title="Cambiar mesa">
                                                    <i class="fas fa-times">Otra</i>
                                                </button>
                                            </div>
                                        @else
                                            <input type="text" wire:model.live="buscar_mesa" class="form-control" placeholder="Escriba el número de mesa...">
                                        @endif
                                    </div>
                                    
                                    @if(!$mesa_sufragio_id && !empty($mesas_sugeridas))
                                        <ul class="list-group position-absolute w-100" style="z-index: 1050; max-height: 250px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                                            @foreach($mesas_sugeridas as $m)
                                                <button type="button" wire:click="seleccionarMesa({{ $m['id'] }}, '{{ $m['numero_mesa'] }}')" class="list-group-item list-group-item-action text-left py-2">
                                                    <h6 class="mb-1 text-primary"><strong>Mesa: {{ $m['numero_mesa'] }}</strong></h6>
                                                    <small class="text-muted d-block"><i class="fas fa-school mr-1"></i> {{ $m['centro_votacion']['nombre'] ?? 'Sin Centro' }}</small>
                                                    <small class="text-muted d-block"><i class="fas fa-map-signs mr-1"></i> {{ $m['centro_votacion']['ubigeo']['nombre'] ?? 'Sin Distrito' }}</small>
                                                </button>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Total Electores en Mesa</label>
                                    <input type="text" class="form-control" value="{{ $total_votantes_mesa }}" disabled>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Total Votantes en Acta</label>
                                    <input type="number" wire:model.live="total_votantes_acta" class="form-control font-weight-bold text-primary" min="1" required {{ $acta_bloqueada ? 'disabled' : '' }}>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Estado del Acta</label>
                                    <select wire:model="estado" class="form-control" required {{ $acta_bloqueada ? 'disabled' : '' }}>
                                        <option value="procesada">Cerrar (Procesada)</option>
                                        <option value="observada">Observada</option>
                                        <option value="impugnada">Impugnada</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL INFORMATIVO DE UBICACIÓN Y ESTADO DE LECTURA -->
                        @if($mesa_sufragio_id)
                            <div class="row mt-3">
                                <div class="col-md-7">
                                    <div class="callout callout-info bg-light border-info m-0" style="height: 100%;">
                                        <h5 class="text-info font-weight-bold mb-2"><i class="fas fa-info-circle mr-1"></i> Ubicación de la Mesa</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <span class="text-muted">Mesa:</span> <br>
                                                <strong class="text-success text-md"><i class="fas fa-check-circle mr-1"></i> {{ $mesa_seleccionada_texto }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted">Centro:</span> <br>
                                                <strong class="text-truncate d-inline-block" style="max-width: 100%;"><i class="fas fa-school text-secondary mr-1"></i> {{ $centro_votacion_texto }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted">Distrito:</span> <br>
                                                <strong><i class="fas fa-map-marker-alt text-secondary mr-1"></i> {{ $distrito_texto }} @if($region_calculada) <span class="text-xs text-primary">({{ $region_calculada }})</span> @endif</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    @if($acta_bloqueada)
                                        <div class="callout callout-success bg-light border-success m-0 p-2 d-flex align-items-center justify-content-between" style="height: 100%;">
                                            <div>
                                                <h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-lock mr-1"></i> Acta Cerrada</h5>
                                                <p class="text-muted text-xs m-0">Los datos están en modo de solo lectura.</p>
                                            </div>
                                            <!-- REQUERIMIENTO: Botón para quitar el estado de cerrada -->
                                            <button type="button" wire:click="habilitarEdicion" class="btn btn-warning btn-sm font-weight-bold ml-2">
                                                <i class="fas fa-unlock-alt mr-1"></i> Editar Acta
                                            </button>
                                        </div>
                                    @else
                                        <div class="callout callout-warning bg-light border-warning m-0 d-flex flex-column justify-content-center" style="height: 100%;">
                                            <h5 class="text-success font-weight-bold mb-1"><i class="fas fa-edit mr-1"></i> @if($modo_edicion) Modo Edición Habilitado @else Pendiente de Registro @endif</h5>
                                            <p class="text-muted text-xs m-0">@if($modo_edicion) Puede modificar los votos; los cambios se sobrescribirán al guardar. @else Ingrese los votos correspondientes de la mesa. @endif</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                       
                    </div>
                </div>

                <!-- TARJETA 2: CONTEO DE VOTOS POR PARTIDO Y ESPECIALIDAD -->
                <div class="card card-secondary mt-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-poll-h mr-2"></i> Conteo de Votos por Partido (Orden Cédula)</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap table-bordered m-0">
                            <thead>
                                <tr class="bg-light text-center">
                                    <th class="text-left" style="width: 5%;">Pos.</th>
                                    <th class="text-left" style="width: 35%;">Organización Política</th>
                                    <th style="width: 15%;">Gobernador</th>
                                    <th style="width: 15%;">Consejero</th>
                                    <th style="width: 15%;">Alcalde Provincial</th>
                                    <th style="width: 15%;">Alcalde Distrital</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($partidos as $partido)
                                    <tr>
                                        <td class="text-center align-middle font-weight-bold text-muted bg-light">
                                            {{ $partido->orden_cedula }}
                                        </td>
                                        <td class="d-flex justify-content-between">
                                            <!-- REQUERIMIENTO: Mostrar el logo del partido -->
                                            <strong>{{ $partido->nombre }}</strong>
                                            @if($partido->logo_url)
                                                <img src="{{ asset('storage/' .$partido->logo_url) }}" alt="Logo" class="img-thumbnail mr-2" style="max-height: 32px; max-width: 32px; object-fit: contain;">
                                            @else
                                                <div class="d-inline-block text-center mr-2 bg-secondary text-white rounded font-weight-bold" style="width: 32px; height: 32px; line-height: 32px; font-size: 11px;">S/L</div>
                                            @endif
                                            
                                        </td>
                                        <td><input type="number" wire:model.live="votos_partidos.{{ $partido->id }}.gobernador" class="form-control text-center mx-auto" style="max-width: 110px;" min="0" {{ $acta_bloqueada ? 'disabled' : '' }}></td>
                                        <td><input type="number" wire:model.live="votos_partidos.{{ $partido->id }}.consejero" class="form-control text-center mx-auto" style="max-width: 110px;" min="0" {{ $acta_bloqueada ? 'disabled' : '' }}></td>
                                        <td><input type="number" wire:model.live="votos_partidos.{{ $partido->id }}.provincial" class="form-control text-center mx-auto" style="max-width: 110px;" min="0" {{ $acta_bloqueada ? 'disabled' : '' }}></td>
                                        <td><input type="number" wire:model.live="votos_partidos.{{ $partido->id }}.distrital" class="form-control text-center mx-auto" style="max-width: 110px;" min="0" {{ ($es_capital || $acta_bloqueada) ? 'disabled' : '' }}></td>
                                    </tr>
                                @endforeach

                                <!-- Votos en Blanco -->
                                <tr class="table-warning" style="background-color: #fff9e6;">
                                    <td></td>
                                    <td class="align-middle"><strong><i class="far fa-circle mr-2 text-warning"></i> VOTOS EN BLANCO</strong></td>
                                    <td><input type="number" wire:model.live="blancos_gobernador" class="form-control text-center mx-auto font-weight-bold" style="max-width: 110px;" min="0" {{ $acta_bloqueada ? 'disabled' : '' }}></td>
                                    <td><input type="number" wire:model.live="blancos_consejero" class="form-control text-center mx-auto font-weight-bold" style="max-width: 110px;" min="0" {{ $acta_bloqueada ? 'disabled' : '' }}></td>
                                    <td><input type="number" wire:model.live="blancos_provincial" class="form-control text-center mx-auto font-weight-bold" style="max-width: 110px;" min="0" {{ $acta_bloqueada ? 'disabled' : '' }}></td>
                                    <td><input type="number" wire:model.live="blancos_distrital" class="form-control text-center mx-auto font-weight-bold" style="max-width: 110px;" min="0" {{ ($es_capital || $acta_bloqueada) ? 'disabled' : '' }}></td>
                                </tr>

                                <!-- Votos Nulos -->
                                <tr class="table-danger" style="background-color: #fde8e8;">
                                    <td></td>
                                    <td class="align-middle"><strong><i class="fas fa-times-circle mr-2 text-danger"></i> VOTOS NULOS</strong></td>
                                    <td><input type="number" wire:model.live="nulos_gobernador" class="form-control text-center mx-auto font-weight-bold" style="max-width: 110px;" min="0" {{ $acta_bloqueada ? 'disabled' : '' }}></td>
                                    <td><input type="number" wire:model.live="nulos_consejero" class="form-control text-center mx-auto font-weight-bold" style="max-width: 110px;" min="0" {{ $acta_bloqueada ? 'disabled' : '' }}></td>
                                    <td><input type="number" wire:model.live="nulos_provincial" class="form-control text-center mx-auto font-weight-bold" style="max-width: 110px;" min="0" {{ $acta_bloqueada ? 'disabled' : '' }}></td>
                                    <td><input type="number" wire:model.live="nulos_distrital" class="form-control text-center mx-auto font-weight-bold" style="max-width: 110px;" min="0" {{ ($es_capital || $acta_bloqueada) ? 'disabled' : '' }}></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer d-flex justify-content-end bg-light">
                        @if(!$acta_bloqueada)
                            <button type="submit" class="btn btn-success btn-lg font-weight-bold">
                                <i class="fas fa-save mr-2"></i> @if($modo_edicion) Actualizar Cambios @else Procesar y Guardar @endif
                            </button>
                        @else
                            <button type="button" wire:click="limpiarMesa" class="btn btn-secondary btn-lg font-weight-bold">
                                <i class="fas fa-undo mr-2"></i> Nueva Consulta
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
