<?php

namespace App\Livewire;

use App\Models\PostalService;
use App\Support\CalculatorHistory;
use App\Support\PostalCalculator;
use App\Support\PostalDestinations;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Calculadora extends Component
{
    public $servicio = '';
    public $modalidad = '';
    public $categoria;
    public $perfilCategoria = '';
    public $destino;
    public $busquedaDestino = '';
    public array $opcionesDestino = [];
    public $peso;
    public $largo;
    public $ancho;
    public $alto;
    public $resultado;
    public $certificado = false;
    public $espreso = false;
    public $recibo = false;
    public $registroGuardado;

    public function updatedServicio(): void
    {
        $this->modalidad = '';
        $this->categoria = '';
        $this->perfilCategoria = '';
        $this->reiniciarCalculo();

        if (count($this->modalidades) === 1) {
            $this->modalidad = array_key_first($this->modalidades);
            $this->sincronizarCategoria();
            return;
        }

        $this->dispatch('categoria-cambiada', categoria: '', perfil: '', modalidad: '');
    }

    public function updatedModalidad(): void
    {
        $this->sincronizarCategoria();
    }

    public function getModalidadesProperty(): array
    {
        return PostalService::where('code', $this->servicio)
            ->orderByRaw("FIELD(modality, 'NAT', 'INT', 'PAQUE', 'DOC')")
            ->pluck('category', 'modality')
            ->all();
    }

    public function getCatalogoServiciosProperty(): array
    {
        return PostalService::query()
            ->orderBy('name')
            ->get()
            ->groupBy('code')
            ->map(fn ($services) => $services->first()->name)
            ->all();
    }

    private function sincronizarCategoria(): void
    {
        $this->categoria = $this->modalidades[$this->modalidad] ?? '';
        $this->perfilCategoria = PostalService::where('category', $this->categoria)->value('profile') ?? '';
        $this->reiniciarCalculo();
        $this->dispatch('categoria-cambiada', categoria: $this->categoria, perfil: $this->perfilCategoria, modalidad: $this->modalidad);
    }

    private function reiniciarCalculo(): void
    {
        $this->opcionesDestino = $this->getDestinosProperty();
        $this->destino = '';
        $this->busquedaDestino = '';
        $this->resultado = null;
        $this->registroGuardado = null;
        $this->resetValidation();
    }

    public function getDestinosProperty(): array
    {
        return PostalDestinations::forService(PostalService::where('code', $this->servicio)
            ->where('modality', $this->modalidad)->first());
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['destino', 'peso', 'largo', 'ancho', 'alto', 'certificado', 'espreso', 'recibo'], true)) {
            $this->resultado = null;
            $this->registroGuardado = null;
            $this->resetValidation();
        }
    }

    public function updatedBusquedaDestino(): void
    {
        $this->destino = array_search($this->busquedaDestino, $this->destinos, true) ?: '';
        $this->resultado = null;
        $this->registroGuardado = null;
        $this->resetValidation('destino');
    }

    public function limpiar(): void
    {
        $this->reset('servicio', 'modalidad', 'categoria', 'perfilCategoria', 'destino', 'busquedaDestino', 'opcionesDestino', 'peso',
            'largo', 'ancho', 'alto', 'resultado', 'certificado', 'espreso', 'recibo', 'registroGuardado');
        $this->resetValidation();
    }

    public function calcular()
    {
        $started = hrtime(true);
        $this->resultado = null;
        $this->registroGuardado = null;
        $this->validate([
            'servicio' => ['required', 'string', Rule::exists('postal_services', 'code')],
            'modalidad' => ['required', Rule::in(array_keys($this->modalidades))],
        ], [
            'required' => 'Completa el campo :attribute.',
            'in' => 'Selecciona una opción válida para :attribute.',
            'exists' => 'El servicio seleccionado ya no está disponible.',
        ]);

        $service = PostalService::where('code', $this->servicio)->where('modality', $this->modalidad)->firstOrFail();
        $this->categoria = $service->category;
        $this->perfilCategoria = $service->profile;
        $calculation = app(PostalCalculator::class)->calculate($service, $this->only([
            'destino', 'peso', 'largo', 'ancho', 'alto', 'certificado', 'espreso', 'recibo',
        ]));
        $this->resultado = $calculation['total'] ?? (($calculation['reason'] ?? null) === 'ambiguous'
            ? 'Hay tarifas distintas para el mismo peso. Contacte a un administrador.'
            : 'No se encontraron tarifas para los datos ingresados.');

        if (!session()->has('calculator_visitor')) {
            session()->put('calculator_visitor', (string) Str::uuid());
        }
        try {
            $record = app(CalculatorHistory::class)->save(
                $service, $calculation, $started, auth()->user(), session('calculator_visitor')
            );
            $this->registroGuardado = $record->id;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('historial', 'El cálculo está disponible, pero no se pudo guardar en el historial. Inténtalo de nuevo antes de limpiar.');
        }
    }

    public function render()
    {
        return view('livewire.calculadora');
    }
}
