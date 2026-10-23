<aside class="main-sidebar has-account-menu {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>

    @auth
        <details class="sidebar-account">
            <summary aria-label="Opciones de perfil" aria-controls="sidebar-account-options">
                <span class="sidebar-account__avatar"><i class="fas fa-user" aria-hidden="true"></i></span>
                <span class="sidebar-account__text"><strong>Perfil</strong><small>{{ Auth::user()->name }}</small></span>
                <i class="fas fa-chevron-up sidebar-account__arrow" aria-hidden="true"></i>
            </summary>
            <div class="sidebar-account__panel" id="sidebar-account-options">
                <div class="sidebar-account__identity">
                    <strong>{{ Auth::user()->name }}</strong>
                    <span>{{ Auth::user()->isAdministrator() ? 'Administrador' : 'Usuario' }}</span>
                    <small>{{ Auth::user()->email }}</small>
                </div>
                <a href="{{ route('profile.edit') }}"><i class="fas fa-user-circle" aria-hidden="true"></i> Ver mi perfil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Cerrar sesión</button>
                </form>
            </div>
        </details>
    @endauth
</aside>
