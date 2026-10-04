<div>
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="text-dark"><i class="bi bi-calculator-fill me-2"></i>Cálculo de Cifra Repartidora Oficial - ERM
                2026</h4>
        </div>
    </div>

    <!-- Filtros de Jurisdicción Inteligentes Segmentados -->
    <div class="card card-dark shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <!-- Select 1: Tipo de Elección Oficial -->
                <div class="col-md-3 mb-2">
                    <label class="form-label fw-bold">Tipo de Proceso:</label>
                    <select wire:model.live="tipo_reporte" class="form-select font-weight-bold text-primary">
                        <option value="GOBERNADOR">GOBERNADOR (D'Hondt Puro)</option>
                        <option value="CONSEJERO">CONSEJERO REGIONAL (D'Hondt Puro)</option>
                        <option value="ALCALDE_PROVINCIAL">ALCALDE PROVINCIAL (Premio Mayoría)</option>
                        <option value="ALCALDE_DISTRITAL">ALCALDE DISTRITAL (Premio Mayoría)</option>
                    </select>
                </div>

                <!-- Select 2: Región (Habilitado para todos los procesos) -->
                <div class="col-md-3 mb-2">
                    <label class="form-label fw-bold">Región / Circunscripción:</label>
                    <select wire:model.live="selectedDep" class="form-select">
                        <option value="">-- Seleccione Región --</option>
                        @foreach ($departamentos as $dep)
                            <option value="{{ $dep['id'] }}">{{ $dep['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select 3: Provincia (Se bloquea únicamente si es Gobernador) -->
                <div class="col-md-3 mb-2">
                    <label class="form-label fw-bold">Provincia:</label>
                    <select wire:model.live="selectedProv" class="form-select"
                        {{ $tipo_reporte === 'GOBERNADOR' || empty($provincias) ? 'disabled' : '' }}>
                        <option value="">--
                            {{ $tipo_reporte === 'GOBERNADOR' ? 'No Requerido' : 'Seleccione Provincia' }} --</option>
                        @foreach ($provincias as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select 4: Distrito (Sólo se activa para Alcalde Distrital) -->
                <div class="col-md-3 mb-2">
                    <label class="form-label fw-bold">Distrito:</label>
                    <select wire:model.live="selectedDist" class="form-select"
                        {{ $tipo_reporte !== 'ALCALDE_DISTRITAL' || empty($distritos) ? 'disabled' : '' }}>
                        <option value="">--
                            {{ $tipo_reporte !== 'ALCALDE_DISTRITAL' ? 'No Requerido' : 'Seleccione Distrito' }} --
                        </option>
                        @foreach ($distritos as $dist)
                            <option value="{{ $dist->id }}">{{ $dist->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Resultados con Adjudicación de Curules -->
    <div class="row">
        <div class="col-12">
            @if (!empty($resultados))
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="card-title m-0 fw-bold text-success">
                            <i class="bi bi-award-fill me-2"></i>
                            @if (in_array($tipo_reporte, ['GOBERNADOR', 'CONSEJERO']))
                                Escaños Adjudicados al Consejo (Total: {{ $escañosAEliger }})
                            @else
                                Regidurías del Concejo Municipal (Total: {{ $escañosAEliger }})
                            @endif
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Añadimos de forma estricta el formato de descarga nativa de Livewire -->
                            <button type="button" wire:click="exportarActaDHondt" wire:loading.attr="disabled"
                                class="btn btn-sm btn-success fw-bold shadow-sm d-flex align-items-center gap-1">
                                <i class="bi bi-file-earmark-excel-fill"></i>
                                <span wire:loading.remove wire:target="exportarActaDHondt">Exportar Acta a Excel</span>
                                <span wire:loading wire:target="exportarActaDHondt">Generando Archivo...</span>
                            </button>


                            <span class="badge bg-dark font-weight-bold p-2">VOTOS VÁLIDOS NETOS</span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped m-0 align-middle text-center">
                                <thead class="table-success">
                                    <tr>
                                        <th>Posición</th>
                                        <th class="text-start">Organización Política</th>
                                        <th>Votos Obtenidos</th>
                                        <th>Porcentaje Válido (%)</th>
                                        <th>Escaños/Regidurías</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalVotosValidos = array_sum(array_column($resultados, 'votos')); @endphp
                                    @foreach ($resultados as $indice => $res)
                                        <tr>
                                            <td class="fw-bold text-muted">#{{ $indice + 1 }}</td>
                                            <td class="text-start">
                                                <span class="badge bg-dark me-2">{{ $res['siglas'] }}</span>
                                                <span class="fw-bold">{{ $res['nombre'] }}</span>
                                            </td>
                                            <td class="fw-bold text-primary">{{ number_format($res['votos']) }}</td>
                                            <td>{{ $totalVotosValidos > 0 ? round(($res['votos'] / $totalVotosValidos) * 100, 2) : 0 }}%
                                            </td>
                                            <td>
                                                @if ($res['escaños'] > 0)
                                                    <span
                                                        class="badge bg-success fs-5 px-3 py-2 animate__animated animate__bounceIn">
                                                        <i class="bi bi-person-fill me-1"></i> {{ $res['escaños'] }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted fs-6">0</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info text-center border-0 py-4 shadow-sm">
                    <i class="bi bi-grid-3x3-gap-fill display-6 d-block mb-2 text-primary"></i>
                    Filtre la circunscripción completa según el nivel del cargo para procesar el cálculo automático de
                    la Cifra Repartidora.
                </div>
            @endif
        </div>
    </div>
</div>
