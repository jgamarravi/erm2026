<div style="padding: 20px; font-family: sans-serif;">
    <h2 style="color: #e11d48; border-bottom: 2px solid #e11d48; padding-bottom: 8px; margin-bottom: 25px; font-weight: 700;">
        📊 Redacción y Saneamiento de Crónicas Radiales
    </h2>

    <div class="row">
        
        <!-- FORMULARIO DE EDICIÓN -->
        <div class="col-md-5 mb-4">
            <div class="card card-danger card-outline" style="background: white; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <form wire:submit.prevent="guardarNoticia">
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label style="font-weight: bold; font-size: 0.9em; color: #475569;">Titular de la Crónica:</label>
                            <input type="text" wire:model="titular" class="form-control">
                            @error('titular') <small style="color: red; font-weight: bold;">{{ $message }}</small> @enderror
                        </div>

                        <!-- INPUT NUEVO: Firma de Locutor / Periodista -->
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label style="font-weight: bold; font-size: 0.9em; color: #475569;">Firma de la Locutora / Redactor:</label>
                            <input type="text" wire:model="locutora_firma" placeholder="Ej: Lic. María Mendoza" class="form-control">
                            @error('locutora_firma') <small style="color: red; font-weight: bold;">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 12px;">
                            <label style="font-weight: bold; font-size: 0.9em; color: #475569;">Vincular Encuesta (Opcional):</label>
                            <select wire:model="encuesta_id" class="form-control">
                                <option value="">-- Ninguna --</option>
                                @foreach($encuestasDisponibles as $e) <option value="{{ $e->id }}">{{ $e->pregunta }}</option> @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 12px;">
                            <label style="font-weight: bold; font-size: 0.9em; color: #475569;">Cuerpo del Artículo:</label>
                            <textarea wire:model="contenido" rows="6" class="form-control" style="resize: none;"></textarea>
                            @error('contenido') <small style="color: red; font-weight: bold;">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: bold; font-size: 0.9em; color: #475569;">Imagen Destacada:</label>
                            <input type="file" wire:model="foto_destacada" accept="image/*" class="form-control-file">
                        </div>

                        <div style="display: flex; gap: 10px;">
                            @if($modoEdicion)
                                <button type="button" wire:click="limpiarFormulario" class="btn btn-secondary" style="flex: 1;">Cancelar</button>
                            @endif
                            <button type="submit" class="btn text-white" style="background-color: #e11d48; font-weight: bold; flex: 2;">
                                {{ $modoEdicion ? 'Actualizar Artículo' : 'Publicar Crónica' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- LISTADO DERECHO COMPACTO ACTUALIZADO CON BORRADO Y FIRMA -->
        <div class="col-md-7 mb-4">
            <div class="card card-outline card-danger" style="background: white; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div class="card-header" style="background-color: #fff;"><h3 class="card-title" style="margin: 0; font-weight: bold; font-size: 1.1em;">📰 Crónicas Emitidas en el Historial</h3></div>
                
                <div class="card-body" style="display: flex; flex-direction: column; gap: 15px; max-height: 75vh; overflow-y: auto;">
                    @forelse($noticiasHistoricas as $noticia)
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                            <div style="display: flex; gap: 12px; align-items: center; overflow: hidden; flex: 1;">
                                @if($noticia->imagen_path)
                                    <img src="{{ asset('storage/' . $noticia->imagen_path) }}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px;">
                                @else
                                    <div style="width: 45px; height: 45px; background: #e2e8f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.2em;">📻</div>
                                @endif
                                <div style="overflow: hidden;">
                                    <h5 style="margin: 0; font-size: 0.95em; font-weight: bold; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $noticia->titular }}</h5>
                                    <small style="color: #64748b; display: block; margin-top: 1px;">
                                        📅 {{ $noticia->created_at->format('d/m/Y') }} | ✍️ Por: <strong style="color: #475569;">{{ $noticia->locutora_firma ?? 'Locutor Central' }}</strong>
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Botones de Acción Agrupados -->
                            <div style="display: inline-flex; gap: 6px;">
                                <button wire:click="editarNoticia({{ $noticia->id }})" class="btn btn-xs btn-primary" style="font-weight: bold;">✏️</button>
                                <!-- NUEVO: Clic capturado por Alpine y derivado a SweetAlert2 -->
                                <button @click="$dispatch('confirmar-borrado-cronica-panel', { id: {{ $noticia->id }} })" class="btn btn-xs btn-danger" style="font-weight: bold;">🗑️</button>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; color: #888; padding: 20px; font-style: italic;">No se registran crónicas periodísticas redactadas.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
    @livewireScripts
    <script>
        window.addEventListener('confirmar-borrado-cronica-panel', event => {
            Swal.fire({
                title: '¿Eliminar esta crónica?',
                text: "El artículo e imagen destacados desaparecerán de forma permanente del Home público de la radio.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    Livewire.dispatch('ejecutar-borrado-cronica', {
                        id: event.detail.id
                    });
                }
            });
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('notificar-estadistica', (event) => {
                const datos = Array.isArray(event) ? event : event;
                Swal.fire({
                    title: 'Hecho',
                    text: datos.mensaje,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        });
    </script>
</div>

