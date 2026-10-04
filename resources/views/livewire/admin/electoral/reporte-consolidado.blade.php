<div class="container-fluid pt-3">
    <!-- PANEL DE SELECTORES EN CASCADA TERRITORIAL -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-map-marked-alt mr-2 text-primary"></i> Filtros de Ámbito en Cascada (INEI)</h3>
        </div>
        <div class="card-body">
            <div class="row" wire:key="panel-filtros-electorales">
                <!-- 1. Tipo de Elección -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo de Elección</label>
                        <select wire:model.live="tipo_eleccion" class="form-control font-weight-bold">
                            <option value="gobernador">Gobernador Regional</option>
                            <option value="consejero">Consejero Regional</option>
                            <option value="provincial">Alcalde Provincial</option>
                            <option value="distrital">Alcalde Distrital</option>
                        </select>
                    </div>
                </div>

                <!-- 2. Región Electoral -->
                <div class="col-md-3">
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

                <!-- 3. Provincia INEI -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Provincia</label>
                        <select wire:model.live="provincia_id" class="form-control" {{ $tipo_eleccion === 'gobernador' ? 'disabled' : '' }}>
                            <option value="">-- Seleccione Provincia --</option>
                            @foreach($provincias as $p)
                                <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 4. Distrito Jurisdiccional (Deshabilitado si es Provincial) -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Distrito</label>
                        <select wire:model.live="distrito_id" class="form-control" {{ in_array($tipo_eleccion, ['gobernador', 'consejero', 'provincial']) ? 'disabled' : '' }}>
                            <option value="">-- Seleccione Distrito --</option>
                            @foreach($distritos as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Configuración manual de escaños (Solo visible si aplica D'Hondt) -->
            @if($esPluripersonal)
                <div class="row mt-2" wire:key="panel-escanos-dhondt">
                    <div class="col-md-3">
                        <div class="form-group m-0">
                            <label class="text-xs">Cargos / Escaños a repartir (D'Hondt):</label>
                            <input type="number" wire:model.live="escanos_repartir" class="form-control form-control-sm font-weight-bold text-success" min="1">
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- SECCIÓN DINÁMICA DE RESULTADOS -->
    @if($filtroValido)
        <div class="row mt-4" wire:key="resultados-computados-container">
            <!-- COLUMNA IZQUIERDA: GRÁFICOS DE BARRAS DE VOTOS VÁLIDOS -->
            <div class="col-md-7">
                <div class="card card-primary card-outline shadow">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar mr-2 text-primary"></i> Gráfico Estadístico de Votos Válidos</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 bg-light p-2 rounded d-flex justify-content-between align-items-center">
                            <span class="text-muted font-weight-bold">Ámbito: <span class="text-primary">{{ strtoupper($tipo_eleccion) }}</span></span>
                            <span class="badge badge-info p-2 font-weight-bold" style="font-size: 95%;">Total Votos Útiles: {{ number_format($votosPartidos->sum('votos_totales')) }}</span>
                        </div>
                        
                        @forelse($votosPartidos->sortByDesc('votos_totales') as $p)
                            @php 
                                $sumaVotos = $votosPartidos->sum('votos_totales');
                                $porcentaje = $sumaVotos > 0 ? ($p->votos_totales / $sumaVotos) * 100 : 0; 
                            @endphp
                            <div class="progress-group mb-4">
                                <div class="d-flex align-items-center mb-1">
                                    @if($p->logo_url)
                                        <img src="{{ asset('storage/'.$p->logo_url) }}" class="img-thumbnail mr-2" style="width:30px; height:30px; object-fit:contain;" alt="">
                                    @else
                                        <div class="bg-secondary text-white rounded-circle text-center mr-2 font-weight-bold text-xs" style="width:30px; height:30px; line-height:30px;">OP</div>
                                    @endif
                                    <div>
                                        <span class="font-weight-bold text-dark">{{ $p->nombre }}</span>
                                    </div>
                                    <span class="ml-auto font-weight-bold text-primary">{{ number_format($p->votos_totales) }} <span class="text-muted text-xs">votos</span></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="progress progress-md w-100 m-0 shadow-sm" style="height: 18px; border-radius: 4px;">
                                        <div class="progress-bar bg-gradient-primary progress-bar-striped" style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                    <span class="ml-2 font-weight-bold text-md text-secondary" style="min-width: 55px; text-align: right;">{{ number_format($porcentaje, 2) }}%</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-2 text-gray"></i> <br>
                                No se registran actas procesadas para el ámbito territorial seleccionado.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: DISTRIBUCIÓN ESCAÑOS D'HONDT (SI CORRESPONDE) -->
            <div class="col-md-5">
                @if($esPluripersonal)
                    <div class="card card-success card-outline shadow">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-balance-scale mr-2 text-success"></i> Distribución de Escaños (Método D'Hondt)</h3>
                        </div>
                        <div class="card-body p-0">
                            @if(empty($repartoDhondt))
                                <div class="text-center p-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 text-gray"></i> <br> 
                                    No hay votos suficientes registrados para calcular la cifra repartidora.
                                </div>
                            @else
                                <table class="table table-striped table-valign-middle m-0 table-bordered">
                                    <thead class="bg-light">
                                        <tr class="text-center text-xs text-muted uppercase">
                                            <th class="text-left" style="width: 55%;">Organización Política</th>
                                            <th style="width: 25%;">Votos Efectivos</th>
                                            <th style="width: 20%;">Escaños</th>
                                        </tr>
                                    </thead>
                                                                        <tbody>
                                        @foreach($repartoDhondt as $rd)
                                            @php 
                                                $sumaVotos = $votosPartidos->sum('votos_totales');
                                                $porc = $sumaVotos > 0 ? ($rd['votos'] / $sumaVotos) * 100 : 0; 
                                            @endphp
                                            <tr>
                                                <td class="align-middle">
                                                    @if($rd['logo_url'])
                                                        <img src="{{ asset('storage/'.$rd['logo_url']) }}" class="img-circle mr-2 border shadow-sm" style="width:28px; height:28px; object-fit:contain;" alt="">
                                                    @endif
                                                    <span class="font-weight-bold text-dark d-inline-block">{{ $rd['nombre'] }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark">{{ number_format($rd['votos']) }}</span> <br>
                                                    <small class="text-success font-weight-bold">({{ number_format($porc, 1) }}%)</small>
                                                </td>
                                                <!-- CORREGIDO: Celda forzada con texto visible y alineado en AdminLTE -->
                                                <td class="text-center align-middle bg-white font-weight-bold" style="vertical-align: middle !important;">
                                                    <span class="badge bg-success p-2 text-white shadow-sm" style="font-size: 115%; min-width: 40px; display: inline-block; border-radius: 6px;">
                                                        <i class="fas fa-chair mr-1"></i> {{ $rd['escanos'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text font-weight-bold">Elección Unipersonal</span>
                            <span class="info-box-number text-xs" style="font-weight: 400; line-height: 1.3;">
                                Para el cargo de Gobernador Regional, la asignación se define por mayoría simple de
                                votos válidos acumulados. No requiere cálculo de cifra repartidora D'Hondt.
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- MENSAJE DE PANTALLA DE ESPERA / PRE-SELECCIÓN -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-light border p-5 text-center text-secondary shadow-sm">
                    <div class="p-4">
                        <i class="fas fa-map-signs fa-4x text-muted mb-3"></i>
                        Esperando Selección de Ámbito Territorial
                        Para visualizar los resultados y calcular la cifra repartidora, complete los filtros requeridos
                        en la parte superior:Gobernador: Requiere seleccionar la Región.Consejero: Requiere seleccionar
                        Región y Provincia.Alcaldes: Requiere seleccionar Región, Provincia y Distrito.
                    </div>
                </div>
            </div>
        </div>

    @endif

</div>
