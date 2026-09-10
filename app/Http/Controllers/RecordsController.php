<?php

namespace App\Http\Controllers;

use App\Models\CalculatorQuery;
use App\Support\CalculatorReport;
use App\Support\PostalTime;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RecordsController extends Controller
{
    private function filters(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('from') ? 'after_or_equal:from' : null]),
            'service' => ['nullable', 'string', 'max:50'],
            'modality' => ['nullable', Rule::in(['NAT', 'INT', 'PAQUE', 'DOC'])],
            'status' => ['nullable', Rule::in(['calculated', 'no_rate'])],
            'actor' => ['nullable', Rule::in(['account', 'guest'])],
            'destination' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', Rule::in(['20', '50', '100'])],
        ]);
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = CalculatorReport::query($filters);
        return view('records.index', [
            'filters' => $filters,
            'summary' => CalculatorReport::summary($query),
            'records' => $query->latest('created_at')->latest('id')
                ->paginate((int) ($filters['per_page'] ?? 50))->withQueryString(),
            'services' => CalculatorQuery::selectRaw('service_code, MAX(service_name) AS name')->groupBy('service_code')->orderBy('name')->get(),
            'destinations' => CalculatorQuery::selectRaw('destination_code, MAX(destination_name) AS name')->groupBy('destination_code')->orderBy('name')->get(),
        ]);
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request);
        $query = CalculatorReport::query($filters);
        // A bounded export avoids exhausting the web worker; nothing is silently truncated.
        if ((clone $query)->count() > 1000) {
            throw ValidationException::withMessages(['export' => 'El informe admite hasta 1.000 consultas. Filtra por fecha o servicio para descargarlo.']);
        }
        $summary = CalculatorReport::summary($query);
        $records = $query->latest('created_at')->latest('id')->get();
        $pdf = Pdf::loadView('records.pdf', compact('filters', 'summary', 'records'))
            ->setPaper('a4', 'landscape')->setOptions(['isRemoteEnabled' => false]);
        $pdf->render();
        $pdf->getDomPDF()->getCanvas()->page_text(
            652,
            565,
            'Página {PAGE_NUM} de {PAGE_COUNT} · '.$records->count().' consultas',
            null,
            8,
            [.2, .3, .4]
        );

        return $pdf->download('registros-postales-'.PostalTime::now()->format('Y-m-d').'.pdf');
    }
}
