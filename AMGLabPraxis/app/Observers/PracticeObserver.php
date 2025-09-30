<?php
namespace App\Observers;

use App\Models\Practice;
use App\Models\PracticeArchive;
use Illuminate\Support\Facades\Auth;

class PracticeObserver
{
    protected function archive(Practice $p, $action, $note = null)
    {
        try {
            $user = Auth::user();
        } catch (\Exception $e) {
            $user = null;
        }

        PracticeArchive::create([
            'practice_id'    => $p->id,
            'codice'         => $p->codice,
            'cliente_nome'   => $p->cliente_nome,
            'tipo_pratica'   => $p->tipo_pratica,
            'caso'           => $p->caso,
            'stato'          => $p->stato,
            'data_arrivo'    => $p->data_arrivo,
            'data_scadenza'  => $p->data_scadenza,
            'note'           => $p->note,
            'action'         => $action,
            'action_by'      => $user ? $user->id : null,
            'action_by_name' => $user ? ($user->name ?? $user->email ?? 'Utente') : 'Sistema',
            'action_note'    => $note,
        ]);
    }

    public function created(Practice $p)
    {
        $this->archive($p, 'created');
    }

    public function updated(Practice $p)
    {
        $this->archive($p, 'updated');
    }

    public function deleted(Practice $p)
    {
        // questo viene chiamato sia per soft delete che forceDelete (laravel chiama deleted anche per force?)
        // per sicurezza, registriamo come 'deleted' (soft) qui; forceDelete viene gestito in forceDeleted
        $this->archive($p, 'deleted');
    }

    public function restored(Practice $p)
    {
        $this->archive($p, 'restored');
    }

    public function forceDeleted(Practice $p)
    {
        // prima che sia eliminata definitivamente, salviamo lo snapshot come force_deleted
        $this->archive($p, 'force_deleted');
    }
}
