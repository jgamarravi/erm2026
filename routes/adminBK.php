<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Livewire\Admin\Electoral\ActasObservadas;
use App\Livewire\Admin\Electoral\AutoridadesElectas;
use App\Livewire\Admin\Electoral\ConfiguracionRadio;
use App\Livewire\Admin\Electoral\ControlCandidatos;
use App\Livewire\Admin\Electoral\ControlUsuarios;
use App\Livewire\Admin\Electoral\EncuestasEstadisticas;
use App\Livewire\Admin\Electoral\EncuestasReporte;
use App\Livewire\Admin\Electoral\FichaPaloteo;
use App\Livewire\Admin\Electoral\GestionLocales;
use App\Livewire\Admin\Electoral\GestionPartidosUbigeo;
use App\Livewire\Admin\Electoral\RegistrarActa;
use App\Livewire\Admin\Electoral\ReporteConsolidado;
use App\Livewire\Admin\Ubigeo\ControlUbigeo;

/*
|--------------------------------------------------------------------------
| Rutas Administrativas (El prefijo 'admin.' se añade automáticamente)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // ==============================================================
    // ➔ SECCIÓN 1: PANEL GENERAL (Todos los roles ingresan)
    // ==============================================================
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:Administrador|Digitador|Locutor|Oyente|Supervisor|Clerk')
        ->name('dashboard');

    // Reporte Consolidado (Cifra Repartidora D'Hondt) - Acceso General
    Route::get('/electoral/cifra-repartidora', ReporteConsolidado::class)
        ->name('cifra');

    // Autoridades Electas - Acceso General de lectura
    Route::get('/electoral/autoridades-electas', AutoridadesElectas::class)
        ->name('autoridades');



    // ==============================================================
    // ➔ SECCIÓN 2: PROCESAMIENTO ELECTORAL (DIGITACIÓN Y REPORTES)
    // ==============================================================

    // Digitación de Actas (Administrador, Supervisor, Clerk y Digitador)
    Route::get('/electoral/actas', RegistrarActa::class)
        ->middleware('role:Administrador|Supervisor|Clerk|Digitador')
        ->name('actas');

    

    Route::middleware(['role:Administrador|Locutor'])->group(function () {
        // Redactar portada, 3/4 de pantalla pantalla, para la webpage
        Route::get('/portada', ConfiguracionRadio::class)
            ->name('portada');

        // Lanzar encuestas en la página web
        Route::get('/encuestaradio', EncuestasReporte::class)
            ->name('encuestaradio');

        // Redacta crónicas de opinión sobre resultado de las encuestas emitidas
        Route::get('/cronicas', EncuestasEstadisticas::class)
            ->name('cronicas');

        // Imprimir formulario para tomar datos de poblacion
        Route::get('/encuesta', FichaPaloteo::class)
            ->name('encuesta');

    });

    // ==============================================================
    // ➔ SECCIÓN 3: FISCALIZACIÓN JURÍDICA (JEE / AUDITORÍA)
    // ==============================================================
    Route::middleware(['role:Administrador|Supervisor'])->group(function () {

        // Mesa de resolución para corregir actas observadas/impugnadas
        Route::get('/electoral/actas-observadas', ActasObservadas::class)
            ->name('observadas');
    });


    // ==============================================================
    // ➔ SECCIÓN 4: CONFIGURACIÓN Y PADRÓN TERRITORIAL (ARCHIVOS)
    // ==============================================================
    Route::middleware(['role:Administrador|Supervisor|clerk'])->group(function () {

        // Mantenimiento parcial del catálogo nacional de Ubigeos, asignar escaños para distritos y consejeros para provincias.
        Route::get('/ubigeos', ControlUbigeo::class)
            ->name('ubigeos');

        // Locales de Votación y Mesas de Sufragio
        Route::get('/electoral/locales', GestionLocales::class)
            ->name('locales');

        // Catálogo de Partidos Políticos y Candidaturas
        Route::get('/electoral/candidatos', ControlCandidatos::class)
            ->name('candidatos');

        // Asignar los partidos inscritos a los ubigeos donde registrará candidatos
        Route::get('/electoral/ubigeo-partidos', GestionPartidosUbigeo::class)
            ->name('ubigeopartidos');
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
