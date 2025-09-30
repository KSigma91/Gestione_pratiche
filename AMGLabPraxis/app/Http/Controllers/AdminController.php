<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Practice;
use Carbon\Carbon;
use App\Jobs\DeletePractice;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Knp\Snappy\Pdf;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Pagina principale admin con lista pratiche
    public function index(Request $request)
    {
        $q = Practice::query();

        // Filtro per stato solo se il valore è presente e diverso da "tutti" (o valore definito per "tutti")
        if ($request->filled('stato') && $request->input('stato') !== 'tutti') {
            $q->where('stato', $request->input('stato'));
        }

        if ($request->has('cliente') && $request->input('cliente') !== '') {
            $q->where('cliente_nome', 'like', '%' . $request->input('cliente') . '%');
        }

        $pratiche = $q->orderBy('data_arrivo', 'desc')
                    ->paginate(15)
                    ->appends($request->except('page'));

        return view('pratiche.index', compact('pratiche'));
    }

    public function create()
    {
        return view('pratiche.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_nome' => 'required|string|max:255',
            'caso' => 'required|string|max:255',
            'tipo_pratica' => [ 'required', Rule::in(['Tribunale Penale', 'Tribunale Civile', 'Giudice di Pace', 'Tar'])],
            'stato' => 'required|in:in_giacenza,in_lavorazione,completata,annullata',
            'data_arrivo' => 'required|date',
            'data_scadenza' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        // (la colonna 'codice' nella migration è NOT NULL; quindi forniamo un placeholder unico).
        $tmpCodice = 'PRAT-TMP-' . uniqid();

        // Inserimento iniziale (codice temporaneo)
        $dataWithTmp = $data;
        $dataWithTmp['codice'] = $tmpCodice;

        $pr = \App\Models\Practice::create($dataWithTmp);

        // Ora abbiamo l'id assegnato dal DB. Generiamo il codice definitivo basato sull'id.
        $finalCodice = sprintf('PRAT-%06d', $pr->id);

        // Salviamo il codice definitivo
        $pr->codice = $finalCodice;
        $pr->save();

        return redirect()->route('admin.pratiche.index')
        ->with('status', 'Pratica creata con successo. Codice: ' . $finalCodice);
    }

    public function edit($id)
    {
        $pr = Practice::findOrFail($id);
        return view('pratiche.edit', compact('pr'));
    }

    public function update(Request $request, $id)
    {
        $pr = \App\Models\Practice::findOrFail($id);

        $data = $request->validate([
            'cliente_nome' => 'required|string|max:255',
            'caso' => 'required|string|max:255',
            'tipo_pratica' => [ 'required', Rule::in(['Tribunale Penale', 'Tribunale Civile', 'Giudice di Pace', 'Tar'])],
            'stato' => 'required|in:in_giacenza,in_lavorazione,completata,annullata',
            'data_arrivo' => 'required|date',
            'data_scadenza' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        $pr->update($data);

        return redirect()->route('admin.pratiche.index')->with('status', 'Pratica aggiornata con successo.');
    }

    // Soft delete -> move to Cestino
    public function destroy($id)
    {
        $pr = Practice::findOrFail($id);
        $pr->delete(); // soft delete
        return redirect()->route('admin.pratiche.index')->with('status', 'Pratica spostata nel cestino.');
    }

    // Trash listing
    public function trash(Request $request)
    {
        $q = Practice::onlyTrashed();
        if ($request->has('cliente')) {
            $q->where('cliente_nome', 'like', '%' . $request->input('cliente') . '%');
        }
        $pratiche = $q->orderBy('deleted_at', 'desc')->paginate(20)->appends($request->except('page'));
        return view('pratiche.trash', compact('pratiche'));
    }

    // Restore from trash
    public function restore($id)
    {
        $pr = Practice::withTrashed()->findOrFail($id);
        if ($pr->trashed()) {
            $pr->restore();
            return redirect()->route('admin.pratiche.trash')->with('status', 'Pratica ripristinata.');
        }
        return redirect()->route('admin.pratiche.trash')->with('warning', 'Pratica non trovata nel cestino.');
    }

    // Force delete (permanente)
    public function forceDelete($id)
    {
        $pr = Practice::withTrashed()->findOrFail($id);
        $pr->forceDelete();
        return redirect()->route('admin.pratiche.trash')->with('status', 'Pratica eliminata definitivamente.');
    }

    public function markGiacenza($id)
    {
        $pr = \App\Models\Practice::findOrFail($id);
        $pr->stato = 'in_giacenza';
        $pr->data_arrivo = now();  // oppure lascia la data già presente se preferisci
        $pr->save();

        return redirect()->back()->with('status', 'Pratica #' . $pr->id . ' messa in giacenza.');

        if ($pr->stato === 'in_giacenza') {
            return redirect()->back()->with('warning', 'La pratica è già in giacenza.');
        }
    }

    public function archiveIndex($year = null)
    {
        // Raggruppa pratiche per anno e mese, contando quantità
        $cacheKey = 'pratiche_archive_summary';

        $archive = Cache::remember($cacheKey, now()->addMinutes(1), function () { // addMinutes 10
            return DB::table('pratiche')
                ->select(DB::raw('YEAR(data_arrivo) as year'),
                        DB::raw('MONTH(data_arrivo) as month'),
                        DB::raw('COUNT(*) as total'))
                ->whereNotNull('data_arrivo')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
        });

        Carbon::setLocale('it');

        return view('pratiche.archive_index', compact('archive'));
    }

    public function archiveView($year, $month, Request $request)
    {
        $pratiche = \App\Models\Practice::whereYear('data_arrivo', $year)
            ->whereMonth('data_arrivo', $month)
            ->orderBy('data_arrivo', 'desc')
            ->paginate(15);

        return view('pratiche.archive_view', compact('year', 'month', 'pratiche'));
    }

    /**
     * Export CSV: stream per evitare memory spikes
     */
    public function exportYearCsv($year)
    {
        // validazione semplice
        if (!ctype_digit($year)) {
            return redirect()->back()->with('error', 'Anno non valido.');
        }

        $filename = "pratiche_{$year}.csv";

        $query = Practice::whereYear('data_arrivo', $year)->orderBy('data_arrivo', 'asc');

        $callback = function () use ($query) {
            $out = fopen('php://output', 'w');
            // intestazione CSV
            fputcsv($out, ['ID','Codice','Cliente','Tipo','Caso','Stato','Data Arrivo','Data Scadenza','Note']);

            // chunk per efficienza
            $query->chunk(200, function($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->codice,
                        $r->cliente_nome,
                        $r->tipo_pratica,
                        $r->caso,
                        $r->stato,
                        optional($r->data_arrivo)->format('Y-m-d H:i'),
                        optional($r->data_scadenza)->format('Y-m-d H:i'),
                        str_replace(["\r","\n"], [' ',' '], $r->note)
                    ]);
                }
            });

            fclose($out);
        };

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Excel simple: output HTML table con header Excel (apre in Excel)
     * (molto robusto e senza dipendenze)
     */
    public function exportYearExcel($year)
    {
        if (!ctype_digit($year)) {
            return redirect()->back()->with('error', 'Anno non valido.');
        }

        $filename = "pratiche_{$year}.xls"; // .xls: Excel apre HTML table
        $pratiche = Practice::whereYear('data_arrivo', $year)->orderBy('data_arrivo','asc')->get();

        $html = view('pratiche.exports.table_export', compact('pratiche'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Export Word: semplice HTML con header .doc (Word apre HTML)
     */
    public function exportYearWord($year)
    {
        if (!ctype_digit($year)) {
            return redirect()->back()->with('error', 'Anno non valido.');
        }

        $filename = "pratiche_{$year}.doc";
        $pratiche = Practice::whereYear('data_arrivo', $year)->orderBy('data_arrivo','asc')->get();

        $html = view('pratiche.exports.word_export', compact('pratiche'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Export PDF: usa Snappy se installato, altrimenti fornisce la pagina HTML scaricabile
     */
    public function exportYearPdf($year)
{
    if (!ctype_digit((string)$year)) {
        return redirect()->back()->with('error', 'Anno non valido.');
    }
    $pratiche = \App\Models\Practice::whereYear('data_arrivo', $year)
        ->orderBy('data_arrivo', 'asc')
        ->get();
    $html = view('pratiche.exports.pdf_export', compact('pratiche', 'year'))->render();

    $binaryPath = config('snappy.pdf.binary') ?: 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';

    // aggiungi virgolette esterne se non ci sono
    if (strpos($binaryPath, ' ') !== false && $binaryPath[0] !== '"') {
        $binaryPath = '"' . $binaryPath . '"';
    }

    // Debug: mostra il path e se esiste
    if (!file_exists(trim($binaryPath, '"'))) {
        dd("Binary non trovato", $binaryPath);
    }

    $snappy = new \Knp\Snappy\Pdf($binaryPath);
    $snappy->setOption('page-size', 'A4');
    $snappy->setOption('orientation', 'Landscape');
    $snappy->setOption('encoding', 'UTF-8');
    $snappy->setOption('enable-local-file-access', true);
    $snappy->setOption('no-outline', true);

    try {
        $pdfContent = $snappy->getOutputFromHtml($html);
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"pratiche_{$year}.pdf\"",
        ]);
    } catch (\Exception $e) {
        // Mostra messaggio di errore per debug
        dd("Errore PDF: " . $e->getMessage());
    }
}

}
