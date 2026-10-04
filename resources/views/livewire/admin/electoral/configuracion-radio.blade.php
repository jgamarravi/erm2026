<div class="card card-outline card-danger"
    style="background: white; border-radius: 6px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 650px; margin: 10px; border-top: 3px solid #e11d48;">
    <div class="card-header"
        style="background: #fff; border-bottom: 1px solid #eee; margin-bottom: 15px; padding-left: 0;">
        <h3 class="card-title" style="margin:0; font-weight:bold; color:#333; font-size: 1.15em;">
            📻 Ajustes Editoriales de Portada - Radio Stereo 92
        </h3>
    </div>

    <div class="card-body" style="padding: 0;">
        <p style="font-size: 0.85em; color: #64748b; margin-bottom: 20px;">
            Redacte el mensaje de bienvenida y adjunte un banner publicitario o promocional. Todo se actualizará de
            inmediato en la columna izquierda del frente público de Huacho de manera automatizada.
        </p>

        <form wire:submit.prevent="guardarConfiguracion">

            <!-- Campo 1: Redacción del Mensaje Libre -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: bold; font-size: 0.9em; color: #475569; display: block; margin-bottom: 6px;">
                    Mensaje de Bienvenida de la Radio:
                </label>
                <textarea wire:model="mensaje_bienvenida" rows="5" class="form-control"
                    placeholder="Escriba el saludo o anuncio oficial de la radio..."
                    style="border-radius: 4px; resize: none; font-size: 0.95em; line-height: 1.5;"></textarea>
                @error('mensaje_bienvenida')
                    <small style="color: red; font-weight: bold; display: block; margin-top: 4px;">⚠️
                        {{ $message }}</small>
                @enderror
            </div>

            <!-- Campo 2: Carga de Archivo de Fotografía o Banner -->
            <div class="form-group"
                style="margin-bottom: 25px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1;">
                <label style="font-weight: bold; font-size: 0.9em; color: #475569; display: block; margin-bottom: 6px;">
                    Fotografía Promocional de Portada:
                </label>
                <input type="file" wire:model="foto_portada" accept="image/*" class="form-control-file"
                    style="font-size: 0.9em;">

                <!-- PREVISUALIZADORES EN TIEMPO REAL -->
                @if ($foto_portada)
                    <div style="margin-top: 12px; border-top: 1px solid #e2e8f0; padding-top: 10px;">
                        <small style="color:#28a745; font-weight:bold; display:block; margin-bottom:4px;">✨
                            Previsualizando nueva imagen:</small>
                        <img src="{{ $foto_portada->temporaryUrl() }}"
                            style="max-width: 100%; max-height: 140px; border-radius: 4px; border: 1px solid #ccc; object-fit: contain;">
                    </div>
                    @elif ($imagenExistente)
                    <div style="margin-top: 12px; border-top: 1px solid #e2e8f0; padding-top: 10px;">
                        <small style="color:#64748b; font-weight:bold; display:block; margin-bottom:4px;">🖼️ Imagen
                            activa en la portada actual:</small>
                        <img src="{{ asset('storage/' . $imagenExistente) }}"
                            style="max-width: 100%; max-height: 140px; border-radius: 4px; border: 1px solid #ccc; object-fit: contain;">
                    </div>
                @endif

                @error('foto_portada')
                    <small style="color: red; display:block; margin-top: 5px; font-weight:bold;">⚠️
                        {{ $message }}</small>
                @enderror
            </div>

            <!-- Botón Guardar -->
            <div style="text-align: right;">
                <!-- NUEVA SECCIÓN: CONTROL DE CONTACTO WHATSAPP -->
                <div class="card card-success card-outline mb-4"
                    style="background: #fff; padding: 15px; border-radius: 6px; border: 1px solid #cbd5e1; margin-top: 15px;">
                    <h5 style="font-weight: bold; color: #28a745; margin-bottom: 12px; font-size: 0.95em;">
                        🟢 Configuración de Enlace a WhatsApp (Cabina Radial)
                    </h5>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label style="font-size: 0.85em; font-weight: bold; color: #475569;">Número de
                                WhatsApp:</label>
                            <input type="text" wire:model="whatsapp_numero" class="form-control"
                                placeholder="Ej: 51926634659">
                            @error('whatsapp_numero')
                                <small style="color: red;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-8 form-group">
                            <label style="font-size: 0.85em; font-weight: bold; color: #475569;">Mensaje Predeterminado
                                del Oyente:</label>
                            <input type="text" wire:model="whatsapp_mensaje_base" class="form-control"
                                placeholder="Texto que enviará el usuario al hacer clic en el botón flotante">
                            @error('whatsapp_mensaje_base')
                                <small style="color: red;">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn text-white"
                    style="background-color: #e11d48; font-weight: bold; padding: 10px 30px; border-radius: 4px; text-transform: uppercase; font-size: 0.85em; letter-spacing: 0.5px;">
                    💾 Publicar Cambios en Vivo
                </button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notificar-config-radio', (event) => {
                const datos = Array.isArray(event) ? event : event;
                Swal.fire({
                    title: 'Portada Actualizada',
                    text: datos.mensaje,
                    icon: 'success',
                    timer: 2300,
                    showConfirmButton: false,
                    confirmButtonColor: '#e11d48'
                });
            });
        });
    </script>

</div>
