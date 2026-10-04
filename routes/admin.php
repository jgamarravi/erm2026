<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Livewire\ProclamacionAutoridades;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Electoral\ConfiguracionRadio;
use App\Livewire\Admin\Electoral\ControlCandidatos;
use App\Livewire\Admin\Electoral\ControlUsuarios;
use App\Livewire\Admin\Electoral\EncuestasEstadisticas;
use App\Livewire\Admin\Electoral\EncuestasReporte;
use App\Livewire\Admin\Electoral\GestionLocales;
use App\Livewire\Admin\Ubigeo\ControlUbigeo;
use App\Livewire\AuditoriaInscripciones;
use App\Livewire\ControlActasObservadas;
use App\Livewire\DigitacionActa;
use App\Livewire\FichaPaloteo;
use App\Livewire\MonitoreoActas;
use App\Livewire\RegistroInscripcion;
use App\Livewire\ResultadosDashboard;

/*
|--------------------------------------------------------------------------
| Rutas Administrativas
| Administrador, Supervisor, Locutor, Clerk (archivos de mantenimiento),
| Digitador (solo ingresa datos), Oyente
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'verified'])
    ->group(function () {

        // ==============================================================
        // ➔ SECCIÓN 1: PANEL GENERAL (Todos los roles ingresan)
        // ==============================================================
        Route::middleware('role:Administrador|Digitador|Locutor|Oyente|Supervisor|Clerk')
            ->group(function () {

        //    Route::get('/dashboard', ResultadosDashboard::class)->name('dashboard');

        Route::get('/dashboard',MonitoreoActas::class)->name('dashboard');

        // Ver resultados por tipo eleccion y por ubigeo
        Route::get('/resultados', ResultadosDashboard::class)->name('resultados');
        // Ver autoridades electas -> Todos
            Route::get('/autoridades', ProclamacionAutoridades::class)
                ->name('autoridades');
        });

        // ==============================================================
        // ➔ SECCIÓN 2: PROCESAMIENTO ELECTORAL (DIGITACIÓN Y REPORTES)
        // ==============================================================
    
        // Digitación de Actas (Administrador, Supervisor, Clerk y Digitador)
        Route::middleware(['role:Administrador|Supervisor|Clerk|Digitador'])->group(function () {
            Route::get('/digitacion', DigitacionActa::class)
                ->name('digitacion');

        });


        // Módulo de Medios de Comunicación (Radio / Encuestas)
        Route::middleware(['role:Administrador|Locutor'])->group(function () {

            // Redactar portada, 3/4 de pantalla, para la webpage
            Route::get('/portada', ConfiguracionRadio::class)
                ->name('portada');

            // Lanzar encuestas de opinion en la página web
            Route::get('/encuestaradio', EncuestasReporte::class)
                ->name('encuestaradio');

            // Redacta crónicas de opinión sobre resultado de las encuestas emitidas
            Route::get('/cronicas', EncuestasEstadisticas::class)
                ->name('cronicas');

            // Imprimir formulario para recabar intención de votos
            Route::get('/imprimir/polling', FichaPaloteo::class)
                ->name('polling');
        });


        // ==============================================================
        // ➔ SECCIÓN 3: FISCALIZACIÓN JURÍDICA (JEE / AUDITORÍA)
        // ==============================================================
        Route::middleware(['role:Administrador|Supervisor'])->group(function () {

        // Mesa de resolución para corregir actas observadas/impugnadas
            Route::get('/actas-observadas', ControlActasObservadas::class)
                ->name('observadas');
            
        });


        // ==============================================================
        // ➔ SECCIÓN 4: CONFIGURACIÓN Y PADRÓN TERRITORIAL (ARCHIVOS)
        // ==============================================================
        Route::middleware(['role:Administrador|Supervisor|Clerk'])->group(function () {

            // Modificar número de escaños y consejeros por ubigeo
            Route::get('/ubigeos', ControlUbigeo::class)
                ->name('ubigeos');

            // Locales de Votación y Mesas de Sufragio
            Route::get('/locales', GestionLocales::class)
                ->name('locales');

            // Catálogo de Partidos Políticos y Candidaturas
            Route::get('/candidatos', ControlCandidatos::class)
                ->name('candidatos');

            // Asignar los partidos inscritos a los ubigeos
            Route::get('/inscribir/partido', RegistroInscripcion::class)
                ->name('inscribir_partido');

            // Listado de Partidos inscritos por ubigeo, para controlar cedula de digitación
            Route::get('/auditoria', AuditoriaInscripciones::class)
                ->name('auditoria');
        });


        // ==============================================================
        // ➔ SECCIÓN 5: CONTROL DE SEGURIDAD (EXCLUSIVO ADMINISTRADOR)
        // ==============================================================
        Route::middleware(['role:Administrador'])->group(function () {

            // Panel de control de usuarios, roles y switches de Spatie
            Route::get('/usuarios', ControlUsuarios::class)
                ->name('usuarios');
        });

    });
