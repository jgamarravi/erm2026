<?php

namespace App\Livewire\Admin\Electoral;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Encuesta;
use App\Models\Admin\EncuestaOpcion;
use Livewire\Attributes\On;


class EncuestasReporte extends Component
{
    use WithPagination;

    public $nuevaPregunta = '';
    public $opcionesTexto = '';
    public $mensajeBienvenida = '';


    // Filtros de Historial
    public $fechaInicio = '';
    public $fechaFin = '';

    protected $queryString = ['fechaInicio', 'fechaFin'];

    public function updatingFechaInicio()
    {
        $this->resetPage();
    }
    public function updatingFechaFin()
    {
        $this->resetPage();
    }

    public function guardarEncuesta()
    {
        $this->validate([
            'nuevaPregunta' => 'required|string|min:10',
            'opcionesTexto' => 'required'
        ]);

        Encuesta::where('activa', true)->update(['activa' => false]);

        $encuesta = Encuesta::create([
            'pregunta' => $this->nuevaPregunta,
            'mensaje_bienvenida' => $this->mensajeBienvenida ?: 'Conectando a toda la provincia con el ritmo y la voz que nos identifica.', // Mensaje por defecto si se deja vacío
            'activa' => true
        ]);

        $lineas = explode("\n", str_replace("\r", "", $this->opcionesTexto));
        foreach ($lineas as $linea) {
            $lineaLimpia = trim($linea);
            if (!empty($lineaLimpia)) {
                $encuesta->opciones()->create(['opcion' => $lineaLimpia, 'votos' => 0]);
            }
        }

        $this->reset(['nuevaPregunta', 'opcionesTexto', 'mensajeBienvenida']);
        $this->dispatch('notificar-auditoria', mensaje: '¡Nueva encuesta publicada al aire con éxito!');
    }

    // ACCIÓN NUEVA: Eliminar encuesta permanente
    #[On('eliminar-encuesta-confirmado')]
    public function eliminarEncuesta($id)
    {
        $encuesta = Encuesta::findOrFail($id);
        $encuesta->delete(); // Elimina en cascada las opciones por la regla de la BD

        $this->dispatch('notificar-auditoria', mensaje: 'Encuesta eliminada del historial con éxito.');
    }
    public function exportarExcel($id)
    {
        $encuesta = Encuesta::with('opciones')->findOrFail($id);
        $totalVotos = $encuesta->opciones->sum('votos');

        $fileName = "reporte_encuesta_" . $encuesta->id . ".xls";

        return response()->streamDownload(function () use ($encuesta, $totalVotos) {
            // CORRECCIÓN ANTIMUTACIÓN DE CARACTERES: Inyectar el BOM UTF-8 (Firma invisible para Excel)
            echo "\xEF\xBB\xBF";

            echo "<table border='1'>";
            echo "<tr style='background-color:#e11d48; color:white; font-weight:bold;'>";
            echo "<th colspan='3' style='font-size:14px; padding:8px;'>RADIO STEREO 92 FM - REPORTE DE OPINIÓN PÚBLICA</th>";
            echo "</tr>";
            echo "<tr><td colspan='3'><b>Pregunta:</b> " . e($encuesta->pregunta) . "</td></tr>";
            echo "<tr><td colspan='3'><b>Fecha de Corte:</b> " . now()->format('d/m/Y H:i') . "</td></tr>";
            echo "<tr></tr>";

            echo "<tr style='background-color:#f1f5f9; font-weight:bold;'>";
            echo "<th>Alternativa / Opción</th>";
            echo "<th>Votos Recibidos</th>";
            echo "<th>Porcentaje Proporcional</th>";
            echo "</tr>";

            foreach ($encuesta->opciones as $opc) {
                $porcentaje = $totalVotos > 0 ? round(($opc->votos / $totalVotos) * 100, 2) : 0;
                echo "<tr>";
                echo "<td>" . e($opc->opcion) . "</td>";
                echo "<td align='right'>{$opc->votos}</td>";
                echo "<td align='right'>{$porcentaje}%</td>";
                echo "</tr>";
            }

            echo "<tr style='font-weight:bold; background-color:#e2e8f0;'>";
            echo "<td>TOTAL DE OYENTES PARTICIPANTES</td>";
            echo "<td align='right'>{$totalVotos}</td>";
            echo "<td align='right'>100.00%</td>";
            echo "</tr>";
            echo "</table>";
        }, $fileName);
    }




    public function render()
    {
        // Aplicamos el filtro relacional por rango de fechas utilizando Eloquent
        $encuestas = Encuesta::withCount('opciones')
            ->when($this->fechaInicio, function ($q) {
                $q->whereDate('created_at', '>=', $this->fechaInicio);
            })
            ->when($this->fechaFin, function ($q) {
                $q->whereDate('created_at', '<=', $this->fechaFin);
            })
            ->orderBy('id', 'desc')
            ->paginate(5); // Agregamos paginación nativa de Livewire para listas largas

        return view('livewire.admin.electoral.encuestas-reporte', [
            'encuestas' => $encuestas
        ])->layout('layouts.app');
    }
}
