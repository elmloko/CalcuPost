<?php

namespace Tests\Feature;

use App\Livewire\Tarifario;
use App\Models\PostalService;
use App\Models\Precios;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
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

    public function test_public_contracts_are_removed(): void
    {
        $this->get('/tarifario/contratos-publicos')->assertNotFound();
        $this->get('/tarifario')->assertOk()->assertDontSee('Contratos públicos');
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('contract_tariffs'));
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

    public function test_non_admin_cannot_change_tariffs_through_livewire(): void
    {
        $tariff = Precios::where('categoria', 'QA DISPLAY NAT')->firstOrFail();
        $this->actingAs(User::create(['name' => 'Regular QA', 'email' => Str::uuid().'@example.test',
            'password' => 'test-password', 'is_admin' => false]));

        Livewire::test(Tarifario::class)->set('editingId', $tariff->id)
            ->call('updateTarifa')->assertStatus(403);
        Livewire::test(Tarifario::class)->set('deletingId', $tariff->id)
            ->call('deleteTarifa')->assertStatus(403);
        Livewire::test(Tarifario::class)->call('createTarifa')->assertStatus(403);
        $this->assertDatabaseHas('tarifario', ['id' => $tariff->id, 'nacional' => 42]);
    }

    public function test_edit_rejects_overlapping_weight_brackets(): void
    {
        $tariff = Precios::where('categoria', 'QA DISPLAY NAT')->firstOrFail();
        Precios::create(['categoria' => 'QA DISPLAY NAT', 'peso_min_kg' => 2.001,
            'peso_max_kg' => 3, 'nacional' => 60]);

        Livewire::test(Tarifario::class)->call('selectService', 'QA_DISPLAY')
            ->call('selectModality', 'NAT')->call('editTarifa', $tariff->id)
            ->set('peso_max_kg', 2.5)->call('updateTarifa')->assertHasErrors('peso_min_kg');
        $this->assertDatabaseHas('tarifario', ['id' => $tariff->id, 'peso_max_kg' => 2]);
    }

    public function test_create_rejects_overlapping_weight_brackets(): void
    {
        $before = Precios::where('categoria', 'QA DISPLAY NAT')->count();

        $component = Livewire::test(Tarifario::class)->call('selectService', 'QA_DISPLAY')
            ->call('selectModality', 'NAT')->call('prepararCreacion')
            ->set('nuevoServicio', 'QA_DISPLAY')
            ->set('nuevaModalidad', 'NAT')->set('peso_min_kg', 1.5)
            ->set('peso_max_kg', 3)->set('nacional', 60)
            ->call('createTarifa');
        $component->assertHasErrors('peso_min_kg');

        $this->assertSame($before, Precios::where('categoria', 'QA DISPLAY NAT')->count());
    }

    public function test_create_keeps_the_current_category_of_a_renamed_service(): void
    {
        Livewire::test(Tarifario::class)->call('selectService', 'QA_DISPLAY')
            ->call('selectModality', 'NAT')->call('prepararCreacion')
            ->set('nuevoServicio', 'QA_DISPLAY')->set('nuevaModalidad', 'NAT')
            ->set('peso_min_kg', 2.001)->set('peso_max_kg', 3)
            ->set('nacional', 60)->call('createTarifa')->assertHasNoErrors();

        $this->assertDatabaseHas('tarifario', ['categoria' => 'QA DISPLAY NAT',
            'peso_min_kg' => 2.001, 'peso_max_kg' => 3, 'nacional' => 60]);
    }

    public function test_read_only_tariff_check_reports_conflicting_ranges(): void
    {
        $this->assertSame(0, Artisan::call('tariffs:check', ['--category' => 'QA DISPLAY NAT']));
        Precios::create(['categoria' => 'QA DISPLAY NAT', 'peso_min_kg' => 1.5,
            'peso_max_kg' => 3, 'nacional' => 60]);
        $this->assertSame(1, Artisan::call('tariffs:check', ['--category' => 'QA DISPLAY NAT']));
        $this->assertDatabaseHas('tarifario', ['categoria' => 'QA DISPLAY NAT', 'peso_min_kg' => 1.5]);
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

    public function test_create_persists_every_visible_ems_field_and_ignores_hidden_prices(): void
    {
        Livewire::test(Tarifario::class)->call('prepararCreacion')
            ->set('nuevoServicio', 'QA_DISPLAY')->set('nuevaModalidad', 'NAT')
            ->set('peso_min_kg', 2.001)->set('peso_max_kg', 3)
            ->set('pro_dentro', 75.25)->set('dest_a', -999)
            ->call('createTarifa')->assertHasNoErrors();

        $this->assertDatabaseHas('tarifario', ['categoria' => 'QA DISPLAY NAT',
            'peso_min_kg' => 2.001, 'pro_dentro' => 75.25, 'dest_a' => null]);
    }

    public function test_edit_does_not_write_unvalidated_hidden_prices(): void
    {
        $tariff = Precios::where('categoria', 'QA DISPLAY NAT')->firstOrFail();
        Livewire::test(Tarifario::class)->call('selectService', 'QA_DISPLAY')
            ->call('selectModality', 'NAT')->call('editTarifa', $tariff->id)
            ->set('dest_a', -999)->set('nacional', 55)->call('updateTarifa')->assertHasNoErrors();

        $this->assertDatabaseHas('tarifario', ['id' => $tariff->id, 'nacional' => 55, 'dest_a' => 42]);
    }

    public function test_tariff_values_must_fit_storage_without_rounding_or_overflow(): void
    {
        $tariff = Precios::where('categoria', 'QA DISPLAY NAT')->firstOrFail();
        foreach ([['nacional', 1.001], ['nacional', '1000000000000.00'],
            ['peso_min_kg', 1000000], ['peso_max_kg', 1000000], ['peso_max_kg', 0]] as [$field, $value]) {
            Livewire::test(Tarifario::class)->call('selectService', 'QA_DISPLAY')
                ->call('selectModality', 'NAT')->call('editTarifa', $tariff->id)
                ->set($field, $value)->call('updateTarifa')->assertHasErrors($field);
        }
        $this->assertDatabaseHas('tarifario', ['id' => $tariff->id, 'nacional' => 42,
            'peso_min_kg' => 1, 'peso_max_kg' => 2]);
    }

    public function test_service_names_must_generate_a_nonempty_code(): void
    {
        $before = PostalService::count();
        Livewire::test(Tarifario::class)->set('nombreNuevoServicio', '!!!')
            ->set('modalidadesNuevoServicio', ['NAT'])->call('crearServicioPostal')
            ->assertHasErrors('nombreNuevoServicio');
        $this->assertSame($before, PostalService::count());
    }

    public function test_special_modalities_can_be_renamed_without_losing_their_tariffs(): void
    {
        PostalService::create(['code' => 'QA_SUPER', 'name' => 'Super QA', 'modality' => 'PAQUE',
            'category' => 'QA SUPER PAQUE', 'profile' => 'SUPER PAQUE NAT']);
        $tariff = Precios::create(['categoria' => 'QA SUPER PAQUE', 'peso_min_kg' => 1,
            'peso_max_kg' => 2, 'dest_a' => 123]);

        Livewire::test(Tarifario::class)->call('prepararEdicionServicio', 'QA_SUPER')
            ->set('nombreNuevoServicio', 'Super renombrado QA')->call('actualizarServicioPostal')
            ->assertHasNoErrors()->assertSet('activeTab', 'SUPER RENOMBRADO QA PAQUE');
        $this->assertDatabaseHas('postal_services', ['code' => 'SUPER_RENOMBRADO_QA',
            'modality' => 'PAQUE', 'profile' => 'SUPER PAQUE NAT']);
        $this->assertDatabaseHas('tarifario', ['id' => $tariff->id,
            'categoria' => 'SUPER RENOMBRADO QA PAQUE', 'dest_a' => 123]);
    }

    public function test_published_livewire_assets_match_the_installed_package(): void
    {
        $this->assertSame(file_get_contents(base_path('vendor/livewire/livewire/dist/manifest.json')),
            file_get_contents(public_path('vendor/livewire/manifest.json')));
        $this->assertSame(hash_file('sha256', base_path('vendor/livewire/livewire/dist/livewire.js')),
            hash_file('sha256', public_path('vendor/livewire/livewire.js')));
    }
}
