<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Precios;
use Livewire\WithPagination;

class Tarifario extends Component
{
    use WithPagination;

    public $activeTab = 'EMS INT'; // Pestaña activa por defecto

    public function selectTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Filtra los datos según la pestaña activa
        $tarifarios = Precios::where('categoria', $this->activeTab)->paginate(10);
    
        return view('livewire.tarifario', [
            'tarifarios' => $tarifarios,
        ]);
    }
}