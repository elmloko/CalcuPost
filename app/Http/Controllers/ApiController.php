<?php

namespace App\Http\Controllers;

use App\Models\PostalService;
use App\Support\CalculatorHistory;
use App\Support\PostalCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function calcular(Request $request, PostalCalculator $calculator, CalculatorHistory $history)
    {
        $started = hrtime(true);
        $request->validate(['categoria' => 'required|string|max:50']);
        Validator::make(['sesion_consulta' => $request->header('X-Calculator-Session')], [
            'sesion_consulta' => 'nullable|uuid',
        ])->validate();

        // Calculations remain public; an existing Sanctum token may identify the caller.
        $user = Auth::guard('sanctum')->user();
        if ($request->bearerToken() && !$user) {
            return response()->json(['message' => 'Token de autenticación inválido.'], 401);
        }
        $service = PostalService::where('category', $request->input('categoria'))->first();
        if (!$service) {
            return response()->json(['message' => 'El servicio postal no está disponible.'], 404);
        }
        $calculation = $calculator->calculate($service, $request->all());
        // Reuse this UUID to count an API session once, without storing IP addresses.
        $session = $request->header('X-Calculator-Session') ?: (string) Str::uuid();
        try {
            $record = $history->save($service, $calculation, $started, $user, 'api:'.$session);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'success' => false, 'registro_guardado' => false,
                'message' => 'No se pudo guardar la consulta. Inténtalo de nuevo.',
            ], 503);
        }

        $body = [
            'success' => $calculation['status'] === 'calculated',
            'categoria' => $service->category, 'peso' => (float) $calculation['data']['peso'],
            'destino' => $calculation['data']['destino'], 'tarifa' => $calculation['total'],
            'registro_id' => $record->id, 'registro_guardado' => true, 'sesion_consulta' => $session,
        ];
        if ($calculation['status'] === 'no_rate') {
            $body['message'] = 'No se encontraron tarifas para los datos ingresados.';
        }
        return response()->json($body, $calculation['status'] === 'calculated' ? 200 : 404);
    }
}
