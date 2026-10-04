<div>
    <div style="padding: 15px; font-family: sans-serif;">

        <!-- CARD DE BÚSQUEDA DE MESA -->
        <div class="card card-danger card-outline no-print"
            style="background: white; border-radius: 6px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 15px;">
            <div style="display: flex; gap: 15px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label
                        style="font-weight: bold; font-size: 0.9em; color: #475569; display: block; margin-bottom: 4px;">Ingrese
                        el Número de Mesa de Sufragio:</label>
                    <input type="text" wire:model="numeroMesaBuscar" placeholder="Ej: 087654" class="form-control"
                        style="border-radius: 4px;">
                </div>
                <button wire:click="buscarMesa" class="btn btn-danger"
                    style="background-color: #e11d48; font-weight: bold; padding: 7px 25px; border-radius: 4px;">
                    🔍 Consultar Mesa
                </button>
            </div>
            @error('numeroMesaBuscar')
                <small style="color: red; font-weight: bold; display: block; margin-top: 5px;">⚠️
                    {{ $message }}</small>
            @enderror
        </div>

        <!-- CUADRO DE CAPTURA COMPUESTO -->
        @if ($mesaActual)
            <div class="card card-danger"
                style="background: white; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #e11d48;">
                <div class="card-header"
                    style="background: #fff; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee;">
                    <div>
                        <h4 style="margin: 0; font-weight: bold; color: #1e293b;">📋 Registro de Cómputo: Mesa N°
                            {{ $mesaActual->numero_mesa }}</h4>
                        <small style="color: #64748b;">📍 Ubicación: {{ $mesaActual->centroVotacion->distrito->nombre }}
                            —
                            {{ $mesaActual->centroVotacion->nombre }}</small>
                    </div>
                    <div style="width: 160px;">
                        <label style="font-size: 0.8em; font-weight: bold; display: block; margin-bottom: 2px;">Total
                            Firmas
                            en Padrón:</label>
                        <input type="number" min="0" @disabled($bloqueado)
                            wire:model="total_votantes_acta" class="form-control text-center font-weight-bold"
                            style="border: 2px solid #000; font-size: 1.1em; height: 34px;">
                    </div>
                </div>

                <div class="card-body">
                    <!-- ENCABEZADOS DE LAS 4 COLUMNAS PARALELAS -->
                    <div
                        style="display: grid; grid-template-columns: 2fr 100px 100px 100px {{ !$esDistritoCapital ? '100px' : '' }}; gap: 12px; font-weight: bold; border-bottom: 2px solid #dee2e6; padding-bottom: 8px; font-size: 0.82em; text-align: center; margin-bottom: 10px;">
                        <div style="text-align: left; padding-left: 5px;">Organización Política</div>
                        <div style="color: #0056b3; background: #e6f0fa; border-radius: 4px; padding: 2px;">🔵
                            GOBERNADOR
                        </div>
                        <div style="color: #6f42c1; background: #f3e5f5; border-radius: 4px; padding: 2px;">🟣 CONSEJERO
                        </div>
                        <div style="color: #495057; background: #f1f3f5; border-radius: 4px; padding: 2px;">🏢
                            PROVINCIAL
                        </div>
                        @if (!$esDistritoCapital)
                            <div style="color: #28a745; background: #e8f5e9; border-radius: 4px; padding: 2px;">🌳
                                DISTRITAL
                            </div>
                        @endif
                    </div>

                    <!-- FORMULARIO CORREGIDO MEDIANTE ARRAY_KEYS -->
                    <div
                        style="display: flex; flex-direction: column; gap: 6px; max-height: 42vh; overflow-y: auto; padding-right: 5px;">
                        @foreach (array_keys($votosGobernador) as $pId)
                            @php $partido = \App\Models\Admin\PartidoPolitico::find($pId); @endphp
                            @if ($partido)
                                <div
                                    style="display: grid; grid-template-columns: 2fr 100px 100px 100px {{ !$esDistritoCapital ? '100px' : '' }}; gap: 12px; align-items: center; background: #f8fafc; padding: 6px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                    <span
                                        style="font-weight: 600; font-size: 0.88em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-left: 5px;">{{ $partido->siglas }}</span>
                                    <input type="number" min="0" @disabled($bloqueado)
                                        wire:model="votosGobernador.{{ $pId }}"
                                        class="form-control text-center form-control-sm"
                                        style="border: 1px solid #0056b3;">
                                    <input type="number" min="0" @disabled($bloqueado)
                                        wire:model="votosConsejero.{{ $pId }}"
                                        class="form-control text-center form-control-sm"
                                        style="border: 1px solid #6f42c1;">
                                    <input type="number" min="0" @disabled($bloqueado)
                                        wire:model="votosProvincial.{{ $pId }}"
                                        class="form-control text-center form-control-sm"
                                        style="border: 1px solid #495057;">
                                    @if (!$esDistritoCapital)
                                        <input type="number" min="0" @disabled($bloqueado)
                                            wire:model="votosDistrital.{{ $pId }}"
                                            class="form-control text-center form-control-sm"
                                            style="border: 1px solid #28a745;">
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- SUB-PANEL: CASILLEROS DE VOTOS BLANCOS Y NULOS POR AUTORIDAD -->
                    <div
                        style="margin-top: 15px; padding: 12px; background: #fff3cd; border-radius: 6px; border: 1px solid #ffeeba; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; font-size: 0.8em;">
                        <div>
                            <small style="font-weight: bold; color: #0056b3; display: block; margin-bottom: 2px;">🔵
                                GOBERNADOR (B / N):</small>
                            <div style="display: flex; gap: 4px;">
                                <input type="number" min="0" @disabled($bloqueado)
                                    wire:model="blancos_gobernador" class="form-control form-control-sm text-center">
                                <input type="number" min="0" @disabled($bloqueado)
                                    wire:model="nulos_gobernador" class="form-control form-control-sm text-center">
                            </div>
                        </div>
                        <div>
                            <small style="font-weight: bold; color: #6f42c1; display: block; margin-bottom: 2px;">🟣
                                CONSEJERO (B / N):</small>
                            <div style="display: flex; gap: 4px;">
                                <input type="number" min="0" @disabled($bloqueado)
                                    wire:model="blancos_consejero" class="form-control form-control-sm text-center">
                                <input type="number" min="0" @disabled($bloqueado)
                                    wire:model="nulos_consejero" class="form-control form-control-sm text-center">
                            </div>
                        </div>
                        <div>
                            <small style="font-weight: bold; color: #495057; display: block; margin-bottom: 2px;">🏢
                                PROVINCIAL (B / N):</small>
                            <div style="display: flex; gap: 4px;">
                                <input type="number" min="0" @disabled($bloqueado)
                                    wire:model="blancos_provincial" class="form-control form-control-sm text-center">
                                <input type="number" min="0" @disabled($bloqueado)
                                    wire:model="nulos_provincial" class="form-control form-control-sm text-center">
                            </div>
                        </div>
                        @if (!$esDistritoCapital)
                            <div>
                                <small style="font-weight: bold; color: #28a745; display: block; margin-bottom: 2px;">🌳
                                    DISTRITAL (B / N):</small>
                                <div style="display: flex; gap: 4px;">
                                    <input type="number" min="0" @disabled($bloqueado)
                                        wire:model="blancos_distrital" class="form-control form-control-sm text-center">
                                    <input type="number" min="0" @disabled($bloqueado)
                                        wire:model="nulos_distrital" class="form-control form-control-sm text-center">
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- ACCIONES FINALES, CLASIFICACIÓN DE ACTA Y VALIDACIÓN -->
                    <div
                        style="margin-top: 20px; display: grid; grid-template-columns: 1.1fr 160px auto; gap: 15px; align-items: flex-end;">
                        <div>
                            @error('total_votantes_acta')
                                <div
                                    style="color: #dc3545; font-weight: bold; font-size: 0.88em; margin-bottom: 5px; background: #f8d7da; padding: 6px; border-radius: 4px; border: 1px solid #f5c6cb;">
                                    ⚠️ {{ $message }}</div>
                            @enderror
                            <label
                                style="font-size: 0.85em; font-weight: bold; display: block; margin-bottom: 4px;">Observaciones
                                del Acta:</label>
                            <input type="text" wire:model="observaciones" @disabled($bloqueado)
                                placeholder="Anote observaciones del escrutinio..."
                                class="form-control form-control-sm">
                        </div>

                        <!-- NUEVO: Selector de Estado para el Digitador -->
                        <div>
                            <label
                                style="font-size: 0.85em; font-weight: bold; display: block; margin-bottom: 4px;">Estado
                                del Acta:</label>
                            <select wire:model="estado" @disabled($bloqueado)
                                class="form-control form-control-sm font-weight-bold">
                                <option value="procesada">🟢 PROCESADA (Sana)</option>
                                <option value="observada">⚠️ OBSERVADA</option>
                                <option value="impugnada">🚨 IMPUGNADA</option>
                            </select>
                        </div>

                        <div>
                            @if (!$bloqueado)
                                <button wire:click="guardarActa" class="btn btn-primary"
                                    style="padding: 10px 25px; font-weight: bold; text-transform: uppercase; font-size: 0.85em;">
                                    💾 Guardar Cómputo Completo
                                </button>
                            @else
                                🔒 ACTA CERRADA (ONPE)
                            @endif
        @endif
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('notificar-escrutinio', (event) => {
                    const datos = Array.isArray(event) ? event : event;
                    Swal.fire({
                        title: 'Guardado',
                        text: datos.mensaje,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            });
        </script>
    </div>
</div>
