<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Calculadora extends Component
{
    public $categoria;
    public $destino;
    public $peso;
    public $resultado;

    public function calcular()
    {
        $this->validate([
            'categoria' => 'required',
            'peso' => 'required|numeric|min:0',
        ]);

        // Realizar la consulta en la tabla 'tarifario'
        $this->resultado = DB::table('tarifario')
            ->where('categoria', $this->categoria)
            ->where('peso_min_kg', '<=', $this->peso)
            ->where('peso_max_kg', '>=', $this->peso)
            ->value($this->destino);

        if (!$this->resultado) {
            $this->resultado = "No se encontraron tarifas para los datos ingresados.";
        }
    }

    public function render()
    {
        return view('livewire.calculadora');
    }
}
