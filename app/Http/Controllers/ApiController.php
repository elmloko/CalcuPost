<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function calcular(Request $request)
    {
        $request->validate([
            'categoria' => 'required|string',
            'peso'      => 'required|numeric|min:0',
            'destino'   => 'required|string',
            'largo'     => 'nullable|numeric|min:0',
            'ancho'     => 'nullable|numeric|min:0',
            'alto'      => 'nullable|numeric|min:0',
            'certificado' => 'boolean',
            'espreso'     => 'boolean',
            'recibo'      => 'boolean',
        ]);

        $categoria = $request->categoria;
        $peso = $request->peso;
        $destino = $request->destino;
        $certificado = $request->certificado ?? false;
        $espreso = $request->espreso ?? false;
        $recibo = $request->recibo ?? false;

        $columna = self::parsearDestino($destino);

        if (!$columna) {
            return response()->json(['message' => 'Destino inválido'], 400);
        }

        $resultado = DB::table('tarifario')
            ->where('categoria', $categoria)
            ->where('peso_min_kg', '<=', $peso)
            ->where('peso_max_kg', '>=', $peso)
            ->value($columna);

        if (!$resultado) {
            return response()->json(['message' => 'No se encontraron tarifas para los datos ingresados.'], 404);
        }

        // Cálculo de volumen
        $largo = $request->largo ?? 0;
        $ancho = $request->ancho ?? 0;
        $alto = $request->alto ?? 0;

        $dimensiones = $largo + $ancho + $alto;

        // Si la suma de largo, ancho y alto supera 1.5, agregar un 30%
        if ($dimensiones > 1.5) {
            $resultado += $resultado * 0.30;
        }

        // Aplicar costos adicionales
        $resultado = self::aplicarServiciosAdicionales($categoria, $resultado, $certificado, $espreso);

        return response()->json([
            'success' => true,
            'categoria' => $categoria,
            'peso' => $peso,
            'destino' => $destino,
            'tarifa' => $resultado
        ]);
    }

    private static function aplicarServiciosAdicionales($categoria, $resultado, $certificado, $espreso)
    {
        if (self::esNacional($categoria)) {
            if ($certificado) $resultado += 2;
            if ($espreso) $resultado += 10;
        } elseif (self::esInternacional($categoria)) {
            if ($certificado) $resultado += 8;
            if ($espreso) $resultado += 10;
        }
        return $resultado;
    }

    private static function esNacional($categoria)
    {
        $categoriasNacionales = [
            "EMS NAT", "MI ENCOMIENDA", "LC/AO NAT",
            "ECA NAT", "PLIEGOS NAT", "SACAS M NAT", "SUPER NAT"
        ];
        return in_array($categoria, $categoriasNacionales);
    }

    private static function esInternacional($categoria)
    {
        $categoriasInternacionales = [
            "EMS INT", "ENCOMIENDA", "LC/AO INT",
            "ECA INT", "PLIEGOS INT", "SACAS M INT", "SUPER DOC NAT", "SUPER PAQUE NAT"
        ];
        return in_array($categoria, $categoriasInternacionales);
    }

    private static function parsearDestino($destino)
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
}
