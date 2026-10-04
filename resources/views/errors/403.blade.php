@component('layouts.app')
    <div class="container-fluid pt-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card card-outline card-danger shadow">
                    <div class="card-body p-5">
                        <div class="error-page m-0 d-md-flex align-items-center">
                            <h2 class="headline text-danger font-weight-light mr-md-4 mb-3 mb-md-0" style="font-size: 80px; line-height: 1;">403</h2>
                            
                            <div class="error-content pl-md-4" style="border-left: 2px solid #dee2e6;">
                                <h4 class="font-weight-bold text-dark mb-2">
                                    <i class="fas fa-shield-alt text-danger mr-2"></i> Acceso Restringido
                                </h4>
                                <p class="text-muted text-sm">
                                    Tu credencial u operador electoral actual no cuenta con las facultades ni el rol necesario para interactuar con este módulo del sistema.
                                </p>
                                <p class="text-muted text-xs">
                                    Si consideras que se trata de un error de configuración, solicita soporte técnico al Administrador para que actualice tus permisos en tiempo real.
                                </p>
                                <div class="mt-4">
                                    <a href="javascript:history.back()" class="btn btn-danger font-weight-bold shadow-sm btn-sm">
                                        <i class="fas fa-arrow-left mr-1"></i> Volver a la pantalla anterior
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcomponent
