<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PostalService;
use App\Models\Precios;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;

class Tarifario extends Component
{
    use WithPagination;

    private const CAMPOS_TARIFA = ['peso_min_kg', 'peso_max_kg', 'dest_a', 'dest_b', 'dest_c', 'dest_d', 'dest_e', 'dest_f', 'dest_g', 'local_1', 'local_2', 'local_3', 'local_4', 'nacional', 'cui_int', 'cui_cap', 'cui1', 'cui2', 'pro_dentro', 'pro_otro'];
    public $activeTab = '';
    public string $servicioActivo = '';
    public string $modalidadActiva = '';
    public $editingId = null;
    public ?int $deletingId = null;
    public bool $creando = false;
    public string $nuevoServicio = '';
    public string $nuevaModalidad = '';
    public string $nombreNuevoServicio = '';
    public array $modalidadesNuevoServicio = [];
    public ?string $editandoServicioCodigo = null;
    public $peso_min_kg, $peso_max_kg, $dest_a, $dest_b, $dest_c, $dest_d, $dest_e, $dest_f, $dest_g, $local_1, $local_2, $local_3, $local_4, $nacional, $cui_int, $cui_cap, $cui1, $cui2, $pro_dentro, $pro_otro; // Campos dinámicos

    public function selectService(string $servicio): void
    {
        $servicios = $this->serviceDefinitions();
        abort_unless(array_key_exists($servicio, $servicios), 404);

        $this->servicioActivo = $servicio;
        $this->modalidadActiva = '';
        $this->activeTab = '';
        $this->editingId = null;
        $this->deletingId = null;
        $this->resetInputs();
        $this->resetPage();

        if (count($servicios[$servicio]) === 1) {
            $this->selectModality(array_key_first($servicios[$servicio]));
        }
    }

    public function selectModality(string $modalidad): void
    {
        $categoria = $this->serviceDefinitions()[$this->servicioActivo][$modalidad] ?? null;
        abort_unless($categoria, 404);

        $this->modalidadActiva = $modalidad;
        $this->activeTab = $categoria;
        $this->editingId = null;
        $this->deletingId = null;
        $this->resetInputs();
        $this->resetPage();
    }

    public function getCatalogoServiciosProperty(): array
    {
        $customLabels = PostalService::query()->pluck('name', 'code');

        return collect($this->serviceDefinitions())->map(function (array $modalidades, string $servicio) use ($customLabels) {
            return [
                'label' => $customLabels->get($servicio, $servicio),
                'modalidades' => collect($modalidades)->map(fn (string $categoria, string $codigo) => [
                    'label' => match ($codigo) {
                        'NAT' => 'Nacional',
                        'INT' => 'Internacional',
                        'PAQUE' => 'Paquetería internacional',
                        'DOC' => 'Documentos internacionales',
                    },
                    'categoria' => $categoria,
                ])->all(),
            ];
        })->all();
    }

    private function serviceDefinitions(): array
    {
        $custom = PostalService::query()
            ->orderBy('name')
            ->get()
            ->groupBy('code')
            ->map(fn ($items) => $items->pluck('category', 'modality')->all())
            ->all();

        return $custom;
    }

    public function prepararNuevoServicio(): void
    {
        $this->authorizeAdministrator();
        $this->resetValidation(['nombreNuevoServicio', 'modalidadesNuevoServicio']);
        $this->editandoServicioCodigo = null;
        $this->nombreNuevoServicio = '';
        $this->modalidadesNuevoServicio = [];
        $this->dispatch('openServiceModal');
    }

    public function crearServicioPostal(): void
    {
        $this->authorizeAdministrator();
        $this->validateServiceData();

        $name = trim($this->nombreNuevoServicio);
        $code = $this->makeServiceCode($name);

        if (PostalService::where('code', $code)->exists()) {
            throw ValidationException::withMessages([
                'nombreNuevoServicio' => 'Ya existe un servicio postal con ese nombre.',
            ]);
        }

        DB::transaction(function () use ($name, $code) {
            foreach (array_unique($this->modalidadesNuevoServicio) as $modality) {
                PostalService::create([
                    'code' => $code,
                    'name' => $name,
                    'modality' => $modality,
                    'category' => mb_strtoupper($name) . ' ' . $modality,
                    'profile' => 'CUSTOM ' . $modality,
                ]);
            }
        });

        $this->selectService($code);
        session()->flash('message', 'Servicio postal creado. Ahora puede registrar sus tarifas.');
        $this->dispatch('closeServiceModal');
    }

    public function prepararEdicionServicio(string $code): void
    {
        $this->authorizeAdministrator();
        $services = PostalService::where('code', $code)->get();
        abort_if($services->isEmpty(), 404);

        $this->resetValidation(['nombreNuevoServicio', 'modalidadesNuevoServicio']);
        $this->editandoServicioCodigo = $code;
        $this->nombreNuevoServicio = $services->first()->name;
        $this->modalidadesNuevoServicio = $services->pluck('modality')->all();
        $this->dispatch('openServiceModal');
    }

    public function actualizarServicioPostal(): void
    {
        $this->authorizeAdministrator();
        $this->validateServiceData();

        $services = PostalService::where('code', $this->editandoServicioCodigo)->get();
        abort_if($services->isEmpty(), 404);

        $name = trim($this->nombreNuevoServicio);
        $newCode = $this->makeServiceCode($name);
        $modalities = array_unique($this->modalidadesNuevoServicio);

        if (PostalService::where('code', $newCode)
            ->where('code', '!=', $this->editandoServicioCodigo)->exists()) {
            throw ValidationException::withMessages([
                'nombreNuevoServicio' => 'Ya existe un servicio postal con ese nombre.',
            ]);
        }

        $removed = $services->whereNotIn('modality', $modalities);
        if (Precios::whereIn('categoria', $removed->pluck('category'))->exists()) {
            throw ValidationException::withMessages([
                'modalidadesNuevoServicio' => 'No puede quitar una modalidad que contiene tarifas. Elimine primero esas tarifas.',
            ]);
        }

        DB::transaction(function () use ($services, $name, $newCode, $modalities, $removed) {
            foreach ($removed as $service) {
                Cache::forget(Precios::tariffCacheKey($service->category));
                $service->delete();
            }

            foreach ($modalities as $modality) {
                $service = $services->firstWhere('modality', $modality);
                $newCategory = mb_strtoupper($name) . ' ' . $modality;

                if ($service) {
                    $oldCategory = $service->category;
                    Precios::where('categoria', $oldCategory)->update(['categoria' => $newCategory]);
                    $service->update([
                        'code' => $newCode,
                        'name' => $name,
                        'category' => $newCategory,
                    ]);
                    Cache::forget(Precios::tariffCacheKey($oldCategory));
                } else {
                    PostalService::create([
                        'code' => $newCode,
                        'name' => $name,
                        'modality' => $modality,
                        'category' => $newCategory,
                        'profile' => 'CUSTOM ' . $modality,
                    ]);
                }

                Cache::forget(Precios::tariffCacheKey($newCategory));
            }
        });

        $this->selectService($newCode);
        session()->flash('message', 'Servicio postal actualizado correctamente.');
        $this->dispatch('closeServiceModal');
    }

    public function eliminarServicioPostal(string $code): void
    {
        $this->authorizeAdministrator();
        $services = PostalService::where('code', $code)->get();
        abort_if($services->isEmpty(), 404);

        $categories = $services->pluck('category');
        $tariffsDeleted = Precios::whereIn('categoria', $categories)->count();

        DB::transaction(function () use ($services, $categories) {
            Precios::whereIn('categoria', $categories)->delete();
            PostalService::whereIn('id', $services->pluck('id'))->delete();
        });

        foreach ($categories as $category) {
            Cache::forget(Precios::tariffCacheKey($category));
        }

        if ($this->servicioActivo === $code) {
            $this->servicioActivo = '';
            $this->modalidadActiva = '';
            $this->activeTab = '';
            $this->resetPage();
        }

        session()->flash('message', "Servicio postal eliminado junto con {$tariffsDeleted} tarifa(s).");
    }

    public function getServicioActivoPersonalizadoProperty(): bool
    {
        return $this->servicioActivo !== '' && PostalService::where('code', $this->servicioActivo)->exists();
    }

    private function validateServiceData(): void
    {
        $this->nombreNuevoServicio = trim($this->nombreNuevoServicio);
        $modalities = ['NAT', 'INT'];
        if ($this->editandoServicioCodigo) {
            $modalities = array_unique(array_merge($modalities, PostalService::where('code', $this->editandoServicioCodigo)
                ->pluck('modality')->all()));
        }
        $this->validate([
            'nombreNuevoServicio' => ['required', 'string', 'min:2', 'max:40'],
            'modalidadesNuevoServicio' => ['required', 'array', 'min:1', 'max:4'],
            'modalidadesNuevoServicio.*' => ['required', 'string', 'distinct', \Illuminate\Validation\Rule::in($modalities)],
        ], [
            'nombreNuevoServicio.required' => 'Ingrese el nombre del servicio postal.',
            'modalidadesNuevoServicio.required' => 'Seleccione al menos una modalidad.',
            'modalidadesNuevoServicio.min' => 'Seleccione al menos una modalidad.',
        ]);
        $this->makeServiceCode($this->nombreNuevoServicio);
    }

    private function makeServiceCode(string $name): string
    {
        $code = Str::of(Str::ascii($name))->upper()->replaceMatches('/[^A-Z0-9]+/', '_')->trim('_')->toString();
        if ($code === '') {
            throw ValidationException::withMessages([
                'nombreNuevoServicio' => 'El nombre debe contener al menos una letra o número.',
            ]);
        }

        return $code;
    }

    public function getEsCategoriaPersonalizadaProperty(): bool
    {
        return str_starts_with($this->categoriaVisual, 'CUSTOM ');
    }

    public function getCategoriaVisualProperty(): string
    {
        return PostalService::where('category', $this->activeTab)->value('profile') ?? $this->activeTab;
    }

    public function getModalidadCategoriaActivaProperty(): string
    {
        return PostalService::where('category', $this->activeTab)->value('modality') ?? '';
    }

    public function getColumnasTarifaProperty(): array
    {
        return match ($this->categoriaVisual) {
            'EMS NAT' => ['local_1' => 'Local 1', 'local_2' => 'Local 2', 'local_3' => 'Local 3',
                'local_4' => 'Local 4', 'nacional' => 'Nacional', 'cui_int' => 'Ciudades intermedias',
                'pro_dentro' => 'Provincia', 'cui1' => 'Trinidad / Cobija',
                'cui2' => 'Riberalta / Guayaramerín'],
            'MI ENCOMIENDA' => ['cui_cap' => 'Ciudades capitales', 'cui1' => 'Trinidad / Cobija',
                'pro_dentro' => 'Provincia del departamento', 'pro_otro' => 'Provincia de otro departamento'],
            'LC/AO NAT', 'ECA NAT' => ['local_1' => 'Local', 'nacional' => 'Nacional',
                'pro_dentro' => 'Provincia del departamento', 'pro_otro' => 'Provincia de otro departamento',
                'cui1' => 'Trinidad / Cobija', 'cui2' => 'Riberalta / Guayaramerín'],
            'PLIEGOS NAT' => ['local_1' => 'Local', 'nacional' => 'Nacional',
                'pro_dentro' => 'Provincia del departamento', 'pro_otro' => 'Provincia de otro departamento'],
            'SACAS M NAT' => ['nacional' => 'Nacional', 'pro_dentro' => 'Provincial'],
            'SUPER NAT' => ['nacional' => 'Nacional'],
            'SUPER PAQUE NAT', 'SUPER DOC NAT' => [
                'dest_a' => 'Tarifa 1', 'dest_b' => 'Tarifa 2', 'dest_c' => 'Tarifa 3',
                'dest_d' => 'Tarifa 4', 'dest_e' => 'Tarifa 5', 'dest_f' => 'Tarifa 6', 'dest_g' => 'Tarifa 7',
            ],
            'EMS INT', 'ENCOMIENDA', 'LC/AO INT', 'ECA INT', 'PLIEGOS INT', 'SACAS M INT' => [
                'dest_a' => 'Destino A', 'dest_b' => 'Destino B', 'dest_c' => 'Destino C',
                'dest_d' => 'Destino D', 'dest_e' => 'Destino E',
            ],
            default => $this->esCategoriaPersonalizada
                ? ($this->modalidadCategoriaActiva === 'INT'
                    ? ['dest_a' => 'Destino A', 'dest_b' => 'Destino B', 'dest_c' => 'Destino C',
                        'dest_d' => 'Destino D', 'dest_e' => 'Destino E']
                    : ['local_1' => 'Local', 'nacional' => 'Nacional',
                        'pro_dentro' => 'Provincia del departamento', 'pro_otro' => 'Provincia de otro departamento'])
                : [],
        };
    }

    private function authorizeAdministrator(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdministrator(), 403);
    }

    public function resetInputs()
    {
        $this->peso_min_kg = $this->peso_max_kg = $this->dest_a = $this->dest_b = $this->dest_c = $this->dest_d = $this->dest_e= $this->dest_f= $this->dest_g = $this->local_1 = $this->local_2 = $this->local_3 = $this->local_4 = $this->nacional = $this->cui_int = $this->cui_cap = $this->cui1 = $this->cui2 = $this->pro_dentro= $this->pro_otro = null;
    }

    public function prepararCreacion(): void
    {
        $this->authorizeAdministrator();
        $this->editingId = null;
        $this->creando = true;
        $this->resetInputs();
        $borrador = Session::get('tarifario.borrador', []);
        $this->nuevoServicio = $borrador['servicio'] ?? '';
        $this->nuevaModalidad = $borrador['modalidad'] ?? '';

        foreach (self::CAMPOS_TARIFA as $campo) {
            $this->{$campo} = $borrador['campos'][$campo] ?? null;
        }

        $this->sincronizarCategoriaNueva();

        $this->dispatch('openModal');
    }

    public function getModalidadesCreacionProperty(): array
    {
        return $this->serviceDefinitions()[$this->nuevoServicio] ?? [];
    }

    public function getCategoriaNuevaProperty(): string
    {
        return $this->modalidadesCreacion[$this->nuevaModalidad] ?? '';
    }

    public function updatedNuevoServicio(): void
    {
        $this->nuevaModalidad = '';
        $this->resetInputs();

        if (count($this->modalidadesCreacion) === 1) {
            $this->nuevaModalidad = array_key_first($this->modalidadesCreacion);
            $this->sincronizarCategoriaNueva();
        }

        $this->guardarBorrador();
    }

    public function updatedNuevaModalidad(): void
    {
        $this->sincronizarCategoriaNueva();
        $this->guardarBorrador();
    }

    public function updated(): void
    {
        $this->guardarBorrador();
    }

    private function guardarBorrador(): void
    {
        if ($this->creando) {
            Session::put('tarifario.borrador', [
                'servicio' => $this->nuevoServicio,
                'modalidad' => $this->nuevaModalidad,
                'campos' => collect(self::CAMPOS_TARIFA)->mapWithKeys(
                    fn ($campo) => [$campo => $this->{$campo}]
                )->all(),
            ]);
        }
    }

    public function descartarBorrador(): void
    {
        Session::forget('tarifario.borrador');
        $this->nuevoServicio = '';
        $this->nuevaModalidad = '';
        $this->resetInputs();
    }

    private function sincronizarCategoriaNueva(): void
    {
        if ($this->categoriaNueva) {
            $this->servicioActivo = $this->nuevoServicio;
            $this->modalidadActiva = $this->nuevaModalidad;
            $this->activeTab = $this->categoriaNueva;
            $this->resetPage();
        }
    }

    public function editTarifa(int $id): void
    {
        $this->authorizeAdministrator();
        $tarifa = Precios::where('categoria', $this->activeTab)->findOrFail($id);
        $this->editingId = $tarifa->id;
        $this->creando = false;

        foreach (self::CAMPOS_TARIFA as $campo) {
            $this->{$campo} = $tarifa->{$campo};
        }

        $this->resetValidation();
    }

    public function cancelarEdicionTarifa(): void
    {
        $this->editingId = null;
        $this->resetInputs();
        $this->resetValidation();
    }

    public function updateTarifa(): void
    {
        $this->authorizeAdministrator();
        $data = $this->validatedTariffData();
        $tarifa = DB::transaction(function () use ($data) {
            PostalService::where('category', $this->activeTab)->lockForUpdate()->firstOrFail();
            $tarifa = Precios::where('categoria', $this->activeTab)->findOrFail($this->editingId);
            if ($this->overlappingTariff($tarifa->categoria, $data['peso_min_kg'], $data['peso_max_kg'], $tarifa->id)) {
                throw ValidationException::withMessages([
                    'peso_min_kg' => 'El rango se superpone con otra tarifa de esta categoría.',
                ]);
            }
            $tarifa->update($data);

            return $tarifa;
        });

        Cache::forget(Precios::tariffCacheKey($tarifa->categoria));
        $this->editingId = null;
        $this->resetInputs();
        session()->flash('message', 'Tarifa actualizada exitosamente.');
    }

    public function createTarifa()
    {
        $this->authorizeAdministrator();
        if (!$this->categoriaNueva) {
            session()->flash('error', 'Selecciona servicio postal y modalidad antes de guardar.');
            return;
        }

        $this->activeTab = $this->categoriaNueva;
        $data = $this->validatedTariffData();

        try {
            DB::transaction(function () use ($data) {
                PostalService::where('category', $this->activeTab)->lockForUpdate()->firstOrFail();
                if ($this->overlappingTariff($this->activeTab, $data['peso_min_kg'], $data['peso_max_kg'])) {
                    throw ValidationException::withMessages([
                        'peso_min_kg' => 'El rango se superpone con otra tarifa de esta categoría.',
                    ]);
                }
                Precios::create(['categoria' => $this->activeTab] + $data);
            });
            Cache::forget(Precios::tariffCacheKey($this->activeTab));
            Session::forget('tarifario.borrador');

            session()->flash('message', 'Tarifa creada exitosamente.');
            $this->creando = false;
            $this->nuevoServicio = '';
            $this->nuevaModalidad = '';
            $this->resetInputs();
            $this->dispatch('closeModal');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            report($exception);
            session()->flash('error', 'No se pudo crear la tarifa. Inténtalo de nuevo.');
        }
    }

    public function solicitarEliminacionTarifa(int $id): void
    {
        $this->authorizeAdministrator();
        Precios::where('categoria', $this->activeTab)->findOrFail($id);
        $this->deletingId = $id;
    }

    public function cancelarEliminacionTarifa(): void
    {
        $this->deletingId = null;
    }

    public function getTarifaAEliminarProperty(): ?Precios
    {
        return $this->deletingId
            ? Precios::where('categoria', $this->activeTab)->find($this->deletingId)
            : null;
    }

    public function deleteTarifa(): void
    {
        $this->authorizeAdministrator();
        if (! $this->deletingId) {
            return;
        }

        try {
            $tarifario = Precios::where('categoria', $this->activeTab)->findOrFail($this->deletingId);
            $categoria = $tarifario->categoria;
            $tarifario->delete();
            Cache::forget(Precios::tariffCacheKey($categoria));
            $this->deletingId = null;
            session()->flash('message', 'Tarifa eliminada exitosamente.');
        } catch (\Exception $exception) {
            report($exception);
            session()->flash('error', 'No se pudo eliminar la tarifa. Inténtalo de nuevo.');
        }
    }

    public function render()
    {
        $tarifarios = $this->activeTab
            ? Cache::remember(Precios::tariffCacheKey($this->activeTab), now()->addHour(), fn () =>
                Precios::where('categoria', $this->activeTab)->orderBy('peso_min_kg')->orderBy('id')->get()
            )
            : collect();

        $perPage = 100;
        $page = $this->getPage();
        $tarifarios = new LengthAwarePaginator(
            $tarifarios->forPage($page, $perPage)->values(),
            $tarifarios->count(),
            $perPage,
            $page
        );

        return view('livewire.tarifario', [
            'tarifarios' => $tarifarios,
        ]);
    }

    private function validatedTariffData(): array
    {
        abort_if($this->columnasTarifa === [], 404);
        $rules = [
            'peso_min_kg' => 'required|numeric|decimal:0,3|min:0|max:999999',
            'peso_max_kg' => 'required|numeric|decimal:0,3|gt:0|gte:peso_min_kg|max:999999',
        ];
        foreach (array_keys($this->columnasTarifa) as $field) {
            $rules[$field] = 'nullable|numeric|decimal:0,2|min:0|max:999999999999.99';
        }

        return $this->validate($rules);
    }

    private function overlappingTariff(string $category, $minimum, $maximum, ?int $exceptId = null): bool
    {
        return Precios::where('categoria', $category)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->where('peso_min_kg', '<=', $maximum)
            ->where('peso_max_kg', '>=', $minimum)
            ->exists();
    }
}
