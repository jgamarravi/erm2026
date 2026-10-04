<div class="container-fluid pt-3">
    <!-- NOTIFICACIONES FLASH -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible shadow-sm">
            <h5><i class="icon fas fa-shield-alt"></i></h5>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@canany(['gestionar usuarios', 'gestionar roles', 'gestionar permisos'])

    <div class="row">
        <!-- COLUMNA IZQUIERDA: LISTADO DE LOS 7 USUARIOS -->
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2 text-primary"></i> Operadores
                        del Sistema</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <input type="text" wire:model.live="buscar_usuario" class="form-control form-control-sm"
                            placeholder="Buscar usuario por nombre o correo...">
                    </div>

                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover table-sm table-bordered m-0 text-sm">
                            <thead class="bg-light text-center text-muted text-xs uppercase sticky-top"
                                style="z-index: 1;">
                                <tr>
                                    <th class="text-left" style="width: 45%;">Usuario / Operador</th>
                                    <th style="width: 40%;">Roles Activos</th>
                                    <th style="width: 15%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($usuarios as $u)
                                    <tr
                                        class="{{ $usuario_seleccionado_id == $u->id ? 'table-primary font-weight-bold' : '' }}">
                                        <td class="align-middle">
                                            <span class="d-block text-dark"><strong>{{ $u->name }}</strong></span>
                                            <small class="text-muted text-xs">{{ $u->email }}</small>
                                        </td>
                                        <td class="text-center align-middle">
                                            @forelse($u->getRoleNames() as $rolName)
                                                <span
                                                    class="badge bg-primary text-xs px-2 py-1 m-0.5">{{ $rolName }}</span>
                                            @empty
                                                <span class="badge bg-secondary text-xs px-2 py-1 m-0.5">Sin Rol
                                                    Asignado</span>
                                            @endforelse
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" wire:click="seleccionarUsuario({{ $u->id }})"
                                                class="btn btn-sm btn-primary" title="Ver roles"><svg xmlns="http://w3.org" width="16" height="16"
                                                    fill="currentColor" class="bi bi-person-badge me-2"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                                    <path
                                                        d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492z" />
                                                </svg>
                                            </button>
                                            @can('gestionar usuarios')
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    wire:click="eliminarUsuario({{ $u->id }})"
                                                    wire:confirm="¿Estás seguro de que deseas eliminar permanentemente al usuario {{ $u->name }}?"
                                                    title="Eliminar usuario">
                                                    <!-- Ícono SVG nativo si no usas FontAwesome -->
                                                    <svg xmlns="http://w3.org" width="16" height="16"
                                                        fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                        <path
                                                            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                                                        <path
                                                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="text-muted small">Sin permisos</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        {{ $usuarios->links() }}
                    </div>
                </div>
            </div>
            <!-- Pega este bloque justo DEBAJO de la tarjeta de "Operadores del Sistema Electoral" (Cierre de la primera tarjeta en la col-md-6) -->
            {{-- <div class="card card-secondary card-outline mt-3 shadow-sm" wire:key="panel-mantenimiento-roles">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold text-secondary"><i class="fas fa-cogs mr-2"></i>
                        Mantenimiento de Roles del Sistema</h3>
                </div>
                <div class="card-body">
                    <!-- Formulario Inline para Crear Rol -->
                    <form wire:submit.prevent="crearRol" class="form-inline mb-3">
                        <div class="input-group input-group-sm w-100">
                            <input type="text" wire:model="nuevo_rol_nombre" class="form-control"
                                placeholder="Nombre del nuevo rol (Ej: Auditor)..." required>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success font-weight-bold">
                                    <i class="fas fa-plus mr-1"></i> Crear Rol
                                </button>
                            </div>
                        </div>
                        @error('nuevo_rol_nombre')
                            <small class="text-danger font-weight-bold mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </form>

                    <!-- Listado resumido de Roles del sistema con opción de eliminación -->
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-striped table-bordered text-center m-0 text-xs">
                            <thead>
                                <tr class="text-muted bg-light">
                                    <th class="text-left" style="width: 70%;">Rol Registrado</th>
                                    <th style="width: 30%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todosLosRoles as $r)
                                    <tr>
                                        <td class="text-left align-middle font-weight-bold text-dark px-2">
                                            <i class="fas fa-user-tag text-muted mr-1"></i> {{ $r->name }}
                                        </td>
                                        <td class="align-middle">
                                            @if ($r->name !== 'Administrador')
                                                <button type="button" wire:click="eliminarRol({{ $r->id }})"
                                                    wire:confirm="¿Está seguro de eliminar este Rol? Se revocará automáticamente de todos los usuarios que lo posean."
                                                    class="btn btn-xs btn-outline-danger font-weight-bold px-2">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                                </button>
                                            @else
                                                <span class="text-muted text-xs font-italic"><i class="fas fa-lock"></i>
                                                    Sistema</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
 --}}
        </div>

        <!-- COLUMNA DERECHA: ASIGNACIÓN DE ROLES Y PERMISOS EN VIVO -->
        <div class="col-md-4">
            @if ($usuario_seleccionado_id && $usuarioDetalle)
                <div class="card card-outline card-success shadow-sm"
                    wire:key="panel-spatie-usuario-{{ $usuario_seleccionado_id }}">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title font-weight-bold text-success"><i class="fas fa-shield-alt mr-2"></i>
                            Privilegios: {{ $usuarioDetalle->name }}</h3>
                        <button type="button" wire:click="limpiarSeleccion" class="close ml-auto" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- BLOQUE 1: ASIGNACIÓN DE ROLES (Muchos a Muchos) -->
                        <h5 class="text-dark font-weight-bold border-bottom pb-2 mb-3"><i
                                class="fas fa-user-tag text-teal mr-1"></i> Roles de Perfil</h5>
                        <div class="row mb-4">
                            @forelse($todosLosRoles as $rol)
                                @php $tieneRol = in_array($rol->name, $roles_usuario); @endphp
                                <div class="col-md-6 mb-2">
                                    <div
                                        class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                        <input type="checkbox" class="custom-control-input"
                                            id="switch-role-{{ $rol->id }}"
                                            wire:click="alternarRol('{{ $rol->name }}')"
                                            {{ $tieneRol ? 'checked' : '' }}>
                                        <label
                                            class="custom-control-label font-weight-bold {{ $tieneRol ? 'text-success' : 'text-muted' }}"
                                            for="switch-role-{{ $rol->id }}">
                                            {{ $rol->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted text-xs">No hay roles creados en Spatie.
                                    Ejecute su RoleSeeder.</div>
                            @endforelse
                        </div>

                        <!-- BLOQUE 2: ASIGNACIÓN DE PERMISOS DIRECTOS -->
                        {{-- <h5 class="text-dark font-weight-bold border-bottom pb-2 mb-3"><i
                                class="fas fa-key text-orange mr-1"></i> Permisos Especiales Directos</h5>
                        <div class="row">
                            @forelse($todosLosPermisos as $permiso)
                                @php $tienePermiso = in_array($permiso->name, $permisos_usuario); @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="check-perm-{{ $permiso->id }}"
                                            wire:click="alternarPermiso('{{ $permiso->name }}')"
                                            {{ $tienePermiso ? 'checked' : '' }}>
                                        <label
                                            class="custom-control-label text-sm {{ $tienePermiso ? 'text-primary font-weight-bold' : 'text-muted' }}"
                                            for="check-perm-{{ $permiso->id }}">
                                            {{ $permiso->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted text-xs">No hay permisos individuales
                                    registrados en el sistema.</div>
                            @endforelse
                        </div> --}}
                    </div>
                    <div class="card-footer bg-light text-xs text-muted">
                        <i class="fas fa-info-circle mr-1 text-info"></i> Los cambios aplicados mediante los
                        interruptores se guardan inmediatamente en las tablas relacionales de Spatie de la base de
                        datos.
                    </div>
                    <!-- TARJETA: MANTENIMIENTO DE ROLES CON HERENCIA DE PERMISOS -->
                    {{-- <div class="card card-secondary card-outline mt-3 shadow-sm" wire:key="panel-mantenimiento-roles">
    <div class="card-header">
        <h3 class="card-title font-weight-bold text-secondary"><i class="fas fa-cogs mr-2"></i> Mantenimiento de Roles</h3>
    </div>
    <div class="card-body">
        <!-- Formulario para Crear Rol con Clonación Opcional -->
        <form wire:submit.prevent="crearRol" class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-2">
                        <label class="text-xs">Nombre del nuevo Rol:</label>
                        <input type="text" wire:model="nuevo_rol_nombre" class="form-control form-control-sm" placeholder="Ej: Auditor, Fiscalizador..." required>
                        @error('nuevo_rol_nombre') <small class="text-danger font-weight-bold d-block mt-1">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-2">
                        <label class="text-xs">Copiar permisos de (Plantilla):</label>
                        <select wire:model="rol_plantilla_id" class="form-control form-control-sm">
                            <option value="">-- Crear Vacío (Sin Permisos) --</option>
                            @foreach ($todosLosRoles as $r)
                                <option value="{{ $r->id }}">Copiar de: {{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-2">
                <button type="submit" class="btn btn-sm btn-success font-weight-bold shadow-xs">
                    <i class="fas fa-plus mr-1"></i> Registrar Rol Operativo
                </button>
            </div>
        </form>

        <!-- Listado de Roles Existentes con Opción de Borrado -->
        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
            <table class="table table-sm table-striped table-bordered text-center m-0 text-xs">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="text-left" style="width: 50%;">Rol en el Sistema</th>
                        <th style="width: 20%;">Permisos</th>
                        <th style="width: 30%;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($todosLosRoles as $r)
                        <tr>
                            <td class="text-left align-middle font-weight-bold text-dark px-2">
                                <i class="fas fa-user-tag text-muted mr-1"></i> {{ $r->name }}
                            </td>
                            <td class="align-middle">
                                <span class="badge bg-info">{{ $r->permissions()->count() }}</span>
                            </td>
                            <td class="align-middle">
                                @if (!in_array($r->name, ['Administrador', 'Supervisor', 'Digitador']))
                                    <button type="button" 
                                            wire:click="eliminarRol({{ $r->id }})" 
                                            wire:confirm="¿Está seguro de eliminar este Rol? Se revocará automáticamente de todos los usuarios que lo posean."
                                            class="btn btn-xs btn-outline-danger font-weight-bold px-2">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                @else
                                    <span class="text-muted text-xs font-italic"><i class="fas fa-lock"></i> Bloqueado</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> --}}

                </div>
            @else
                <!-- PANTALLA DE ESPERA INICIAL -->
                <div class="card card-light border shadow-sm p-5 text-center text-secondary d-flex flex-column justify-content-center"
                    style="height: 100%; min-height: 400px; background-color: #f8f9fa;">
                    <div>
                        <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                        <h5 class="font-weight-bold text-dark">Gestión de Perfiles y Roles (Spatie)</h5>
                        <p class="text-muted text-sm mx-auto mb-0" style="max-width: 400px;">
                            Seleccione un operador del listado izquierdo para auditar sus privilegios actuales, otorgar
                            roles ejecutivos o revocar accesos a módulos de manera instantánea.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endcanany
</div>
