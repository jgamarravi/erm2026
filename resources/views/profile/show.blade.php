<x-app-layout>
    <div class="container-fluid">
        
        <!-- Tarjeta de cabecera -->
        <div class="card card-dark mb-4">
            <div class="card-header bg-dark text-white">
                <h3 class="card-title mb-0">Administración de Cuenta</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">Actualiza la información de tu perfil, contraseña y opciones de seguridad.</p>
            </div>
        </div>

        <!-- Rejilla para los componentes de Jetstream -->
        <div class="row">
            <div class="col-12 col-lg-10 mx-auto">
                
                @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                    <div class="card card-white shadow-sm mb-4">
                        <div class="card-body bootstrap-jetstream-fix p-4">
                            @livewire('profile.update-profile-information-form')
                        </div>
                    </div>
                @endif

                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                    <div class="card card-white shadow-sm mb-4">
                        <div class="card-body bootstrap-jetstream-fix p-4">
                            @livewire('profile.update-password-form')
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>
</x-app-layout>
