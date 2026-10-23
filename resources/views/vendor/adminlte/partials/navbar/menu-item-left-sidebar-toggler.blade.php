@if (config('adminlte.sidebar_collapse_remember'))
    <script>
        (() => {
            try {
                const key = 'remember.lte.pushmenu';
                const saved = localStorage.getItem(key);
                if (saved === 'sidebar-open') {
                    document.body.classList.remove('sidebar-collapse');
                } else if (saved === 'sidebar-collapse') {
                    document.body.classList.add('sidebar-collapse');
                } else {
                    localStorage.setItem(key, document.body.classList.contains('sidebar-collapse') ? 'sidebar-collapse' : 'sidebar-open');
                }
            } catch (error) {
                // The regular menu toggle still works if browser storage is unavailable.
            }
        })();
    </script>
@endif

<li class="nav-item">
    <a class="nav-link" data-widget="pushmenu" href="#"
        @if (config('adminlte.sidebar_collapse_remember'))
            data-enable-remember="true"
        @endif
        @if (! config('adminlte.sidebar_collapse_remember_no_transition'))
            data-no-transition-after-reload="false"
        @endif
        @if (config('adminlte.sidebar_collapse_auto_size'))
            data-auto-collapse-size="{{ config('adminlte.sidebar_collapse_auto_size') }}"
        @endif>
        <i class="fas fa-bars"></i>
        <span class="sr-only">{{ __('adminlte::adminlte.toggle_navigation') }}</span>
    </a>
</li>
