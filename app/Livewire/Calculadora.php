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
            'peso'      => 'required|numeric|min:0',
        ]);

        $columna = $this->parsearDestino($this->destino);

        $this->resultado = DB::table('tarifario')
            ->where('categoria', $this->categoria)
            ->where('peso_min_kg', '<=', $this->peso)
            ->where('peso_max_kg', '>=', $this->peso)
            ->value($columna);

        if (!$this->resultado) {
            $this->resultado = "No se encontraron tarifas para los datos ingresados.";
        }
    }

    /**
     * parsearDestino:
     * - Si llega "dest_e_tanzania" => se convierte en "dest_e"
     * - Si llega "local_1_brasil"  => se convierte en "local_1"
     * - Si llega "nacional_la"     => se convierte en "nacional"
     */
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

        // Si no cumple con el formato esperado, retornamos el valor tal cual 
        // (o podríamos manejarlo de otra forma).
        return $destino;
    }

    public function render()
    {
        return view('livewire.calculadora');
    }
}
