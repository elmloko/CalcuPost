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
        $largo = is_numeric($this->largo) ? (float) $this->largo : 0;
        $ancho = is_numeric($this->ancho) ? (float) $this->ancho : 0;
        $alto = is_numeric($this->alto) ? (float) $this->alto : 0;

        $dimensiones = $largo + $ancho + $alto;

        // Si la suma de largo, ancho y alto supera 1.5, agregar un 30% al resultado
        if ($dimensiones > 1.5) {
            $this->resultado += $this->resultado * 0.30;
        }

        // Ajustar el resultado con base en los servicios adicionales
        $this->aplicarServiciosAdicionales();
    }

    private function aplicarServiciosAdicionales()
    {
        // Categorías que pueden sumar por "certificado"
        $categorias_con_certificado = [
            "LC/AO NAT", "ECA NAT", "PLIEGOS NAT", "SACAS M NAT",
            "LC/AO INT", "ECA INT", "PLIEGOS INT", "SACAS M INT"
        ];

        // Verificar si la categoría es nacional o internacional
        if ($this->esNacional($this->categoria)) {
            if ($this->certificado && in_array($this->categoria, $categorias_con_certificado)) {
                $this->resultado += 2;
            }
            if ($this->espreso) {
                $this->resultado += 10;
            }
        } elseif ($this->esInternacional($this->categoria)) {
            if ($this->certificado && in_array($this->categoria, $categorias_con_certificado)) {
                $this->resultado += 8;
            }
            if ($this->espreso) {
                $this->resultado += 10;
            }
        }
    }

    private function esNacional($categoria)
    {
        $categoriasNacionales = [
            "EMS NAT", "MI ENCOMIENDA", "LC/AO NAT",
            "ECA NAT", "PLIEGOS NAT", "SACAS M NAT", "SUPER NAT",
            "SUPER DOC NAT", "SUPER PAQUE NAT"
        ];
        return in_array($categoria, $categoriasNacionales);
    }

    private function esInternacional($categoria)
    {
        $categoriasInternacionales = [
            "EMS INT", "ENCOMIENDA", "LC/AO INT",
            "ECA INT", "PLIEGOS INT", "SACAS M INT"
        ];
        return in_array($categoria, $categoriasInternacionales);
    }

    private function parsearDestino($destino)
    {
        if (empty($destino)) {
            return null;
        }

        $partes = explode('_', $destino);

        if (count($partes) === 1) {
            return $destino;
        }

        if (!empty($partes[0]) && $partes[0] === 'nacional') {
            return 'nacional';
        }

        if (count($partes) >= 2) {
            return $partes[0] . '_' . $partes[1];
        }

        return null;
    }

    public function render()
    {
        return view('livewire.calculadora');
    }
}
