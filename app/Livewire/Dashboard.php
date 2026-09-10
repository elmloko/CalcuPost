<?php

namespace App\Livewire;

use App\Models\Precios;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public string $filtro = 'todos';
    public string $categoriaSeleccionada = '';

    public function filtrar(string $filtro): void
    {
        $this->filtro = $filtro;
        $this->categoriaSeleccionada = '';
    }

    public function seleccionarCategoria(string $categoria): void
    {
        $this->categoriaSeleccionada = $categoria;
    }

    public function render()
    {
        $categorias = Precios::query()
            ->select(
                'categoria',
                DB::raw('COUNT(*) as tarifas'),
                DB::raw('MIN(peso_min_kg) as peso_minimo'),
                DB::raw('MAX(peso_max_kg) as peso_maximo')
            )
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get()
            ->filter(function ($categoria) {
                return match ($this->filtro) {
                    'nacional' => str_ends_with($categoria->categoria, 'NAT') || $categoria->categoria === 'MI ENCOMIENDA',
                    'internacional' => str_ends_with($categoria->categoria, 'INT') || $categoria->categoria === 'ENCOMIENDA',
                    default => true,
                };
            })
            ->values();

        $seleccionada = $categorias->firstWhere('categoria', $this->categoriaSeleccionada);

        return view('livewire.dashboard', [
            'categorias' => $categorias,
            'seleccionada' => $seleccionada,
            'usuarios' => User::withTrashed()->count(),
            'usuariosActivos' => User::count(),
            'usuariosInactivos' => User::onlyTrashed()->count(),
            'totalTarifas' => Precios::count(),
            'pesoMaximo' => Precios::max('peso_max_kg'),
            'administradores' => User::where('is_admin', true)->count(),
        ]);
    }
}
