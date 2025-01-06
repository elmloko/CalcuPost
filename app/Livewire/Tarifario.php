<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Precios;
use Livewire\WithPagination;

class Tarifario extends Component
{
    use WithPagination;

    public $activeTab = 'EMS INT';
    public $peso_min_kg, $peso_max_kg, $dest_a, $dest_b, $dest_c, $dest_d, $dest_e, $dest_f, $dest_g, $local_1, $local_2, $local_3, $local_4, $nacional, $cui_int, $cui_cap, $cui1, $cui2, $pro_dentro, $pro_otro; // Campos dinámicos

    public function selectTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetInputs();
        $this->resetPage();
    }

    public function resetInputs()
    {
        $this->peso_min_kg = $this->peso_max_kg = $this->dest_a = $this->dest_b = $this->dest_c = $this->dest_d = $this->dest_e= $this->dest_f= $this->dest_g = $this->local_1 = $this->local_2 = $this->local_3 = $this->local_4 = $this->nacional = $this->cui_int = $this->cui_cap = $this->cui1 = $this->cui2 = $this->pro_dentro= $this->pro_otro = null;
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
                    'categoria' => 'EMS NAT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'local_1' => $this->local_1,
                    'local_2' => $this->local_2,
                    'local_3' => $this->local_3,
                    'local_4' => $this->local_4,
                    'nacional' => $this->nacional,
                    'cui_int' => $this->cui_int,
                    'cui1' => $this->cui1,
                    'cui2' => $this->cui2,
                ]);
            } elseif ($this->activeTab === 'MI ENCOMIENDA') {
                $data = array_merge($data, [
                    'categoria' => 'MI ENCOMIENDA',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'cui_cap' => $this->cui_cap,
                    'cui1' => $this->cui1,
                    'pro_dentro' => $this->pro_dentro,
                    'pro_otro' => $this->pro_otro,  
                ]);
            } elseif ($this->activeTab === 'ENCOMIENDA') {
                $data = array_merge($data, [
                    'categoria' => 'ENCOMIENDA',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'dest_a' => $this->dest_a,
                    'dest_b' => $this->dest_b,
                    'dest_c' => $this->dest_c,
                    'dest_d' => $this->dest_d,
                    'dest_e' => $this->dest_e,  
                ]);
            } elseif ($this->activeTab === 'LC/AO NAT') {
                $data = array_merge($data, [
                    'categoria' => 'LC/AO NAT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'local_1' => $this->local_1,
                    'nacional' => $this->nacional,
                    'pro_dentro' => $this->pro_dentro,
                    'pro_otro' => $this->pro_otro,  
                    'cui1' => $this->cui1,
                    'cui2' => $this->cui2,
                ]);
            } elseif ($this->activeTab === 'LC/AO INT') {
                $data = array_merge($data, [
                    'categoria' => 'LC/AO INT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'dest_a' => $this->dest_a,
                    'dest_b' => $this->dest_b,
                    'dest_c' => $this->dest_c,
                    'dest_d' => $this->dest_d,
                    'dest_e' => $this->dest_e,  
                ]);
            } elseif ($this->activeTab === 'ECA NAT') {
                $data = array_merge($data, [
                    'categoria' => 'ECA NAT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'local_1' => $this->local_1,
                    'nacional' => $this->nacional,
                    'pro_dentro' => $this->pro_dentro,
                    'pro_otro' => $this->pro_otro,  
                    'cui1' => $this->cui1,
                    'cui2' => $this->cui2,
                ]);
            } elseif ($this->activeTab === 'ECA INT') {
                $data = array_merge($data, [
                    'categoria' => 'ECA INT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'dest_a' => $this->dest_a,
                    'dest_b' => $this->dest_b,
                    'dest_c' => $this->dest_c,
                    'dest_d' => $this->dest_d,
                    'dest_e' => $this->dest_e,  
                ]);
            } elseif ($this->activeTab === 'PLIEGOS NAT') {
                $data = array_merge($data, [
                    'categoria' => 'PLIEGOS NAT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'local_1' => $this->local_1,
                    'nacional' => $this->nacional,
                    'pro_dentro' => $this->pro_dentro,
                    'pro_otro' => $this->pro_otro,
                ]);
            } elseif ($this->activeTab === 'PLIEGOS INT') {
                $data = array_merge($data, [
                    'categoria' => 'PLIEGOS INT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'dest_a' => $this->dest_a,
                    'dest_b' => $this->dest_b,
                    'dest_c' => $this->dest_c,
                    'dest_d' => $this->dest_d,
                    'dest_e' => $this->dest_e,  
                ]);
            } elseif ($this->activeTab === 'SACAS M NAT') {
                $data = array_merge($data, [
                    'categoria' => 'SACAS M NAT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'nacional' => $this->nacional,
                    'pro_dentro' => $this->pro_dentro,
                ]);
            } elseif ($this->activeTab === 'SACAS M INT') {
                $data = array_merge($data, [
                    'categoria' => 'SACAS M INT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'dest_a' => $this->dest_a,
                    'dest_b' => $this->dest_b,
                    'dest_c' => $this->dest_c,
                    'dest_d' => $this->dest_d,
                    'dest_e' => $this->dest_e,  
                ]);
            } elseif ($this->activeTab === 'SUPER NAT') {
                $data = array_merge($data, [
                    'categoria' => 'SUPER NAT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'nacional' => $this->nacional,
                ]);
            } elseif ($this->activeTab === 'SUPER PAQUE NAT') {
                $data = array_merge($data, [
                    'categoria' => 'SUPER PAQUE NAT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'dest_a' => $this->dest_a,
                    'dest_b' => $this->dest_b,
                    'dest_c' => $this->dest_c,
                    'dest_d' => $this->dest_d,
                    'dest_e' => $this->dest_e,  
                    'dest_f' => $this->dest_f,  
                    'dest_g' => $this->dest_g,  
                ]);
            } elseif ($this->activeTab === 'SUPER DOC NAT') {
                $data = array_merge($data, [
                    'categoria' => 'SUPER DOC NAT',
                    'peso_min_kg' => $this->peso_min_kg,
                    'peso_max_kg' => $this->peso_max_kg,
                    'dest_a' => $this->dest_a,
                    'dest_b' => $this->dest_b,
                    'dest_c' => $this->dest_c,
                    'dest_d' => $this->dest_d,
                    'dest_e' => $this->dest_e,  
                    'dest_f' => $this->dest_f,  
                    'dest_g' => $this->dest_g,  
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
        $tarifarios = Precios::where('categoria', $this->activeTab)->paginate(100);

        return view('livewire.tarifario', [
            'tarifarios' => $tarifarios,
        ]);
    }
}
