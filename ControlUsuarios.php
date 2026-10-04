<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\WithPagination; // 1. Importar el trait de paginación

class ControlUsuarios extends Component
{
    use WithPagination; // 2. Utilizar el trait dentro de la clase
    protected $paginationTheme = 'bootstrap';


    public $buscar_usuario = '';
    public $usuario_seleccionado_id = null;

    // Propiedades para la asignación de Spatie por Usuario
    public $roles_usuario = [];
    public $permisos_usuario = [];

    // Propiedades para Mantenimiento de Roles
    public $nuevo_rol_nombre = '';
    public $rol_plantilla_id = ''; // Permite clonar permisos de otro rol existente

    public function limpiarSeleccion()
    {
        $this->reset(['usuario_seleccionado_id', 'roles_usuario', 'permisos_usuario']);
    }

    public function seleccionarUsuario($id)
    {
        $this->usuario_seleccionado_id = $id;
        $usuario = User::find($id);

        if ($usuario) {
            $this->roles_usuario = $usuario->getRoleNames()->toArray();
            $this->permisos_usuario = $usuario->getPermissionNames()->toArray();
        }
    }

    // ACCIÓN: Crear un nuevo Rol y opcionalmente heredar permisos
    public function crearRol()
    {
        $this->validate([
            'nuevo_rol_nombre' => 'required|string|min:3|unique:roles,name',
            'rol_plantilla_id' => 'nullable|exists:roles,id'
        ]);

        DB::beginTransaction();
        try {
            // 1. Creamos el nuevo rol en Spatie
            $nuevoRol = Role::create(['name' => trim($this->nuevo_rol_nombre)]);
            $mensajeAdicional = '';

            // 2. REQUERIMIENTO: Si seleccionaron una plantilla, copiamos sus permisos de inmediato
            if ($this->rol_plantilla_id) {
                $rolBase = Role::findById($this->rol_plantilla_id);
                if ($rolBase) {
                    // Extraemos los permisos del rol base y se los inyectamos al nuevo
                    $permisosBase = $rolBase->permissions()->pluck('name')->toArray();
                    $nuevoRol->givePermissionTo($permisosBase);
                    $mensajeAdicional = " y heredó " . count($permisosBase) . " permisos del perfil '{$rolBase->name}'.";
                }
            }

            DB::commit();

            session()->flash('success', "El rol '{$this->nuevo_rol_nombre}' fue creado con éxito" . $mensajeAdicional);
            $this->reset(['nuevo_rol_nombre', 'rol_plantilla_id']);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear el rol con permisos: ' . $e->getMessage());
        }
    }

    public function eliminarRol($rolId)
    {
        try {
            $rol = Role::findById($rolId);

            if (in_array($rol->name, ['Administrador', 'Supervisor', 'Digitador'])) {
                session()->flash('error', "Por seguridad gubernamental, el rol estructural '{$rol->name}' no puede ser eliminado.");
                return;
            }

            $rol->delete();
            session()->flash('success', 'El rol seleccionado ha sido eliminado del sistema de manera definitiva.');

            if ($this->usuario_seleccionado_id) {
                $this->seleccionarUsuario($this->usuario_seleccionado_id);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el rol: ' . $e->getMessage());
        }
    }

    public function eliminarUsuario($userId)
    {
        // 1. Protección de seguridad: Verificar si tiene el permiso asignado con Spatie
        if (!auth()->user()->can('gestionar usuarios')) {
            abort(403, 'No tienes autorización para realizar esta acción.');
        }

        $user = User::findOrFail($userId);

        // 2. Evitar que el propio Administrador se elimine a sí mismo por error
        if ($user->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta de usuario.');
            return;
        }

        // 3. Proceder con la eliminación
        $user->delete();

        // 4. Enviar notificación de éxito
        session()->flash('message', "El usuario {$user->name} fue eliminado correctamente.");
    }
    public function alternarRol($roleName)
    {
        if (!$this->usuario_seleccionado_id)
            return;
        $usuario = User::find($this->usuario_seleccionado_id);

        if ($usuario->hasRole($roleName)) {
            $usuario->removeRole($roleName);
            session()->flash('success', "Rol '{$roleName}' removido correctamente.");
        } else {
            $usuario->assignRole($roleName);
            session()->flash('success', "Rol '{$roleName}' asignado con éxito.");
        }
        $this->seleccionarUsuario($this->usuario_seleccionado_id);
    }

    public function alternarPermiso($permissionName)
    {
        if (!$this->usuario_seleccionado_id)
            return;
        $usuario = User::find($this->usuario_seleccionado_id);

        if ($usuario->hasPermissionTo($permissionName)) {
            $usuario->revokePermissionTo($permissionName);
            session()->flash('success', "Permiso '{$permissionName}' revocado.");
        } else {
            $usuario->givePermissionTo($permissionName);
            session()->flash('success', "Permiso '{$permissionName}' otorgado.");
        }
        $this->seleccionarUsuario($this->usuario_seleccionado_id);
    }

    public function render()
    {
        $usuarios = User::query()
            // 1. Excluir al rol Administrador usando Spatie
            ->withoutRole('Administrador')

            // 2. Filtro de búsqueda agrupado correctamente
            ->when($this->buscar_usuario, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->buscar_usuario . '%')
                        ->orWhere('email', 'like', '%' . $this->buscar_usuario . '%');
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(15);

        $todosLosRoles = Role::orderBy('name', 'asc')
            ->where('name', '!=', 'Administrador')
            ->get();
        $todosLosPermisos = Permission::orderBy('name', 'asc')->get();
        $usuarioDetalle = $this->usuario_seleccionado_id ? User::find($this->usuario_seleccionado_id) : null;

        return view('livewire.admin.electoral.control-usuarios', [
            'usuarios' => $usuarios,
            'todosLosRoles' => $todosLosRoles,
            'todosLosPermisos' => $todosLosPermisos,
            'usuarioDetalle' => $usuarioDetalle
        ])->layout('layouts.app');
    }
}
