<?php

namespace Tests\Feature;

use App\Livewire\Calculadora;
use App\Models\CalculatorQuery;
use App\Models\PostalService;
use App\Models\Precios;
use App\Models\User;
use App\Support\CalculatorHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CalculatorApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDatabaseName() !== 'calcupost_test') {
            throw new \RuntimeException('API tests require calcupost_test.');
        }
        DB::beginTransaction();
        config(['pulse.enabled' => false]);
        foreach (['NAT', 'INT'] as $mode) {
            PostalService::create(['code' => 'QA_API', 'name' => 'Servicio renombrado',
                'category' => 'QA API '.$mode, 'modality' => $mode, 'profile' => 'LC/AO '.$mode]);
            Precios::create(['categoria' => 'QA API '.$mode, 'peso_min_kg' => 0.001,
                'peso_max_kg' => 20, 'nacional' => 10, 'dest_a' => 100, 'local_1' => 0]);
        }
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function payload(array $changes = []): array
    {
        return array_replace(['categoria' => 'QA API NAT', 'peso' => 1,
            'destino' => 'nacional_la_paz'], $changes);
    }

    public function test_invalid_inputs_return_validation_errors_without_writing_history(): void
    {
        $before = CalculatorQuery::count();
        foreach ([['destino', 'destino_inexistente'], ['destino', 'dest_a_argentina'],
            ['destino', ['nacional_la_paz']], ['peso', 0], ['peso', -1], ['peso', 'texto'],
            ['largo', -1], ['certificado', 'sí'], ['recibo', 'invalid']] as [$field, $value]) {
            $this->postJson('/api/calcular', $this->payload([$field => $value]))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->postJson('/api/calcular', [])->assertUnprocessable()->assertJsonValidationErrors('categoria');
        $this->postJson('/api/calcular', $this->payload(), ['X-Calculator-Session' => 'not-a-uuid'])
            ->assertUnprocessable()->assertJsonValidationErrors('sesion_consulta');
        $this->postJson('/api/calcular', $this->payload(['categoria' => 'QA UNKNOWN']))->assertNotFound();
        $this->assertSame($before, CalculatorQuery::count());
    }

    public function test_renamed_services_keep_web_api_parity_and_snapshots(): void
    {
        foreach (['NAT', 'INT'] as $mode) {
            $destination = $mode === 'NAT' ? 'nacional_la_paz' : 'dest_a_argentina';
            $input = $this->payload(['categoria' => 'QA API '.$mode, 'destino' => $destination,
                'certificado' => true, 'espreso' => true, 'recibo' => true,
                'largo' => 0.5, 'ancho' => 0.5, 'alto' => 0.6]);
            $component = Livewire::test(Calculadora::class)->set('servicio', 'QA_API')->set('modalidad', $mode);
            foreach (array_diff_key($input, ['categoria' => true]) as $field => $value) {
                $component->set($field, $value);
            }
            $component->call('calcular')->assertHasNoErrors();
            $response = $this->postJson('/api/calcular', $input)->assertOk()
                ->assertJsonPath('tarifa', $mode === 'NAT' ? 25 : 148)->assertJsonPath('registro_guardado', true);
            $this->assertEquals($component->get('resultado'), $response->json('tarifa'));
            $record = CalculatorQuery::findOrFail($response->json('registro_id'));
            $this->assertSame('Servicio renombrado', $record->service_name);
            $this->assertSame($mode, $record->modality);
            $this->assertSame(true, $record->extras['certificado']);
            $this->assertSame(0.6, $record->dimensions['alto']);
            $this->assertTrue(Str::isUuid($response->json('sesion_consulta')));
        }
    }

    public function test_zero_tariff_and_missing_tariff_are_distinct_and_saved(): void
    {
        $zero = $this->postJson('/api/calcular', $this->payload(['destino' => 'local_1']))
            ->assertOk()->assertJsonPath('tarifa', 0)->assertJsonPath('success', true);
        $this->assertSame('calculated', CalculatorQuery::findOrFail($zero->json('registro_id'))->status);
        $missing = $this->postJson('/api/calcular', $this->payload(['peso' => 500]))
            ->assertNotFound()->assertJsonPath('tarifa', null)->assertJsonPath('registro_guardado', true);
        $record = CalculatorQuery::findOrFail($missing->json('registro_id'));
        $this->assertSame('no_rate', $record->status);
        $this->assertNull($record->base_amount);
    }

    public function test_super_package_and_document_modalities_use_international_zones(): void
    {
        foreach (['PAQUE' => 437, 'DOC' => 324] as $modality => $amount) {
            $category = 'QA SUPER '.$modality;
            $code = 'QA_SUPER_'.$modality;
            PostalService::create(['code' => $code, 'name' => 'Super '.$modality,
                'category' => $category, 'modality' => $modality, 'profile' => 'SUPER '.$modality.' NAT']);
            Precios::create(['categoria' => $category, 'peso_min_kg' => 0.001,
                'peso_max_kg' => 20, 'dest_a' => $amount]);

            Livewire::test(Calculadora::class)->set('servicio', $code)
                ->assertSet('modalidad', $modality)->assertSee('Argentina')->assertDontSee('La Paz')
                ->set('destino', 'dest_a_argentina')->set('peso', 1)->call('calcular')
                ->assertHasNoErrors()->assertSet('resultado', (float) $amount);
            $this->postJson('/api/calcular', ['categoria' => $category, 'peso' => 1,
                'destino' => 'dest_a_argentina'])->assertOk()->assertJsonPath('tarifa', $amount);
            $this->postJson('/api/calcular', ['categoria' => $category, 'peso' => 1,
                'destino' => 'nacional_la_paz'])->assertUnprocessable()->assertJsonValidationErrors('destino');
        }
    }

    public function test_reused_session_groups_guests_without_overwriting_calculations(): void
    {
        $session = (string) Str::uuid();
        $ids = [];
        for ($i = 0; $i < 2; $i++) {
            $ids[] = $this->postJson('/api/calcular', $this->payload(['user_id' => 1]),
                ['X-Calculator-Session' => $session])->assertOk()
                ->assertJsonPath('sesion_consulta', $session)->json('registro_id');
        }
        $records = CalculatorQuery::whereIn('id', $ids)->get();
        $this->assertCount(2, $records);
        $this->assertCount(1, $records->pluck('actor_key')->unique());
        $this->assertNull($records[0]->user_id);
        $this->assertSame('guest', $records[0]->actor_type);
    }

    public function test_valid_token_identifies_caller_and_invalid_token_is_rejected(): void
    {
        $user = User::create(['name' => 'API QA', 'email' => Str::uuid().'@example.test', 'password' => 'qa-test']);
        $token = $user->createToken('QA')->plainTextToken;
        $response = $this->postJson('/api/calcular', $this->payload(), ['Authorization' => 'Bearer '.$token])->assertOk();
        $record = CalculatorQuery::findOrFail($response->json('registro_id'));
        $this->assertSame($user->id, $record->user_id);
        $this->assertSame('account', $record->actor_type);
        // HTTP tests share the application: discard the previous request's cached guard.
        $this->app['auth']->forgetGuards();
        $before = CalculatorQuery::count();
        $this->postJson('/api/calcular', $this->payload(), ['Authorization' => 'Bearer invalid'])->assertUnauthorized();
        $this->assertSame($before, CalculatorQuery::count());
    }

    public function test_history_failure_is_not_reported_as_success(): void
    {
        $before = CalculatorQuery::count();
        $this->mock(CalculatorHistory::class)->shouldReceive('save')->once()->andThrow(new \RuntimeException('QA simulated history failure'));
        $this->postJson('/api/calcular', $this->payload())->assertStatus(503)
            ->assertJsonPath('success', false)->assertJsonPath('registro_guardado', false);
        $this->assertSame($before, CalculatorQuery::count());
    }
}
