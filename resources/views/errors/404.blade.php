@component('layouts.app')
<div class="container-fluid pt-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card card-outline card-warning shadow">
                <div class="card-body p-5">
                    <div class="error-page m-0 d-md-flex align-items-center">
                        <!-- Número Gigante estilo AdminLTE 3 -->
                        <h2 class="headline text-warning font-weight-light mr-md-4 mb-3 mb-md-0" style="font-size: 80px; line-height: 1; float: left;">404</h2>
                        
                        <div class="error-content pl-md-4" style="margin-left: 160px; border-left: 2px solid #dee2e6;">
                            <h4 class="font-weight-bold text-dark mb-2">
                                <i class="fas fa-exclamation-triangle text-warning mr-2"></i> Página no encontrada
                            </h4>
                            <p class="text-muted text-sm">
                                No hemos podido ubicar el recurso o el módulo electoral que estás intentando consultar en el sistema.
                            </p>
                            <p class="text-muted text-xs">
                                Esto puede deberse a que la URL está mal escrita, el enlace ha expirado o el distrito/provincia consultado no se encuentra mapeado en el padrón del ubigeo actual.
                            </p>
                            <div class="mt-4">
                                <a href="javascript:history.back()" class="btn btn-warning font-weight-bold text-dark shadow-sm btn-sm">
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