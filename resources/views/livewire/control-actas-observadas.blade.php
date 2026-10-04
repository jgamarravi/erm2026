<div class="container-fluid mt-3">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-danger text-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold">🛡️ Oficina de Control de Calidad: Actas Observadas / Derivadas al JEE</h6>
            <span class="badge bg-dark font-monospace">Fiscalización Activa</span>
        </div>
        <div class="card-body p-3">
            @if (session()->has('message')) <div class="alert alert-success fw-bold py-2">{{ session('message') }}</div> @endif

            <!-- Filtro de Clasificación Jurídica -->
            <div class="mb-3" style="max-width: 350px;">
                <select class="form-select form-select-sm" wire:model.live="filtro_error">
                    <option value="">-- Ver todas las inconsistencias --</option>
                    <option value="ERROR_CUADRE">ERROR DE CUADRE MATEMÁTICO</option>
                    <option value="EXCEDE_PADRON">SÍNTOMA: EXCEDE EL PADRÓN INICIAL</option>
                    <option value="IMPUGNADA_JEE">IMPUGNADA POR PERSONERO DE PARTIDO</option>
                </select>
            </div>

            <!-- Tabla de Control Administrativo -->
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered align-middle mb-3">
                    <thead class="table-secondary text-center small text-uppercase fw-bold">
                        <tr>
                            <th>N° Mesa</th>
                            <th>Jurisdicción Región / Distrito</th>
                            <th>Local de Votación</th>
                            <th>Tipo de Inconsistencia Detectada</th>
                            <th>Acciones de Control</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($actas_observadas as $acta)
                        <tr>
                            <td class="text-center font-monospace fw-bold bg-white text-danger fs-6" width="10%">{{ $acta->mesa_numero }}</td>
                            <td>
                                <span class="d-block fw-bold text-dark">{{ $acta->region_electoral }}</span>
                                <small class="text-muted">{{ $acta->distrito_nombre }}</small>
                            </td>
                            <td class="text-truncate" style="max-width: 250px;">{{ $acta->centro_nombre }}</td>
                            <td class="text-center">
                                <span class="badge bg-danger p-2 text-uppercase">
                                    ⚠️ {{ str_replace('_', ' ', $acta->tipo_observacion) }}
                                </span>
                            </td>
                            <td class="text-center" width="15%">
                                <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm" 
                                        wire:click="liberarMesaParaRedigitacion({{ $acta->id }})"
                                        wire:confirm="¿Está seguro de eliminar los votos anteriores y liberar la mesa para una nueva digitación correctora?">
                                    🔄 Liberar Mesa
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4 fw-bold">🎉 ¡Excelente! No existen actas observadas bajo los criterios seleccionados. Todas están cuadradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $actas_observadas->links() }}
            </div>
        </div>
    </div>
</div>
