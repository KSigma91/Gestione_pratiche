<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Practice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

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

        // INPUT cliente: può contenere testo e/o anni (es. "Rossi 2023")
        $clienteInput = trim($request->input('cliente', ''));

        $years = [];
        $textTokens = [];

        if ($clienteInput !== '') {
            // split su spazi
            $tokens = preg_split('/\s+/', $clienteInput);
            foreach ($tokens as $t) {
                // se token è esattamente 4 cifre, lo interpretiamo come anno
                if (preg_match('/^\d{4}$/', $t)) {
                    $years[] = (int) $t;
                } else {
                    $textTokens[] = $t;
                }
            }
        }
        // testo rimanente per ricerca cliente
        $clienteText = trim(implode(' ', $textTokens));

        if ($clienteText !== '') {
            $q->where('cliente_nome', 'like', '%' . $clienteText . '%');
        }

        if (!empty($years)) {
            $q->where(function ($sub) use ($years) {
                foreach ($years as $i => $year) {
                    $sub->{$i === 0 ? 'whereYear' : 'orWhereYear'}('data_arrivo', $year);
                }
            });
        }

        // --- Filtro stato ---
        if ($request->filled('stato') && $request->input('stato') !== 'tutti') {
            $q->where('stato', $request->input('stato'));
        }

        // --- Ordinamenti ---
        $ordinato = false;

        if ($request->has('ordinamento') && $request->ordinamento) {
            $direction = $request->ordinamento === 'asc' ? 'asc' : 'desc';
            $q->orderBy('codice', $direction);
            $ordinato = true;
        }

        if ($request->has('ordinamento_data') && $request->ordinamento_data) {
            $direction = $request->ordinamento_data === 'asc' ? 'asc' : 'desc';
            $q->orderBy('data_arrivo', $direction);
            $ordinato = true;
        }

        // --- Ordinamento di default SOLO se non specificato altro ---
        if (!$ordinato) {
            $q->orderBy('data_arrivo', 'desc');
        }

        $pratiche = $q->paginate(15)->appends($request->except('page'));

        return view('pratiche.index', compact('pratiche'));
    }

    public function create()
    {
        $pr = new Practice();
        return view('pratiche.create', compact('pr'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_nome' => 'required|string|max:255',
            'caso' => 'required|string|max:255',
            'tipo_pratica' => [ 'required', Rule::in(['Tribunale Penale', 'Tribunale Civile', 'Giudice di Pace', 'Tar'])],
            'stato' => 'required|in:in_giacenza,in_lavorazione,completata,annullata',
            'stato_fattura'  => 'required|in:emessa,non_emessa',
            'stato_pagamento'=> 'required|in:pagato,non_pagato',
            'data_arrivo' => 'required|date',
            'data_scadenza' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        // normalizza data_arrivo se usi input datetime-local (esempio)
        if (!empty($data['data_arrivo']) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $data['data_arrivo'])) {
            // converti Y-m-d\TH:i in timestamp
            $data['data_arrivo'] = Carbon::createFromFormat('Y-m-d\TH:i', $data['data_arrivo'])->toDateTimeString();
        }

        $force = $request->input('force_duplicate') ? true : false;
        $duplicateWindowDays = (int) (config('pratiche.duplicate_days', 1) ?: 1);

        // cerca duplicati
        $duplicates = Practice::findPotentialDuplicates($data, $duplicateWindowDays);

        if ($duplicates->isNotEmpty() && !$force) {
            // blocca la creazione e torna indietro con i duplicati nella session
            return redirect()->back()
                ->withInput()
                ->with('duplicate_found', true)
                ->with('duplicate_list', $duplicates)
                ->with('duplicate_message', 'Sono state trovate pratiche simili. Controlla prima di creare una nuova pratica. Se vuoi creare comunque, premi "Crea comunque".');
        }

        // (la colonna 'codice' nella migration è NOT NULL; quindi forniamo un placeholder unico).
        $tmpCodice = 'PRAT-TMP-' . uniqid();

        // Inserimento iniziale (codice temporaneo)
        $dataWithTmp = $data;
        $dataWithTmp['codice'] = $tmpCodice;

        $pr = Practice::create($dataWithTmp);

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
            'stato_fattura'  => 'required|in:emessa,non_emessa',
            'stato_pagamento'=> 'required|in:pagato,non_pagato',
            'data_arrivo' => 'required|date',
            'data_scadenza' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        if (!empty($data['data_arrivo']) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $data['data_arrivo'])) {
            $data['data_arrivo'] = Carbon::createFromFormat('Y-m-d\TH:i', $data['data_arrivo'])->toDateTimeString();
        }

        $force = $request->input('force_duplicate') ? true : false;
        $duplicateWindowDays = (int) (config('pratiche.duplicate_days', 1) ?: 1);

        // cerca duplicati escludendo l'attuale ID
        $duplicates = Practice::findPotentialDuplicates($data, $duplicateWindowDays, $pr->id);

        if ($duplicates->isNotEmpty() && !$force) {
            return redirect()->back()
                ->withInput()
                ->with('duplicate_found', true)
                ->with('duplicate_list', $duplicates)
                ->with('duplicate_message', 'Sono state trovate pratiche simili. Se vuoi aggiornare comunque, premi "Aggiorna comunque".');
        }

        $pr->update($data);

        return redirect()->route('admin.pratiche.index')->with('status', 'Pratica aggiornata con successo.');
    }

    public function show($id)
    {
        $pratica = Practice::withTrashed()->findOrFail($id);
        return view('pratiche.show', compact('pratica'));
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
        $pr = Practice::findOrFail($id);
        $pr->stato = 'in_giacenza';
        $pr->data_arrivo = now();  // oppure lascia la data già presente se preferisci
        $pr->save();

        return redirect()->back()->with('status', 'Pratica #' . $pr->id . ' messa in giacenza.');

        // if ($pr->stato === 'in_giacenza') {
        //     return redirect()->back()->with('warning', 'La pratica è già in giacenza.');
        // }
    }

    public function removeGiacenza(Request $request, $id)
    {
        // carica la pratica anche se soft-deleted (modifica se non vuoi)
        $pratica = Practice::withTrashed()->findOrFail($id);

        // Controlla che sia effettivamente in giacenza
        if ($pratica->stato !== 'in_giacenza') {
            return redirect()->back()->with('warning', 'La pratica non risulta attualmente in giacenza.');
        }

        // stato target: puoi personalizzarlo tramite request (es. target_state=in_lavorazione)
        $targetState = $request->input('target_state', 'in_lavorazione');

        try {
            $oldState = $pratica->stato;
            $pratica->stato = $targetState;
            $pratica->save();

            // (opzionale) registra attività se usi activity log
            if (method_exists($pratica, 'tapActivity')) {
                // se usi spatie/activitylog o log personalizzato, puoi aggiungere qui
            }

            return redirect()->back()->with('success', "Pratica {$pratica->codice} rimossa dalla giacenza. Stato aggiornato: {$targetState}.");
        } catch (\Exception $e) {
            // log dell'errore per debug
            Log::error('Errore removeGiacenza: '.$e->getMessage(), ['pratica_id'=>$id]);
            return redirect()->back()->with('error', 'Si è verificato un errore nel rimuovere la pratica dalla giacenza.');
        }
    }

    public function markNotificaLetta($id)
    {
        $n = \App\Models\NotificaGiacenza::findOrFail($id);
        $n->letta = true;
        $n->save();
        return redirect()->back();
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
        if (!ctype_digit((string) $year)) {
            return redirect()->back()->with('error', 'Anno non valido.');
        }

        $filename = "pratiche_{$year}.xls"; // Excel aprirà l'HTML come foglio

        // prendi le pratiche
        $pratiche = Practice::whereYear('data_arrivo', $year)
                    ->orderBy('data_arrivo','asc')
                    ->get();

        // prepara il logo (base64) — path in public/images/logo.png
        $logoPath = public_path('images/favicon.ico');
        $logoData = null;
        if (file_exists($logoPath) && is_readable($logoPath)) {
            $type = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png'; // fallback se fileinfo non disponibile
            $data = file_get_contents($logoPath);
            $base64 = base64_encode($data);
            $logoData = "data:{$type};base64,{$base64}";
        }

        // render della view (passa $logoData)
        $html = view('pratiche.exports.table_export', compact('pratiche', 'logoData', 'year'))->render();

        // Excel in alcune versioni necessita BOM per riconoscere l'UTF-8 correttamente
        $bom = "\xEF\xBB\xBF";

        return response($bom . $html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
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

        // path del logo
        $logoPath = public_path('images/favicon.ico');

        // prepara il source embeddato (base64) se il file esiste
        $logoSrc = null;
        if (file_exists($logoPath) && is_readable($logoPath)) {
            $mime = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png';
            $data = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:' . $mime . ';base64,' . $data;
        }

        $html = view('pratiche.exports.word_export', compact('pratiche', 'logoSrc', 'year'))->render();

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

        // percorso file logo dentro public (modifica se lo tieni altrove)
        $logoPath = public_path('images/amglabweb.png');

        if (!file_exists($logoPath)) {
            // fallback: puoi usare un logo alternativo o lasciare vuoto
            // qui ritorniamo comunque la vista senza logo, ma potresti anche abortare
            Log::warning("Logo non trovato: {$logoPath}");
            $logoFileUrl = null;
        } else {
            // $normalized = str_replace('\\', '/', $logoPath);

            // $logoFileUrl = 'file:///' . ltrim($normalized, '/');
            $logoFileUrl = 'file:///' . str_replace('\\', '/', $logoPath);
        }

        // render della view (passiamo anche logoFileUrl)
        $html = view('pratiche.exports.pdf_export', compact('pratiche', 'year', 'logoFileUrl'))->render();

        // binario wkhtmltopdf (controllo e normalizzazione)
        $binaryPath = config('snappy.pdf.binary') ?: 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';

        // rimuovi eventuali virgolette attorno al path per file_exists
        $cleanBinary = trim($binaryPath, '"');

        if (!file_exists($cleanBinary)) {
            // non usare dd() in produzione: logga e ritorna con messaggio
            Log::error("wkhtmltopdf binary non trovato: {$cleanBinary}");
            return redirect()->back()->with('error', 'Errore: eseguibile wkhtmltopdf non trovato sul server.');
        }

        // se il path contiene spazi, keep the original $binaryPath so that the constructor can accept quotes if needed
        $snappy = new \Knp\Snappy\Pdf($binaryPath);

        // opzioni
        $snappy->setOption('page-size', 'A4');
        $snappy->setOption('orientation', 'Landscape');
        $snappy->setOption('encoding', 'UTF-8');
        $snappy->setOption('enable-local-file-access', true);
        $snappy->setOption('no-outline', true);
        // margine inferiore (lascia spazio per footer fisso nel CSS)
        $snappy->setOption('margin-bottom', '20mm');
        $snappy->setOption('footer-right', 'Pagina [page] di [toPage]');
        $snappy->setOption('footer-font-size', 7);
        $snappy->setOption('footer-spacing', 5);
        $snappy->setOption('footer-left', 'AMG Lab - Gestione Pratiche');

         // genera il PDF");

        try {
            $pdfContent = $snappy->getOutputFromHtml($html);
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"pratiche_{$year}.pdf\"",
            ]);
        } catch (\Exception $e) {
            Log::error('Errore generazione PDF: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return redirect()->back()->with('error', 'Si è verificato un errore durante la generazione del PDF. Controlla i log.');
        }
    }

    public function activityLogs()
    {
        $logs = Activity::orderBy('created_at', 'desc')->paginate(25);
        return view('admin.logs.index', compact('logs'));
    }

    public function activityLogsPartial(Request $request)
    {
        // partial per modal (carica primi 25)
        $logs = Activity::orderBy('created_at', 'desc')->limit(25)->get();
        return view('admin.logs.partial', compact('logs'));
    }

    public function dashboard(Request $request)
    {
        // KPI numerici
        $totale = Practice::count();
        $inGiacenza = Practice::where('stato', 'in_giacenza')->count();
        $inLavorazione = Practice::where('stato', 'in_lavorazione')->count();
        $completate = Practice::where('stato', 'completata')->count();

        // Dati per grafico linea (ultimi 12 mesi, entrate in giacenza)
        $oggi = Carbon::now();
        $inizio12 = (clone $oggi)->subMonths(11)->startOfMonth();

        $entrateGiacenza = Practice::select(
                DB::raw("YEAR(data_arrivo) as anno"),
                DB::raw("MONTH(data_arrivo) as mese"),
                DB::raw("COUNT(*) as totale")
            )
            ->where('stato', 'in_giacenza')
            ->whereDate('data_arrivo', '>=', $inizio12)
            ->groupBy('anno', 'mese')
            ->orderBy('anno', 'asc')
            ->orderBy('mese', 'asc')
            ->get();

        $period = [];
        $labels = [];
        $datasets = [];

        for ($i = 0; $i < 12; $i++) {
            $m = (clone $inizio12)->addMonths($i);
            $period[] = $m;
            $labels[] = $m->translatedFormat('M Y'); // es. "ott 2024" (Carbon::setLocale('it') presente in AppServiceProvider)
            $datasets[] = 0;
        }

        foreach ($entrateGiacenza as $row) {
            foreach ($period as $idx => $m) {
                if ($m->year == $row->anno && $m->month == $row->mese) {
                    $datasets[$idx] = (int)$row->totale;
                    break;
                }
            }
        }

        // distribuzione stato per doughnut
        $statiCount = Practice::select('stato', DB::raw('COUNT(*) as totale'))
            ->groupBy('stato')
            ->get()
            ->pluck('totale','stato')
            ->toArray();

        // recenti pratiche create / modificate
        $recent = Practice::orderBy('updated_at','desc')->limit(6)->get(['id','codice','cliente_nome','stato','updated_at']);

        // ultime attività (facoltativo, se usi activitylog)
        $recentActivities = [];

        if (class_exists(Activity::class)) {
            $recentActivities = Activity::orderBy('created_at','desc')->limit(5)->get();
        }

        return view('admin.dashboard', compact(
            'totale','inGiacenza','inLavorazione','completate',
            'labels','datasets','statiCount','recent','recentActivities'
        ));
    }

    public function toggleInvoiceStatus(Request $request, $id)
    {
        $practice = Practice::findOrFail($id);

        // autorizzazione opzionale (se hai policies)
        // $this->authorize('update', $practice);

        // toggle
        $practice->stato_fattura = ($practice->stato_fattura === 'emessa') ? 'non_emessa' : 'emessa';
        $practice->save();

        // opzionale: log attività (se usi activitylog)
        if (function_exists('activity')) {
            activity('pratiche')
                ->performedOn($practice)
                ->causedBy(auth()->user())
                ->withProperties(['stato_fattura' => $practice->stato_fattura])
                ->log("Stato fattura cambiato in {$practice->stato_fattura}");
        }

        return response()->json([
            'success' => true,
            'stato_fattura' => $practice->stato_fattura,
            'message' => 'Stato fattura aggiornato.'
        ]);
    }

    public function togglePaymentStatus(Request $request, $id)
    {
        $practice = Practice::findOrFail($id);

        // $this->authorize('update', $practice);

        $practice->stato_pagamento = ($practice->stato_pagamento === 'pagato') ? 'non_pagato' : 'pagato';
        $practice->save();

        if (function_exists('activity')) {
            activity('pratiche')
                ->performedOn($practice)
                ->causedBy(auth()->user())
                ->withProperties(['stato_pagamento' => $practice->stato_pagamento])
                ->log("Stato pagamento cambiato in {$practice->stato_pagamento}");
        }

        return response()->json([
            'success' => true,
            'stato_pagamento' => $practice->stato_pagamento,
            'message' => 'Stato pagamento aggiornato.'
        ]);
    }
}
