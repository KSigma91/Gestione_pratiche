<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Practice;
use Carbon\Carbon;

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

        if ($request->has('stato')) {
            $q->where('stato', $request->input('stato'));
        }
        if ($request->has('cliente')) {
            $q->where('cliente_nome', 'like', '%' . $request->input('cliente') . '%');
        }

        $perPage = 15;
        $pratiche = $q->orderBy('data_arrivo', 'desc')->paginate($perPage)->appends($request->except('page'));

        return view('pratiche.index', compact('pratiche'));
    }

    // Soft delete
    public function destroy($id)
    {
        $pr = Practice::findOrFail($id);
        $pr->delete();
        return redirect()->back()->with('status', 'Pratica eliminata (soft delete).');
    }

    // Force delete
    public function forceDelete($id)
    {
        $pr = Practice::withTrashed()->findOrFail($id);
        $pr->forceDelete();
        return redirect()->back()->with('status', 'Pratica eliminata definitivamente.');
    }

    // Schedule delete
    public function scheduleDelete(Request $request, $id)
    {
        $request->validate([
            'delete_scheduled_at' => 'required|date'
        ]);
        $pr = Practice::findOrFail($id);
        $pr->delete_scheduled_at = $request->input('delete_scheduled_at');
        $pr->save();
        return redirect()->back()->with('status', 'Eliminazione programmata impostata: ' . $pr->delete_scheduled_at);
    }
}
