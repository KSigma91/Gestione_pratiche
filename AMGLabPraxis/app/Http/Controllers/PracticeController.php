<?php

namespace App\Http\Controllers;
use App\Models\Practice;
use App\Jobs\DeletePractice;
use Carbon\Carbon;

use Illuminate\Http\Request;

class PracticeController extends Controller
{
    public function __construct()
    {
        // Assicura che tutte le rotte richiedano autenticazione di sessione
        $this->middleware('auth');
    }

    // Lista tutte le pratiche (con filtri opzionali)
    public function index(Request $request)
    {
        $q = Practice::query();

        if ($request->has('stato')) {
            $q->where('stato', $request->input('stato'));
        }
        if ($request->has('cliente')) {
            $q->where('cliente_nome', 'like', '%' . $request->input('cliente') . '%');
        }

        $perPage = $request->input('per_page', 25);
        return response()->json($q->paginate($perPage));
    }

    // Crea una nuova pratica
    public function store(Request $request)
    {
        $data = $request->validate([
            'codice' => 'required|string|max:100',
            'cliente_nome' => 'required|string|max:255',
            'tipo_pratica' => 'required|string|max:100',
            'data_arrivo' => 'required|date_format:Y-m-d\TH:i',
            'data_scadenza' => 'nullable|date_format:Y-m-d\TH:i',
            'note' => 'nullable|string',
        ]);

        $pr = Practice::create($data);
        return response()->json($pr, 201);
    }

    // Mostra una pratica
    public function show($id)
    {
        $pr = Practice::findOrFail($id);
        return response()->json($pr);
    }

    // Aggiorna
    public function update(Request $request, $id)
    {
        $pr = Practice::findOrFail($id);
        $data = $request->validate([
            'codice' => 'sometimes|required|string|max:100',
            'cliente_nome' => 'sometimes|required|string|max:255',
            'tipo_pratica' => 'sometimes|required|string|max:100',
            'stato' => 'sometimes|in:in_giacenza,in_lavorazione,completata,annullata',
            'data_arrivo' => 'sometimes|date',
            'data_scadenza' => 'nullable|date',
            'note' => 'nullable|string',
        ]);
        $pr->update($data);
        return response()->json($pr);
    }

    // Soft delete (eliminazione "al momento")
    public function destroy($id)
    {
        $pr = Practice::findOrFail($id);
        $pr->delete();
        return response()->json(['message' => 'Pratica eliminata (soft delete).']);
    }

    // Eliminazione definitiva (forza)
    public function forceDelete($id)
    {
        $pr = Practice::withTrashed()->findOrFail($id);
        $pr->forceDelete();
        return response()->json(['message' => 'Pratica eliminata definitivamente.']);
    }

    // Lista degli alert: pratiche in giacenza da almeno 15 giorni
    public function alerts(Request $request)
    {
        $cutoff = Carbon::now()->subDays(15)->toDateString();
        $q = Practice::where('stato', 'in_giacenza')
            ->whereDate('data_arrivo', '<=', $cutoff)
            ->orderBy('data_arrivo', 'asc');

        return response()->json($q->get());
    }
}
