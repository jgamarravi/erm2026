<div class="row" style="padding: 10px;">

    <!-- COLUMNA IZQUIERDA: FORMULARIO AL AIRE (4 de 12 espacios) -->
    <div class="col-md-4 mb-4">
        <div class="card card-danger card-outline"
            style="background: white; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div class="card-header" style="background-color: #fff; border-bottom: 1px solid #eee;">
                <h3 class="card-title" style="margin: 0; color: #333; font-weight: bold; font-size: 1.1em;">🎙️ Lanzar
                    Pregunta al Aire</h3>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardarEncuesta">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: bold; font-size: 0.9em; color: #475569;">Escriba la Pregunta:</label>
                        <input type="text" wire:model="nuevaPregunta"
                            placeholder="Ej: ¿Qué opina sobre las calles de Huacho?" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: bold; font-size: 0.9em; color: #475569;">Mensaje de Bienvenida en el
                            Home (Rotativo):</label>
                        <textarea wire:model="mensajeBienvenida" rows="2"
                            placeholder="Ej: ¡Un saludo especial a todos nuestros oyentes de Hualmay y Carquín!" class="form-control"
                            style="resize: none;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: bold; font-size: 0.9em; color: #475569;">Alternativas (Una por
                            línea):</label>
                        <textarea wire:model="opcionesTexto" rows="5" class="form-control" style="resize: none;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-block"
                        style="background-color: #e11d48; color: white; font-weight: bold;">🚀 Publicar en la
                        Radio</button>
                </form>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: HISTORIAL FILTRADO (8 de 12 espacios) -->
    <div class="col-md-8 mb-4">
        <div class="card card-outline card-danger"
            style="background: white; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

            <!-- NUEVA BARRA DE FILTROS DE FECHAS -->
            <div class="card-header"
                style="background-color: #fff; border-bottom: 1px solid #eee; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px;">
                <h3 class="card-title" style="margin: 0; color: #333; font-weight: bold; font-size: 1.1em;">📊 Historial
                    de Encuestas Radiales</h3>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="date" wire:model.live="fechaInicio" class="form-control form-control-sm"
                        style="width: 135px;" title="Fecha Inicio">
                    <span style="color:#777;">al</span>
                    <input type="date" wire:model.live="fechaFin" class="form-control form-control-sm"
                        style="width: 135px;" title="Fecha Fin">
                </div>
            </div>

            <div class="card-body p-0" style="overflow-x: auto;">
                <table class="table table-bordered table-striped" style="width: 100%; margin-bottom: 0;">
                    <thead>
                        <tr style="background-color: #f8fafc; color: #334155;">
                            <th style="padding: 12px; width: 60px;">ID</th>
                            <th style="padding: 12px;">Pregunta al Aire</th>
                            <th style="padding: 12px; text-align: center; width: 100px;">Estado</th>
                            <th style="padding: 12px; text-align: center; width: 200px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($encuestas as $enc)
                            <tr>
                                <td style="padding: 12px; vertical-align: middle;">{{ $enc->id }}</td>
                                <td style="padding: 12px; font-weight: bold; vertical-align: middle; color: #1e293b;">
                                    {{ $enc->pregunta }}
                                    <small
                                        style="display: block; color: #64748b; font-weight: normal; margin-top: 2px;">
                                        📅 Creado el: {{ $enc->created_at->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td style="padding: 12px; text-align: center; vertical-align: middle;">
                                    <span class="badge {{ $enc->activa ? 'badge-success' : 'badge-secondary' }}"
                                        style="padding: 5px 8px;">
                                        {{ $enc->activa ? 'ACTIVA' : 'CERRADA' }}
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center; vertical-align: middle;">
                                    <div style="display: inline-flex; gap: 5px;">
                                        <!-- Exportar Excel -->
                                        <button wire:click="exportarExcel({{ $enc->id }})" wire:download
                                            class="btn btn-sm btn-success"
                                            style="font-weight: bold; border-radius: 4px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 4px;">
                                            📊 Excel
                                        </button>
                                        <!-- NUEVO BOTÓN: Eliminar Encuesta Masiva -->
                                        <button
                                            @click="$dispatch('confirmar-borrado-encuesta', { id: {{ $enc->id }} })"
                                            class="btn btn-sm btn-danger" title="Eliminar Registro">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 30px; text-align: center; color: #888;">
                                    No se encontraron encuestas registradas para el rango de fechas seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="card-footer clearfix" style="background: white; border-top: 1px solid #eee;">
                {{ $encuestas->links() }}
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT ADAPTADO PARA CAPTURAR EVENTOS CON SWEETALERT2 -->

<script>
    // Interceptamos la orden de eliminación del DOM
    window.addEventListener('confirmar-borrado-encuesta', event => {
        Swal.fire({
            title: '¿Deseas eliminar esta encuesta?',
            text: "Se borrarán de forma definitiva la pregunta, sus alternativas y todos los votos acumulados de los oyentes.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, borrar permanentemente',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                Livewire.dispatch('eliminar-encuesta-confirmado', {
                    id: event.detail.id
                });
            }
        });
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('notificar-auditoria', (event) => {
            const datos = Array.isArray(event) ? event : event;
            Swal.fire({
                title: 'Procesado',
                text: datos.mensaje,
                icon: 'success',
                timer: 2200,
                showConfirmButton: false
            });
        });
    });
</script>
