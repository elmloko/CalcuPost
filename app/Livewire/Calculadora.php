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
    public $certificado = false;
    public $espreso = false;
    public $recibo = false;

    public function calcular()
    {
        $this->validate([
            'categoria' => 'required',
            'peso'      => 'required|numeric|min:0',
        ]);

        if (empty($this->destino)) {
            $this->resultado = "Por favor, selecciona un destino válido.";
            return;
        }

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

        // Ajustar el resultado con base en los servicios adicionales
        $this->aplicarServiciosAdicionales();
    }

    private function aplicarServiciosAdicionales()
    {
        // Verificar categoría nacional o internacional
        if ($this->esNacional($this->categoria)) {
            if ($this->certificado) {
                $this->resultado += 2;
            }
            if ($this->espreso) {
                $this->resultado += 10;
            }
            // El checkbox de "recibo" no altera el resultado
        } elseif ($this->esInternacional($this->categoria)) {
            if ($this->certificado) {
                $this->resultado += 8;
            }
            if ($this->espreso) {
                $this->resultado += 10;
            }
            if ($this->espreso) {
                $this->resultado += 8;
            }
            // El checkbox de "recibo" no altera el resultado
        }
    }

    private function esNacional($categoria)
    {
        $categoriasNacionales = [
            "EMS NAT",
            "MI ENCOMIENDA",
            "LC/AO NAT",
            "ECA NAT",
            "PLIEGOS NAT",
            "SACAS M NAT",
            "SUPER NAT"
        ];
        return in_array($categoria, $categoriasNacionales);
    }

    private function esInternacional($categoria)
    {
        $categoriasInternacionales = [
            "EMS INT",
            "ENCOMIENDA",
            "LC/AO INT",
            "ECA INT",
            "PLIEGOS INT",
            "SACAS M INT",
            "SUPER DOC NAT",
            "SUPER PAQUE NAT"
        ];
        return in_array($categoria, $categoriasInternacionales);
    }

    private function parsearDestino($destino)
    {
        if (empty($destino)) {
            return 0; // Retornar null si no hay un destino válido
        }

        $partes = explode('_', $destino);

        // Si el primer fragmento es "nacional", devolvemos la columna "nacional".
        if (!empty($partes[0]) && $partes[0] === 'nacional') {
            return 'nacional';
        }

        // Para "dest_e_tanzania" o "local_1_mexico": tomamos las dos primeras partes ("dest_e", "local_1").
        if (count($partes) >= 2) {
            return $partes[0] . '_' . $partes[1];
        }

        return null; // Retornar null si no se puede determinar la columna
    }


    public function render()
    {
        return view('livewire.calculadora');
    }
}
