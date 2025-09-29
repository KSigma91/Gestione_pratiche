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
        $archive = Cache::remember($cacheKey, now()->addMinutes(10), function () {
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
}
