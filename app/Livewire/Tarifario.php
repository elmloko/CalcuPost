<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Precios;
use Livewire\WithPagination;

class Tarifario extends Component
{
    use WithPagination;

    public $activeTab = 'EMS INT';
    public $peso_min_kg, $peso_max_kg, $dest_a, $dest_b, $dest_c, $dest_d, $dest_e, $local_1, $local_2; // Campos dinámicos

    public function selectTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetInputs();
        $this->resetPage();
    }

    public function resetInputs()
    {
        $this->peso_min_kg = $this->peso_max_kg = $this->dest_a = $this->dest_b = $this->dest_c = $this->dest_d = $this->dest_e = $this->local_1 = $this->local_2 = null;
    }

    public function createTarifa()
    {
        try {
            $data = [
                'categoria' => $this->activeTab,
            ];

            if ($this->activeTab === 'EMS INT') {
                $data = array_merge($data, [
                    'categoria' => 'EMS INT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'dest_a' => $this->dest_a,
                    'dest_b' => $this->dest_b,
                    'dest_c' => $this->dest_c,
                    'dest_d' => $this->dest_d,
                    'dest_e' => $this->dest_e,
                ]);
            } elseif ($this->activeTab === 'EMS NAT') {
                $data = array_merge($data, [
                    'peso_min_kg' => $this->peso_min_kg,
                    'local_1' => $this->local_1,
                    'local_2' => $this->local_2,
                ]);
            }

            Precios::create($data);

            session()->flash('message', 'Tarifa creada exitosamente.');
            $this->resetInputs();
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la tarifa: ' . $e->getMessage());
        }
    }

    public function deleteTarifa($id)
    {
        try {
            // 1. Buscas el registro (o usas un where).
            $tarifario = Precios::findOrFail($id);

            // 2. Lo eliminas.
            $tarifario->delete();

            // 3. Notificas al usuario
            session()->flash('message', 'Tarifa eliminada exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la tarifa: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $tarifarios = Precios::where('categoria', $this->activeTab)->paginate(10);

        return view('livewire.tarifario', [
            'tarifarios' => $tarifarios,
        ]);
    }
}
