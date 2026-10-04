<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear Permisos Básicos


        $permisos = [
            'administrar sistema',
            'digitar actas',
            'ver reportes',
            'gestionar encuesta',
            'redactar cronicas',
            'participar comunidad',
            'ver panel',
            'gestionar usuarios',
            'gestionar permisos',
            'gestionar roles',
            'gestionar portada',
            'redactar cronica',
            'imprimir polling',
            'gestionar ubigeo',
            'gestionar colegio',
            'gestionar partido',
            'revisar actas',
            'unlock actas',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }
        // 2. Crear Roles y Asignar Permisos
        $admin = Role::create(['name' => 'Administrador']);
        $admin->givePermissionTo(Permission::all());

        $supervisor = Role::create(['name' => 'Supervisor']);
        $supervisor->givePermissionTo(['revisar actas', 'unlock actas']);

        $locutor = Role::create(['name' => 'Locutor']);
        $locutor->givePermissionTo(['redactar cronica', 'gestionar portada', 'gestionar encuesta']);

        $digitador = Role::create(['name' => 'Digitador']);
        $digitador->givePermissionTo(['digitar actas']);

        $clerk = Role::create(['name' => 'Clerk']);
        $clerk->givePermissionTo(['gestionar ubigeo', 'gestionar colegio', 'gestionar partido', 'imprimir polling']);

        $oyente = Role::create(['name' => 'Oyente']);
        $oyente->givePermissionTo(['ver panel']);


        // 3. Crear Usuario Administrador de Prueba
        $adminUser = User::create([
            'name' => 'Administrador',
            'email' => 'admin@ejemplo.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole($admin);

    }
}
