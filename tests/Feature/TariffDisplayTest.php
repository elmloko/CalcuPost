<?php

namespace Tests\Feature;

use App\Livewire\Tarifario;
use App\Models\PostalService;
use App\Models\Precios;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class TariffDisplayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDatabaseName() !== 'calcupost_test') {
            throw new \RuntimeException('Tariff tests require calcupost_test.');
        }
        DB::beginTransaction();
        config(['pulse.enabled' => false]);
        $this->actingAs(User::create(['name' => 'Tariff QA', 'email' => Str::uuid().'@example.test',
            'password' => 'test-password', 'is_admin' => true]));
        foreach (['NAT' => 42, 'INT' => 84] as $mode => $price) {
            PostalService::create(['code' => 'QA_DISPLAY', 'name' => 'Tarifario QA',
                'modality' => $mode, 'category' => 'QA DISPLAY '.$mode, 'profile' => 'EMS '.$mode]);
            Precios::create(['categoria' => 'QA DISPLAY '.$mode, 'peso_min_kg' => 1,
                'peso_max_kg' => 2, 'nacional' => $price, 'dest_a' => $price]);
        }
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) { DB::rollBack(); }
        parent::tearDown();
    }

    public function test_service_then_modality_loads_the_correct_tariff(): void
    {
        $component = Livewire::test(Tarifario::class)->assertSee('Selecciona primero un servicio postal')
            ->call('selectService', 'QA_DISPLAY')->assertSet('activeTab', '')
            ->assertSee('Nacional')->assertSee('Internacional');
        $component->call('selectModality', 'NAT')->assertSet('activeTab', 'QA DISPLAY NAT')
            ->assertViewHas('tarifarios', fn ($rows) => $rows->total() === 1 && (float) $rows[0]->nacional === 42.0);
        $component->call('selectModality', 'INT')->assertSet('activeTab', 'QA DISPLAY INT')
            ->assertViewHas('tarifarios', fn ($rows) => $rows->total() === 1 && (float) $rows[0]->dest_a === 84.0);
    }

    public function test_encomienda_is_one_service_with_both_modalities(): void
    {
        Livewire::test(Tarifario::class)
            ->assertSee('Encomienda')
            ->assertDontSee('Mi Encomienda')
            ->call('selectService', 'ENCOMIENDA')
            ->assertSee('Nacional')
            ->assertSee('Internacional');
    }

    public function test_empty_service_is_explained_instead_of_showing_a_blank_table(): void
    {
        PostalService::create(['code' => 'QA_EMPTY', 'name' => 'Empty QA', 'modality' => 'NAT',
            'category' => 'QA EMPTY NAT', 'profile' => 'CUSTOM NAT']);
        Livewire::test(Tarifario::class)->call('selectService', 'QA_EMPTY')
            ->assertSet('activeTab', 'QA EMPTY NAT')->assertSee('No hay tarifas registradas para')
            ->assertSee('Empty QA')->assertSee('Nacional')
            ->assertSee('entorno de pruebas');
    }

    public function test_database_scoped_cache_ignores_old_shared_values(): void
    {
        Cache::put('tarifario:QA DISPLAY NAT', collect(), 3600);
        $testKey = Precios::tariffCacheKey('QA DISPLAY NAT');
        $default = DB::getDefaultConnection();
        config(['database.connections.key_only' => array_replace(config('database.connections.'.$default),
            ['database' => 'calcupost', 'url' => null])]);
        try {
            // Read connection metadata only: never connect to or modify the main database.
            DB::setDefaultConnection('key_only');
            $mainKey = Precios::tariffCacheKey('QA DISPLAY NAT');
        } finally {
            DB::setDefaultConnection($default);
        }
        $this->assertNotSame($testKey, $mainKey);
        Cache::put($mainKey, collect(), 3600);
        Livewire::test(Tarifario::class)->call('selectService', 'QA_DISPLAY')->call('selectModality', 'NAT')
            ->assertViewHas('tarifarios', fn ($rows) => $rows->total() === 1);
        $this->assertCount(1, Cache::get($testKey));
        $this->assertCount(0, Cache::get($mainKey));
    }

    public function test_edit_refreshes_the_scoped_tariff_cache(): void
    {
        $tariff = Precios::where('categoria', 'QA DISPLAY NAT')->firstOrFail();
        $component = Livewire::test(Tarifario::class)->call('selectService', 'QA_DISPLAY')->call('selectModality', 'NAT');
        $component->call('editTarifa', $tariff->id)->assertSet('editingId', $tariff->id)
            ->assertNotDispatched('openModal')->assertSee('Guardar')
            ->set('nacional', 55)->call('updateTarifa')->assertHasNoErrors()
            ->assertViewHas('tarifarios', fn ($rows) => (float) $rows[0]->nacional === 55.0);
    }

    public function test_delete_uses_an_explicit_confirmation_popup(): void
    {
        $tariff = Precios::where('categoria', 'QA DISPLAY NAT')->firstOrFail();
        $component = Livewire::test(Tarifario::class)
            ->call('selectService', 'QA_DISPLAY')->call('selectModality', 'NAT')
            ->call('solicitarEliminacionTarifa', $tariff->id)
            ->assertSet('deletingId', $tariff->id)
            ->assertSee('¿Eliminar esta tarifa?')
            ->assertSee('Conservar tarifa');

        $this->assertDatabaseHas('tarifario', ['id' => $tariff->id]);
        $component->call('cancelarEliminacionTarifa')->assertSet('deletingId', null)
            ->assertDontSee('¿Eliminar esta tarifa?');
        $this->assertDatabaseHas('tarifario', ['id' => $tariff->id]);
    }

    public function test_published_livewire_assets_match_the_installed_package(): void
    {
        $this->assertSame(file_get_contents(base_path('vendor/livewire/livewire/dist/manifest.json')),
            file_get_contents(public_path('vendor/livewire/manifest.json')));
        $this->assertSame(hash_file('sha256', base_path('vendor/livewire/livewire/dist/livewire.js')),
            hash_file('sha256', public_path('vendor/livewire/livewire.js')));
    }
}
