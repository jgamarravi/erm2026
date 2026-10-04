<div class="container-fluid pt-3">
    <!-- PANEL DE CONTROL JERÁRQUICO -->
    <div class="card card-outline card-navy shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-landmark mr-2 text-navy"></i> Consulta de Autoridades
                Electas</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Nivel de Gobierno -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nivel de Gobierno</label>
                        <select wire:model.live="ambito" class="form-control font-weight-bold">
                            <option value="regional">Gobierno Regional (Gobernador / Consejeros)</option>
                            <option value="provincial">Provincial / Capital (Alcalde / Regidores)</option>
                            <option value="distrital">Distrital / Local (Alcalde / Regidores)</option>
                        </select>
                    </div>
                </div>
                <!-- Región -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Región Electoral</label>
                        <select wire:model.live="region_id" class="form-control">
                            <option value="">-- Seleccione Región --</option>
                            @foreach ($regiones as $r)
                                <option value="{{ $r->region_electoral }}">{{ $r->region_electoral }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- Provincia -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Provincia</label>
                        <select wire:model.live="provincia_id" class="form-control"
                            {{ $ambito === 'regional' ? 'disabled' : '' }}>
                            <option value="">-- Seleccione Provincia --</option>
                            @foreach ($provincias as $p)
                                <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- Distrito -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Distrito</label>
                        <select wire:model.live="distrito_id" class="form-control"
                            {{ in_array($ambito, ['regional', 'provincial']) ? 'disabled' : '' }}>
                            <option value="">-- Seleccione Distrito --</option>
                            @foreach ($distritos as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RENDERIZADO DE PROCLAMACIÓN OFICIAL -->
    @if ($filtroValido)
        <div class="row mt-4" wire:key="panel-proclamacion-autoridades">
            <!-- COLUMNA IZQUIERDA: AUTORIDAD EJECUTIVA ELECTA (Gobernador o Alcalde) -->
            <div class="col-md-5">
                @if ($autoridadEjecutiva)
                    <div class="card card-widget widget-user shadow">
                        <div class="widget-user-header bg-gradient-navy">
                            <h3 class="widget-user-username font-weight-bold" style="font-size: 130%;">
                                {{ $autoridadEjecutiva['partido'] }}</h3>
                            <h5 class="widget-user-desc text-yellow font-weight-bold" style="font-size: 105%;">
                                {{ $autoridadEjecutiva['cargo'] }}</h5>
                        </div>
                        <div class="widget-user-image">
                            @if ($autoridadEjecutiva['logo_url'])
                                <img class="img-circle elevation-2 bg-white"
                                    src="{{ asset('storage/'.$autoridadEjecutiva['logo_url']) }}" alt="Logo Partido"
                                    style="width: 90px; height: 90px; object-fit: contain; padding: 4px;">
                            @else
                                <div class="img-circle elevation-2 bg-secondary text-white text-center font-weight-bold"
                                    style="width: 90px; height: 90px; line-height: 90px; font-size: 24px;">A</div>
                            @endif
                        </div>
                        <div class="card-footer bg-white pt-5">
                            <div class="row border-top pt-3">
                                <div class="col-sm-6 border-right text-center">
                                    <div class="description-block">
                                        <h5 class="description-header font-weight-bold text-navy"
                                            style="font-size: 140%;">{{ number_format($autoridadEjecutiva['votos']) }}
                                        </h5>
                                        <span class="description-text text-muted text-xs uppercase">Votos
                                            Obtenidos</span>
                                    </div>
                                </div>
                                <div class="col-sm-6 text-center">
                                    <div class="description-block">
                                        <h5 class="description-header font-weight-bold text-success"
                                            style="font-size: 140%;">
                                            {{ number_format($autoridadEjecutiva['porcentaje'], 2) }}%</h5>
                                        <span class="description-text text-muted text-xs uppercase">Respaldo
                                            Ciudadano</span>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-success text-center font-weight-bold mt-4 mb-0 py-2"
                                style="font-size: 90%;">
                                <i class="fas fa-certificate mr-2"></i> Candidatura Proclamada por Mayoría Simple
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card card-light p-5 text-center text-muted border shadow-sm">
                        <i class="fas fa-user-slash fa-2x mb-2"></i> <br> No existen datos de votación procesados para
                        definir la autoridad ejecutiva.
                    </div>
                @endif
            </div>

            <!-- COLUMNA DERECHA: CUERPO LEGISLATIVO ELECTO (Consejeros o Regidores) -->
            <div class="col-md-7">
                <div class="card card-navy shadow">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-users-cog mr-2"></i> Distribución
                            Oficial del Cuerpo Colegiado</h3>
                    </div>
                    <div class="card-body p-0">
                        @if (empty($cuerpoColegiado))
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-th-list fa-2x mb-2 text-gray"></i> <br> Sin escaños distribuidos bajo
                                la fórmula matemática.
                            </div>
                        @else
                            <table class="table table-striped table-valign-middle m-0 table-bordered">
                                <thead class="bg-light text-xs text-muted">
                                    <tr class="text-center">
                                        <th class="text-left" style="width: 60%;">Organización Política</th>
                                        <th style="width: 40%;">Representantes / Escaños Obtenidos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cuerpoColegiado as $cc)
                                        <tr>
                                            <td class="align-middle py-3">
                                                @if ($cc['logo_url'])
                                                    <img src="{{ asset('storage/'.$cc['logo_url']) }}"
                                                        class="img-circle mr-2 border shadow-sm"
                                                        style="width:32px; height:32px; object-fit:contain;"
                                                        alt="">
                                                @endif
                                                <span
                                                    class="font-weight-bold text-dark text-md">{{ $cc['partido'] }}</span>
                                            </td>
                                            <td class="text-center align-middle bg-white">
                                                <!-- Contenedor del conteo de escaños y simulación de iconos visuales -->
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <span class="badge text-bg-success px-3 py-2 text-md font-weight-bold mr-3"
                                                        style="font-size: 110%; border-radius: 5px;">
                                                        <i class="fas fa-chair mr-1 text-yellow"></i>
                                                        {{ $cc['escanos'] }}
                                                        {{ Str::plural($cc['tipo_cargo'], $cc['escanos']) }}
                                                    </span>
                                                    <div class="d-none d-lg-inline-block text-warning"
                                                        style="opacity: 0.75;">
                                                        @for ($i = 0; $i < $cc['escanos']; $i++)
                                                            <i class="fas fa-user-tie text-sm mx-0.5"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- PANTALLA INFORMATIVA INICIAL / ESPERA DE FILTROS -->
        <div class="row mt-4" wire:key="pantalla-espera-autoridades">
            <div class="col-12">
                <div class="card card-light border p-5 text-center text-secondary shadow-sm"
                    style="background-color: #f8f9fa;">
                    <div class="p-4">
                        <i
                            class="fas fa-medal fa-4x text-muted mb-3 animate__animated animate__pulse animate__infinite"></i>
                        <h4 class="font-weight-bold text-dark">Cuadro de Mandatarios y Cuerpos Legislativos Electos
                        </h4>
                        <p class="text-muted text-sm mx-auto mb-4" style="max-width: 600px;">
                            Complete los criterios de selección de nivel territorial para desplegar la composición final
                            oficial del Gobierno Regional, Concejos Provinciales o Concejos Distritales:
                        </p>
                        <div class="d-flex justify-content-center text-xs text-left mx-auto"
                            style="max-width: 480px;">
                            <div class="bg-white p-3 border rounded shadow-xs w-100">
                                <span class="d-block mb-1"><strong><i class="fas fa-id-badge text-navy mr-1"></i>
                                        Regional:</strong> Muestra Gobernador + Consejeros por Región.</span>
                                <span class="d-block mb-1"><strong><i class="fas fa-city text-navy mr-1"></i>
                                        Provincial / Capital:</strong> Muestra Alcalde Provincial + Regidores de la
                                    Provincia.</span>
                                <span class="d-block"><strong><i class="fas fa-map-pin text-navy mr-1"></i> Distrital
                                        / Local:</strong> Muestra Alcalde Distrital + Regidores del Distrito.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
