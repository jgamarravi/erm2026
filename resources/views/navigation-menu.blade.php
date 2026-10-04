<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle fs-5"></i>
        <span>{{ Auth::user()?->name }}</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
        <li>
            <a class="dropdown-menu-item d-block px-3 py-2 text-secondary text-decoration-none" href="{{ route('profile.show') }}">
                <i class="bi bi-gear me-2"></i> Mi Perfil
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <!-- Formulario nativo POST para Jetstream -->
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="dropdown-menu-item d-block w-100 px-3 py-2 text-danger border-0 bg-transparent text-start">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                </button>
            </form>
        </li>
    </ul>
</li>
