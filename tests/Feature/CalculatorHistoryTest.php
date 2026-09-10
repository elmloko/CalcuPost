<?php

namespace Tests\Feature;

use App\Livewire\Calculadora;
use App\Livewire\CalculatorAnalytics;
use App\Models\CalculatorQuery;
use App\Models\PostalService;
use App\Models\Precios;
use App\Models\User;
use App\Support\CalculatorReport;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CalculatorHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDatabaseName() !== 'calcupost_test') {
            throw new \RuntimeException('These tests must use calcupost_test, never the application database.');
        }
        DB::beginTransaction();
        config(['pulse.enabled' => false]);
        foreach (['NAT', 'INT'] as $mode) {
            PostalService::create(['code' => 'QA_HISTORY', 'name' => 'Servicio de prueba', 'modality' => $mode,
                'category' => 'QA HISTORY '.$mode, 'profile' => 'EMS '.$mode]);
            Precios::create(['categoria' => 'QA HISTORY '.$mode, 'peso_min_kg' => 0.001, 'peso_max_kg' => 20,
                'nacional' => 25, 'dest_a' => 100]);
        }
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function user(bool $admin = false): User
    {
        return User::create(['name' => 'Usuario de prueba', 'email' => uniqid().'@example.test',
            'password' => 'test-password', 'is_admin' => $admin, 'email_verified_at' => now()]);
    }

    private function calculator(string $mode = 'NAT')
    {
        return Livewire::test(Calculadora::class)->set('servicio', 'QA_HISTORY')->set('modalidad', $mode)
            ->set('destino', $mode === 'NAT' ? 'nacional_la_paz' : 'dest_a_argentina')->set('peso', 2);
    }

    public function test_clear_preserves_queries_and_resets_all_fields_for_the_next_query(): void
    {
        $before = CalculatorQuery::count();
        $component = $this->calculator()->call('calcular')->assertHasNoErrors()->assertSet('resultado', 25.0);
        $first = CalculatorQuery::latest('id')->first();
        $component->set('certificado', true)->set('largo', 20)->call('limpiar')
            ->assertSet('servicio', '')->assertSet('modalidad', '')->assertSet('peso', null)
            ->assertSet('largo', null)->assertSet('certificado', false)->assertSet('resultado', null)
            ->assertSet('registroGuardado', null)->assertHasNoErrors();
        $this->assertSame($before + 1, CalculatorQuery::count());
        $component->set('servicio', 'QA_HISTORY')->set('modalidad', 'INT')->set('destino', 'dest_a_argentina')
            ->set('peso', 1)->call('calcular')->assertHasNoErrors()->assertSet('resultado', 100.0);
        $second = CalculatorQuery::latest('id')->first();
        $this->assertSame($before + 2, CalculatorQuery::count());
        $this->assertSame($first->actor_key, $second->actor_key);
        $this->assertSame('Argentina', $second->destination_name);
        $this->assertSame('La Paz', $first->destination_name);
    }

    public function test_invalid_inputs_are_not_saved_and_cannot_select_a_foreign_destination(): void
    {
        $before = CalculatorQuery::count();
        $this->calculator()->set('destino', 'dest_a_argentina')->call('calcular')->assertHasErrors('destino');
        $this->calculator()->set('destino', 'password')->call('calcular')->assertHasErrors('destino');
        $this->calculator()->set('peso', -1)->call('calcular')->assertHasErrors('peso');
        $this->calculator()->set('largo', -5)->call('calcular')->assertHasErrors('largo');
        Livewire::test(Calculadora::class)->set('servicio', 'QA_HISTORY')->call('calcular')->assertHasErrors('modalidad');
        $this->assertSame($before, CalculatorQuery::count());
    }

    public function test_missing_tariff_is_saved_separately_from_successful_calculations(): void
    {
        $this->calculator()->set('peso', 500)->call('calcular')->assertHasNoErrors()->assertSee('Consulta sin tarifa disponible');
        $record = CalculatorQuery::latest('id')->first();
        $this->assertSame('no_rate', $record->status);
        $this->assertNull($record->total_amount);
        $this->assertNull($record->base_amount);
    }

    public function test_snapshots_survive_service_and_account_deletion_and_hidden_category_cannot_change_pricing(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $this->calculator()->set('categoria', 'EMS INT')->set('perfilCategoria', 'EMS INT')
            ->call('calcular')->assertSet('resultado', 25.0)->assertHasNoErrors();
        $record = CalculatorQuery::latest('id')->first();
        PostalService::where('code', 'QA_HISTORY')->delete();
        $user->forceDelete();
        $record->refresh();
        $this->assertSame('QA HISTORY NAT', $record->category);
        $this->assertSame('Servicio de prueba', $record->service_name);
        $this->assertSame('account', $record->actor_type);
        $this->assertSame('Usuario de prueba', $record->actor_name);
    }

    public function test_records_pdf_and_technical_logs_are_protected(): void
    {
        foreach (['/registros', '/registros/pdf', '/registros/tecnicos', '/registros/tecnicos/api/files'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
        $this->actingAs($this->user());
        foreach (['/registros', '/registros/pdf', '/registros/tecnicos', '/registros/tecnicos/api/files'] as $path) {
            $this->get($path)->assertForbidden();
        }
        $this->actingAs($this->user(true));
        $this->get('/log-viewer')->assertRedirect('/registros');
        $this->get('/registros')->assertOk()->assertSee('Descargar PDF');
        $response = $this->get('/registros/pdf');
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_filtered_records_summary_pdf_dashboard_and_performance_render(): void
    {
        $this->actingAs($this->user(true));
        $this->calculator()->call('calcular')->assertHasNoErrors();
        $this->calculator('INT')->call('calcular')->assertHasNoErrors();
        $filters = ['service' => 'QA_HISTORY', 'modality' => 'NAT', 'actor' => 'account'];
        $this->get('/registros?'.http_build_query($filters))->assertOk()
            ->assertSee('cr-panel--table', false)
            ->assertViewHas('summary', fn ($s) => $s['total'] === 1 && $s['accounts'] === 1);
        $this->get('/registros/pdf?'.http_build_query($filters))->assertOk()->assertHeader('content-type', 'application/pdf');
        $html = view('records.pdf', ['filters' => $filters, 'summary' => CalculatorReport::summary(CalculatorReport::query($filters)), 'records' => CalculatorReport::query($filters)->get()])->render();
        $this->assertStringContainsString('La Paz', $html);
        $this->assertStringNotContainsString('Argentina', $html);
        $this->get('/dashboard')->assertOk()->assertSee('Consultas en un vistazo');
        $this->get('/pulse')->assertOk()->assertSee('Respuesta y disponibilidad de tarifas');
        Livewire::test(CalculatorAnalytics::class)->set('days', 7)->assertSee('7 días, incluido hoy');
        $this->from('/registros')->get('/registros?from=2026-09-10&to=2026-09-01')->assertRedirect('/registros')->assertSessionHasErrors('to');
    }

    public function test_summary_counts_unique_actors_and_filters_dates_inclusively(): void
    {
        $this->calculator()->call('calcular');
        $first = CalculatorQuery::latest('id')->first();
        $first->update(['created_at' => '2026-08-30 04:00:00', 'duration_ms' => 100]);
        $second = $first->replicate();
        $second->fill(['created_at' => '2026-08-31 03:59:59', 'duration_ms' => 200])->save();
        $third = $first->replicate();
        $third->fill(['created_at' => '2026-08-31 04:00:00', 'duration_ms' => 1500, 'actor_type' => 'account', 'actor_key' => str_repeat('a', 64), 'status' => 'no_rate', 'total_amount' => null])->save();
        $report = CalculatorReport::analytics(['service' => 'QA_HISTORY', 'from' => '2026-08-30', 'to' => '2026-08-31']);
        $this->assertSame(3, $report['summary']['total']);
        $this->assertSame(1, $report['summary']['guests']);
        $this->assertSame(1, $report['summary']['accounts']);
        $this->assertSame(1, $report['summary']['no_rate']);
        $this->assertSame(1, $report['summary']['slow']);
        $this->assertSame(600.0, $report['summary']['average_ms']);
        $this->assertEquals(1500, $report['p95']);
        $this->assertSame(2, CalculatorReport::query(['service' => 'QA_HISTORY', 'from' => '2026-08-30', 'to' => '2026-08-30'])->count());
    }

    public function test_pagination_and_pdf_export_limit_do_not_silently_drop_records(): void
    {
        $this->actingAs($this->user(true));
        $this->calculator()->call('calcular')->assertHasNoErrors();
        $first = CalculatorQuery::latest('id')->first();
        for ($i = 1; $i < 32; $i++) {
            $record = $first->replicate();
            $record->fill(['modality' => $i % 2 ? 'INT' : 'NAT',
                'destination_name' => $i % 2 ? 'República Democrática del Congo' : 'Provincia en Otro Departamento',
                'actor_name' => $i % 2 ? 'María Alejandra Fernández de la Cruz' : 'Visitante anónimo',
                'dimensions' => ['largo' => 100, 'ancho' => 50, 'alto' => 25],
                'extras' => ['certificado' => true, 'expreso' => true, 'recibo' => true],
                'created_at' => now()->subDays($i % 7), 'duration_ms' => 12 + $i * 30])->save();
        }
        $this->get('/registros?service=QA_HISTORY&per_page=20&page=2')->assertOk()->assertViewHas('records', fn ($r) => $r->total() === 32 && $r->count() === 12);
        $this->get('/registros?service=QA_HISTORY')->assertOk()->assertViewHas('records',
            fn ($r) => $r->total() === 32 && $r->count() === 32 && $r->lastPage() === 1);
        $pdf = $this->get('/registros/pdf?service=QA_HISTORY');
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        if (getenv('CALCULATOR_REPORT_QA')) {
            \Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8000');
            \Illuminate\Support\Facades\File::ensureDirectoryExists(base_path('tmp/pdfs'));
            \Illuminate\Support\Facades\File::put(base_path('tmp/pdfs/records-qa.pdf'), $pdf->getContent());
            foreach (['records' => '/registros?service=QA_HISTORY', 'dashboard' => '/dashboard', 'performance' => '/pulse'] as $name => $url) {
                \Illuminate\Support\Facades\File::put(base_path('tmp/pdfs/'.$name.'-qa.html'), $this->get($url)->getContent());
            }
            \Illuminate\Support\Facades\URL::forceRootUrl(null);
        }
        $row = $first->getAttributes();
        unset($row['id']);
        DB::table('calculator_queries')->insert(array_fill(0, 969, $row));
        $this->from('/registros')->get('/registros/pdf?service=QA_HISTORY')->assertRedirect('/registros')->assertSessionHasErrors('export');
    }

    public function test_local_dates_convert_without_changing_utc_storage_and_filter_across_midnight(): void
    {
        $this->actingAs($this->user(true));
        $this->calculator()->call('calcular')->assertHasNoErrors();
        $first = CalculatorQuery::latest('id')->first();
        $first->update(['created_at' => '2026-09-02 03:59:59']);
        $second = $first->replicate();
        $second->fill(['created_at' => '2026-09-02 04:00:00'])->save();
        $third = $first->replicate();
        $third->fill(['created_at' => '2026-09-02 15:35:15'])->save();
        $this->assertSame('01/09/2026 23:59:59', $first->local_created_at->format('d/m/Y H:i:s'));
        $this->assertSame('2026-09-02 03:59:59', $first->created_at->toDateTimeString());
        $this->assertSame('02/09/2026 11:35:15', $third->local_created_at->format('d/m/Y H:i:s'));
        $filters = ['service' => 'QA_HISTORY', 'from' => '2026-09-01', 'to' => '2026-09-01'];
        $this->assertSame([$first->id], CalculatorReport::query($filters)->pluck('id')->all());
        $this->get('/registros?'.http_build_query($filters))->assertOk()
            ->assertSee('01/09/2026 23:59:59')->assertSee('Bolivia (UTC-04:00)')->assertDontSee('02/09/2026 00:00:00');
        $report = CalculatorReport::analytics(['service' => 'QA_HISTORY', 'from' => '2026-09-01', 'to' => '2026-09-02']);
        $this->assertSame(1, (int) $report['daily']['2026-09-01']->total);
        $this->assertSame(2, (int) $report['daily']['2026-09-02']->total);
        $html = view('records.pdf', ['filters' => $filters,
            'summary' => CalculatorReport::summary(CalculatorReport::query($filters)),
            'records' => CalculatorReport::query($filters)->get()])->render();
        $this->assertStringContainsString('01/09/2026', $html);
        $this->assertStringContainsString('23:59:59', $html);
        $this->assertStringContainsString('Bolivia (UTC-04:00)', $html);
    }

    public function test_today_and_pdf_filename_use_bolivian_date_before_utc_dawn(): void
    {
        $this->actingAs($this->user(true));
        $this->travelTo(\Carbon\Carbon::parse('2026-09-02 02:30:00', 'UTC'));
        try {
            $this->calculator()->call('calcular')->assertHasNoErrors();
            $record = CalculatorQuery::latest('id')->first();
            $this->assertSame('2026-09-02 02:30:00', $record->created_at->toDateTimeString());
            $this->assertSame('2026-09-01 22:30:00', $record->local_created_at->toDateTimeString());
            Livewire::test(CalculatorAnalytics::class)->assertViewHas('filters',
                fn ($filters) => $filters['to'] === '2026-09-01' && $filters['from'] === '2026-08-03')
                ->assertSee('22:30:00')->assertSee('Bolivia (UTC-04:00)');
            $pdf = $this->get('/registros/pdf?service=QA_HISTORY')->assertOk();
            $this->assertStringContainsString('registros-postales-2026-09-01.pdf', $pdf->headers->get('Content-Disposition'));
        } finally {
            $this->travelBack();
        }
    }
}
