<div class="container-fluid mt-3">
    <!-- PANEL DE BÚSQUEDA INTELIGENTE CON SUGERENCIAS ASÍNCRONAS (D-PRINT-NONE) -->
    <div class="card p-3 shadow-sm mb-3 bg-light border-0 d-print-none position-relative" style="z-index: 1050;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-bold m-0 text-dark">🖥️ Registro y Control de Actas</h6>
                <small class="text-muted" style="font-size: 0.75rem;">Digite el número de mesa o el nombre del Local de
                    Votación</small>
            </div>

            <div class="position-relative" style="width: 100%; max-width: 480px;">
                <div class="input-group">
                    <span class="input-group-text py-1 small bg-dark text-white border-dark">🔍 Mesa--></span>
                    <input type="text" class="form-control form-control-sm text-center fs-6"
                        placeholder="Escriba aquí para buscar sugerencias..."
                        wire:model.live.debounce.250ms="busqueda_mesa" autocomplete="off">
                    <button class="btn btn-primary btn-sm px-3 fw-bold" wire:click="buscarMesa">Cargar</button>
                </div>

                <!-- DESPLEGABLE FLOTANTE DE AYUDA PREDICTIVA -->
                @if (!empty($sugerencias_mesas))
                    <div class="position-absolute w-100 bg-white shadow rounded-3 border mt-1 overflow-hidden"
                        style="left: 0; top: 100%;">
                        <div class="list-group list-group-flush">
                            @foreach ($sugerencias_mesas as $sug)
                                <button type="button"
                                    class="list-group-item list-group-item-action p-2 text-start small border-bottom"
                                    wire:click="seleccionarMesaSugerida('{{ $sug->numero_mesa }}')">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-black text-primary font-monospace fs-6">📌 Mesa N°
                                            {{ $sug->numero_mesa }}</span>

                                        <!-- Semáforo de Estado en la Ayuda Predictiva -->
                                        @if ($sug->estado_acta === 'COMPUTADA')
                                            <span class="badge bg-secondary font-monospace"
                                                style="font-size: 0.65rem;">🔒 CERRADA</span>
                                        @elseif($sug->estado_acta === 'OBSERVADA')
                                            <span class="badge bg-danger font-monospace" style="font-size: 0.65rem;">⚠️
                                                OBSERVADA</span>
                                        @else
                                            <span class="badge bg-success font-monospace" style="font-size: 0.65rem;">🔓
                                                ABIERTA</span>
                                        @endif
                                    </div>
                                    <div class="text-muted text-uppercase text-truncate"
                                        style="font-size: 0.72rem; max-width: 95%;">
                                        <strong>Distrito:</strong> {{ $sug->distrito_nombre }} | <strong>Local:</strong>
                                        {{ $sug->local_nombre }}
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @if (session()->has('error'))
            <div class="text-danger mt-1 fw-bold small ps-1">❌ {{ session('error') }}</div>
        @endif
    </div>


    @if ($mesa)
        <div class="card p-3 shadow-sm border border-secondary">
            @if (session()->has('message'))
                <div class="alert alert-success fw-bold py-2 mb-2">✅ {{ session('message') }}</div>
            @endif

            <!-- INDICADOR ESTÉTICO DE ESTADO CERRADO -->
            @if ($acta_bloqueada)
                <div
                    class="alert alert-dark fw-bold py-2 mb-2 d-flex justify-content-between align-items-center border-start border-4 border-dark shadow-sm">
                    <span>🔒 ACTA COMPUTADA Y CERRADA: La información se encuentra en modo de solo lectura para
                        fiscalización oficial.</span>
                    <!-- BOTÓN DE DESBLOQUEO PARA ADMINISTRACIÓN -->
                    @can('unlock actas')
                        <button class="btn btn-danger btn-sm fw-bold px-3 shadow-sm" wire:click="desbloquearActa"
                            wire:confirm="¿Desea reabrir esta acta? Se requerirá volver a validar la consistencia numérica.">
                            🔓 Acta
                        </button>
                    @endcan
                </div>
            @endif

            <!-- PANEL DE ASISTENCIA OSCURO -->
            <div class="row g-2 align-items-center mb-3 p-3 bg-dark text-white rounded">
                <div class="col-sm-3">
                    <span class="small text-uppercase d-block fw-bold" style="font-size: 0.72rem;">Mesa
                        Seleccionada</span>
                    <span class="fw-bold text-warning fs-4 d-block font-monospace">N° {{ $numero_mesa }}</span>
                </div>
                <div class="col-sm-3">
                    <span class="small text-uppercase d-block fw-bold" style="font-size: 0.72rem;">Hábiles</span>
                    <span class="fw-bold text-light fs-4 d-block font-monospace">{{ $mesa->electores_habiles }}
                </div>
                <div class="col-sm-3">
                    <label class="small text-warning fw-bold text-uppercase m-0 d-block mb-1"
                        style="font-size: 0.72rem;">Personas que Votaron:</label>
                    <!-- BLOQUEADO SI ACTA ESTÁ CERRADA -->
                    <input type="number"
                        class="form-control form-control-sm text-center fw-bold border-warning bg-white text-dark fs-5"
                        style="max-width: 140px;" wire:model.live="personas_votaron"
                        {{ $acta_bloqueada ? 'disabled' : '' }}>
                </div>
                <!-- COLUMNA DEL BOTÓN: REDISEÑADA A UN TAMAÑO COMPACTO Y CENTRADO -->
                           <div class="col-sm-3 d-flex align-items-center justify-content-end">
                @if(!$acta_bloqueada)
                    <!-- BOTÓN CORREGIDO: TAMAÑO COMPACTO Y TEXTO DE INTEGRIDAD ELECTORAL -->
                    <button type="button" class="btn btn-success btn-sm fw-bold px-4 py-2 shadow-sm text-uppercase" 
                            wire:click="guardarActa">
                        💾 Guardar / Cerrar Acta
                    </button>
                @else
                    <button type="button" class="btn btn-secondary btn-sm fw-bold px-4 py-2 shadow-sm text-uppercase" disabled>
                        🔒 Acta Archivada
                    </button>
                @endif
            </div>

                <div class="col-sm-6">
                    <span class="small text-uppercase text-info d-block fw-bold"
                        style="font-size: 0.7rem;">{{ $mesa->centro_nombre }}/{{ $mesa->distrito_nombre }}</span>
                    <span class="fw-bold text-light fs-6 d-block font-monospace">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered align-middle m-0">
                    <thead class="table-secondary text-center">
                        <tr class="align-middle">
                            <th rowspan="2" class="bg-light text-start text-dark py-3">Partido Político</th>
                            <th colspan="2" class="bg-primary text-white py-2">CÉDULA REGIONAL</th>
                            <th colspan="2" class="bg-info text-dark py-2">CÉDULA MUNICIPAL</th>
                        </tr>
                        <tr class="small text-uppercase fw-bold">
                            <th class="table-primary text-primary" width="16%">Gobernador</th>
                            <th class="table-primary text-primary" width="16%">Consejero</th>
                            <th class="table-info text-info" width="16%">Provincial</th>
                            <th class="table-info text-info" width="16%">Distrital</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($partidos_maestros as $partido)
                            <tr>
                                <td class="fw-bold bg-white text-secondary py-2 partido-celda">
                                    <img src="{{ asset('storage/' . ($partido->logo_url ?? 'default.png')) }}"
                                        class="partido-logo">
                                    <span class="partido-nombre">{{ $partido->nombre }}</span>
                                </td>

                                <!-- GOBERNADOR -->
                                <td>
                                    @if (is_null($votos_g[$partido->id]))
                                        <span class="text-muted small d-block py-1 bg-light text-center">No
                                            Postula</span>
                                    @else
                                        <input type="number" class="form-control text-center"
                                            wire:model.live="votos_g.{{ $partido->id }}" min="0"
                                            {{ $acta_bloqueada ? 'disabled' : '' }}>
                                    @endif
                                </td>
                                <!-- CONSEJERO -->
                                <td>
                                    @if (is_null($votos_c[$partido->id]))
                                        <span class="text-muted small d-block py-1 bg-light text-center">No
                                            Postula</span>
                                    @else
                                        <input type="number" class="form-control text-center"
                                            wire:model.live="votos_c.{{ $partido->id }}" min="0"
                                            {{ $acta_bloqueada ? 'disabled' : '' }}>
                                    @endif
                                </td>
                                <!-- PROVINCIAL -->
                                <td>
                                    @if (is_null($votos_p[$partido->id]))
                                        <span class="text-muted small d-block py-1 bg-light text-center">No
                                            Postula</span>
                                    @else
                                        <input type="number" class="form-control text-center"
                                            wire:model.live="votos_p.{{ $partido->id }}" min="0"
                                            {{ $acta_bloqueada ? 'disabled' : '' }}>
                                    @endif
                                </td>
                                <!-- DISTRITAL -->
                                <td>
                                    @if (is_null($votos_d[$partido->id]))
                                        <span class="text-muted small d-block py-1 bg-light text-center">No
                                            Postula</span>
                                    @else
                                        <input type="number" class="form-control text-center"
                                            wire:model.live="votos_d.{{ $partido->id }}" min="0"
                                            {{ $acta_bloqueada ? 'disabled' : '' }}>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        <!-- Votos Especiales (Blancos) -->
                        <tr class="table-warning border-top border-dark">
                            <td class="fw-bold text-success text-uppercase py-2">⬜ VOTOS EN BLANCO</td>
                            <td><input type="number" class="form-control text-center fw-bold"
                                    wire:model.live="blancos.GOBERNADOR" min="0"
                                    {{ $acta_bloqueada || !$col_g_activa ? 'disabled' : '' }}></td>
                            <td><input type="number" class="form-control text-center fw-bold"
                                    wire:model.live="blancos.CONSEJERO" min="0"
                                    {{ $acta_bloqueada || !$col_c_activa ? 'disabled' : '' }}></td>
                            <td><input type="number" class="form-control text-center fw-bold"
                                    wire:model.live="blancos.PROVINCIAL" min="0"
                                    {{ $acta_bloqueada || !$col_p_activa ? 'disabled' : '' }}></td>
                            <td><input type="number" class="form-control text-center fw-bold"
                                    wire:model.live="blancos.DISTRITAL" min="0"
                                    {{ $acta_bloqueada || !$col_d_activa ? 'disabled' : '' }}></td>
                        </tr>
                        <!-- Votos Especiales (Nulos) -->
                        <tr class="table-danger">
                            <td class="fw-bold text-danger text-uppercase py-2">💥 VOTOS NULOS / VICIADOS</td>
                            <td><input type="number" class="form-control text-center fw-bold"
                                    wire:model.live="nulos.GOBERNADOR" min="0"
                                    {{ $acta_bloqueada || !$col_g_activa ? 'disabled' : '' }}></td>
                            <td><input type="number" class="form-control text-center fw-bold"
                                    wire:model.live="nulos.CONSEJERO" min="0"
                                    {{ $acta_bloqueada || !$col_c_activa ? 'disabled' : '' }}></td>
                            <td><input type="number" class="form-control text-center fw-bold"
                                    wire:model.live="nulos.PROVINCIAL" min="0"
                                    {{ $acta_bloqueada || !$col_p_activa ? 'disabled' : '' }}></td>
                            <td><input type="number" class="form-control text-center fw-bold"
                                    wire:model.live="nulos.DISTRITAL" min="0"
                                    {{ $acta_bloqueada || !$col_d_activa ? 'disabled' : '' }}></td>
                        </tr>


                        <!-- INDICADOR DINÁMICO DE TOTALES -->
                        <tr class="table-dark fs-5 text-center">
                            <td class="text-start fw-bold text-warning py-3">📊 SUMA ACTUAL DIGITADA:</td>

                            <!-- GOBERNADOR -->
                            <td
                                class="fw-bold {{ $col_g_activa ? (!empty($personas_votaron) && $total_g == $personas_votaron ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                {{ $col_g_activa ? $total_g : '-' }} <br>
                                <!-- Reemplazo para la columna GOBERNADOR -->
                                <small class="fs-6 d-block">
                                    @if (!empty($personas_votaron))
                                        @if ($total_g == (int) $personas_votaron)
                                            ✔️ Cuadrado
                                        @elseif($total_g > (int) $personas_votaron)
                                            🚨 Excede por {{ $total_g - (int) $personas_votaron }}
                                        @else
                                            ❌ Falta {{ (int) $personas_votaron - $total_g }}
                                        @endif
                                    @else
                                        Falta Asistencia
                                    @endif
                                </small>

                            </td>

                            <!-- CONSEJERO -->
                            <td
                                class="fw-bold {{ $col_c_activa ? (!empty($personas_votaron) && $total_c == $personas_votaron ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                {{ $col_c_activa ? $total_c : '-' }} <br>
                                <!-- Reemplazo para la columna GOBERNADOR -->
                                <small class="fs-6 d-block">
                                    @if (!empty($personas_votaron))
                                        @if ($total_c == (int) $personas_votaron)
                                            ✔️ Cuadrado
                                        @elseif($total_c > (int) $personas_votaron)
                                            🚨 Excede por {{ $total_c - (int) $personas_votaron }}
                                        @else
                                            ❌ Falta {{ (int) $personas_votaron - $total_c }}
                                        @endif
                                    @else
                                        Falta Asistencia
                                    @endif
                                </small>

                            </td>

                            <!-- PROVINCIAL -->
                            <td
                                class="fw-bold {{ $col_p_activa ? (!empty($personas_votaron) && $total_p == $personas_votaron ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                {{ $col_p_activa ? $total_p : '-' }} <br>
                                <!-- Reemplazo para la columna GOBERNADOR -->
                                <small class="fs-6 d-block">
                                    @if (!empty($personas_votaron))
                                        @if ($total_p == (int) $personas_votaron)
                                            ✔️ Cuadrado
                                        @elseif($total_p > (int) $personas_votaron)
                                            🚨 Excede por {{ $total_p - (int) $personas_votaron }}
                                        @else
                                            ❌ Falta {{ (int) $personas_votaron - $total_p }}
                                        @endif
                                    @else
                                        Falta Asistencia
                                    @endif
                                </small>

                            </td>

                            <!-- DISTRITAL -->
                            <td
                                class="fw-bold {{ $col_d_activa ? (!empty($personas_votaron) && $total_d == $personas_votaron ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                {{ $col_d_activa ? $total_d : '-' }} <br>
                                <!-- Reemplazo para la columna GOBERNADOR -->
                                <small class="fs-6 d-block">
                                    @if (!empty($personas_votaron))
                                        @if ($total_d == (int) $personas_votaron)
                                            ✔️ Cuadrado
                                        @elseif($total_d > (int) $personas_votaron)
                                            🚨 Excede por {{ $total_d - (int) $personas_votaron }}
                                        @else
                                            ❌ Falta {{ (int) $personas_votaron - $total_d }}
                                        @endif
                                    @else
                                        Falta Asistencia
                                    @endif
                                </small>

                            </td>
                        </tr>

                    </tbody>

                </table>
            </div>
        </div>
    @endif
    <style>
        /* 1. Este contenedor obliga a sus hijos (img y span) a ponerse en línea */
        .partido-celda {
            display: flex;
            /* Activa el modo en línea/flex */
            align-items: center;
            /* Centra verticalmente la imagen y el texto */
            gap: 8px;
            /* Separa el logo del texto */
        }

        /* 2. Este estilo evita que la imagen deforme la fila */
        .partido-logo {
            width: 24px;
            /* Ancho fijo para el logo */
            height: 24px;
            /* Alto fijo para el logo */
            object-fit: contain;
            /* Mantiene la proporción sin estirarse */
            display: block;
            /* Elimina espacios fantasmas debajo de la imagen */
        }
    </style>
</div>
