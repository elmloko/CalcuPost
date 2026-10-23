@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php($dashboardUrl = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home'))
@php($dashboardUrl = config('adminlte.use_route_url', false) ? ($dashboardUrl ? route($dashboardUrl) : '') : ($dashboardUrl ? url($dashboardUrl) : ''))

<a href="{{ $dashboardUrl }}"
    class="{{ $layoutHelper->isLayoutTopnavEnabled() ? 'navbar-brand' : 'brand-link' }} {{ config('adminlte.classes_brand') }}">
    <span class="brand-text font-weight-light {{ config('adminlte.classes_brand_text') }}">
        {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
    </span>
    @unless ($layoutHelper->isLayoutTopnavEnabled())
        <img src="{{ asset('images/correos-bolivia.png') }}" alt="Agencia Boliviana de Correos"
             class="agency-brand-image" width="80" height="26">
    @endunless
</a>
