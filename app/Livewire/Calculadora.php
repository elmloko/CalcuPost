<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Calculadora extends Component
{
    public $categoria;
    public $destino;
    public $peso;
    public $largo;
    public $ancho;
    public $alto;
    public $resultado;

    public function calcular()
    {
        $this->validate([
            'categoria' => 'required',
            'peso'      => 'required|numeric|min:0',
            'largo'     => 'required|numeric|min:0',
            'ancho'     => 'required|numeric|min:0',
            'alto'      => 'required|numeric|min:0',
        ]);

        $columna = $this->parsearDestino($this->destino);

        $this->resultado = DB::table('tarifario')
            ->where('categoria', $this->categoria)
            ->where('peso_min_kg', '<=', $this->peso)
            ->where('peso_max_kg', '>=', $this->peso)
            ->value($columna);

        if (!$this->resultado) {
            $this->resultado = "No se encontraron tarifas para los datos ingresados.";
            return;
        }

        // Calcular el volumen total
        $dimensiones = $this->largo + $this->ancho + $this->alto;

        // Si la suma de largo, ancho y alto supera 1.5, agregar un 30% al resultado
        if ($dimensiones > 1.5) {
            $this->resultado += $this->resultado * 0.30;
        }
    }

    private function parsearDestino($destino)
    {
        $partes = explode('_', $destino);

        // Si el primer fragmento es "nacional", devolvemos la columna "nacional".
        if (!empty($partes[0]) && $partes[0] === 'nacional') {
            return 'nacional';
        }

        // Para "dest_e_tanzania" o "local_1_mexico": tomamos las dos primeras partes ("dest_e", "local_1").
        if (count($partes) >= 2) {
            return $partes[0] . '_' . $partes[1];
        }
        
        return $destino;
    }

    public function render()
    {
        return view('livewire.calculadora');
    }
}
