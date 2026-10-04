<div>
    <!-- Encabezado con Switch de Cédula de Sufragio -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6 mb-2 mb-md-0">
            <h4 class="text-dark"><i class="bi bi-bar-chart-steps text-primary me-2"></i>Centro de Control Estadístico -
                ERM 2026</h4>
            <p class="text-muted small mb-0">Consolidado métrico en porcentajes de actas procesadas y votación válida.
            </p>
        </div>

    </div>

    <!-- Bloque de Cajas Métricas Dinámicas -->
    <div class="row">
        <!-- Tarjeta: Actas Procesadas -->
        <div class="col-md-4 mb-3">
            <div class="info-box bg-white shadow-sm border-start border-4 border-success rounded">
                <span class="info-box-icon text-success"><i class="bi bi-file-earmark-check-fill"></i></span>
                <div class="info-box-content">
                    <span class="text-muted text-uppercase small font-weight-bold">Actas Oficiales Cerradas</span>
                    <h3 id="widgetProcesadas" class="text-dark m-0 fw-bold">0%</h3>
                    <small id="widgetMesasDetalle" class="text-secondary">0 de {{ $totalMesas }} mesas</small>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Votos Válidos -->
        <div class="col-md-4 mb-3">
            <div class="info-box bg-white shadow-sm border-start border-4 border-primary rounded">
                <span class="info-box-icon text-primary"><i class="bi bi-check-circle-fill"></i></span>
                <div class="info-box-content">
                    <span class="text-muted text-uppercase small font-weight-bold">Votos Válidos (Partidos)</span>
                    <h3 id="widgetValidos" class="text-dark m-0 fw-bold">0</h3>
                    <small class="text-secondary">Excluye votos en blanco y nulos</small>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Asistencia General -->
        <div class="col-md-4 mb-3">
            <div class="info-box bg-white shadow-sm border-start border-4 border-dark rounded">
                <span class="info-box-icon text-dark"><i class="bi bi-people-fill"></i></span>
                <div class="info-box-content">
                    <span class="text-muted text-uppercase small font-weight-bold">Asistencia a las Urnas</span>
                    <h3 id="widgetEmitidos" class="text-dark m-0 fw-bold">0</h3>
                    <small class="text-secondary">Universo total de votantes</small>
                </div>
            </div>
        </div>
    </div>


    <!-- MOTOR DE ACTUALIZACIÓN CON JAVASCRIPT Y CHART.JS -->
    <script>
        // Inyectamos las matrices multidimensionales calculadas por Laravel de forma segura
        const datosAvance = {!! json_encode($avanceActas) !!};
        const datosVotos = {!! json_encode($votosValidosData) !!};
        const totalEmitidos = {!! json_encode($totalEmitidosGlobal) !!};
        const totalValidos = {!! json_encode($totalValidosGlobal) !!};
        const totalMesasEntorno = {{ $totalMesas }};

        let chartDona, chartBarras;

        document.addEventListener("DOMContentLoaded", function() {
            // Inicializar las dos instancias de gráficos vacías por defecto
            const ctxDona = document.getElementById('chartDonaAvance').getContext('2d');
            chartDona = new Chart(ctxDona, {
                type: 'doughnut',
                data: {
                    labels: ['Procesadas (%)', 'Pendientes (%)'],
                    datasets: [{
                        data: ,
                        backgroundColor: ['#198754', '#ffc107'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            const ctxBarras = document.getElementById('chartBarrasVotos').getContext('2d');
            chartBarras = new Chart(ctxBarras, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#0d6efd', '#20c997', '#dc3545', '#ffc107', '#6f42c1',
                            '#17a2b8'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(v) {
                                    return value = v + '%';
                                }
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            });

            // Disparar la carga inicial con la opción por defecto (GOBERNADOR)
            conmutarGraficosDashboard('GOBERNADOR');
        });

        // FUNCIÓN CLAVE: Intercepta el selector y redibuja los gráficos y widgets en tiempo real
        function conmutarGraficosDashboard(estamento) {
            const avance = datosAvance[estamento];
            const votos = datosVotos[estamento];
            const emitidos = totalEmitidos[estamento];
            const validos = totalValidos[estamento];

            // 1. Actualizar Widgets de Texto
            document.getElementById('widgetProcesadas').innerText = avance.procesadas + '%';
            document.getElementById('widgetMesasDetalle').innerText = avance.conteo + ' de ' + totalMesasEntorno +
                ' mesas cerradas';
            document.getElementById('widgetValidos').innerText = Number(validos).toLocaleString();
            document.getElementById('widgetEmitidos').innerText = Number(emitidos).toLocaleString();
            document.getElementById('etiquetaTablaActiva').innerText = estamento.replace('_', ' ');

            chartDona.data.datasets[0].data = [avance.procesadas, avance.pendientes];
            chartDona.update();

            // 3. Evaluar y Actualizar Gráfico de Barras Horizontales (Votos Válidos)
            const wrapper = document.getElementById('wrapperGraficoBarras');
            const alerta = document.getElementById('alertaSinDatos');

            if (validos > 0 && votos.labels.length > 0) {
                wrapper.classList.remove('d-none');
                alerta.classList.add('d-none');
                chartBarras.data.labels = votos.labels;
                chartBarras.data.datasets[0].data = votos.datasets;
                chartBarras.update();
            } else {
                wrapper.classList.add('d-none');
                alerta.classList.remove('d-none');
            }
            // INYECCIÓN QUIRÚRGICA DE LA TABLA RESPONSIVA INFERIOR
            const cuerpoTabla = document.getElementById('cuerpoTablaDinamica');
            cuerpoTabla.innerHTML = ''; // Limpiar filas anteriores

            if (votos.tabla && votos.tabla.length > 0) {
                votos.tabla.forEach(fila => {
                    let claseFila = '';
                    let etiquetaPorcentaje = fila.porcentaje + '%';

                    if (fila.tipo === 'BLANCO' || fila.tipo === 'NULO') {
                        claseFila = 'table-light text-muted italic';
                        etiquetaPorcentaje = '-'; // Por ley, blancos y nulos no entran al porcentaje válido
                    }

                    const tr = document.createElement('tr');
                    if (claseFila) tr.className = claseFila;

                    tr.innerHTML = `
                        <td class="text-start fw-bold">${fila.nombre}</td>
                        <td><span class="badge ${fila.tipo === 'PARTIDO' ? 'bg-dark' : 'bg-secondary'}">${fila.siglas}</span></td>
                        <td class="fw-bold ${fila.tipo === 'PARTIDO' ? 'text-primary' : ''}">${fila.votos.toLocaleString()}</td>
                        <td class="fw-bold">${etiquetaPorcentaje}</td>
                    `;
                    cuerpoTabla.appendChild(tr);
                });
            } else {
                cuerpoTabla.innerHTML =
                    `<tr><td colspan="4" class="text-center text-muted py-3">No hay registros de escrutinio para procesar en esta tabla.</td></tr>`;
            }
        }
    </script>
</div>
