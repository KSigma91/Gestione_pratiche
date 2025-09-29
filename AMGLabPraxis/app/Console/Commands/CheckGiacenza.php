<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Practice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckGiacenza extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pratiche:check-giacenza';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Controlla le pratiche per gli alert di giacenza e processa le eliminazioni programmate';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = \Carbon\Carbon::now('UTC');
        $this->info("Now UTC: " . $now->toDateTimeString());

        try {
            $toDelete = \App\Models\Practice::withTrashed()
                ->whereNotNull('delete_scheduled_at')
                ->where('delete_scheduled_at', '<=', $now->toDateTimeString())
                ->get();

        } catch (\Exception $e) {
            Log::error("CheckGiacenza: errore fetching pratiche da eliminare programmato - " . $e->getMessage());
            $this->error("Errore fetch pratiche: " . $e->getMessage());
            return 1;
        }

        if ($toDelete->isEmpty()) {
            $this->info("Numero di pratiche da eliminare: 0");
            Log::info("CheckGiacenza: nessuna pratica programmate <= now ({$now})");
        } else {
            $this->info("Trovate {$toDelete->count()} pratiche da eliminare programmate");
            foreach ($toDelete as $p) {
                $this->info("→ id={$p->id}, codice={$p->codice}, delete_scheduled_at={$p->delete_scheduled_at}");
                Log::info("About to forceDelete id={$p->id} codice={$p->codice}");

                try {
                    $p->forceDelete();
                    $this->info("Eliminata definitivamente id={$p->id} codice={$p->codice}");
                    Log::info("Eliminata definitivamente id={$p->id} codice={$p->codice}");
                } catch (\Exception $e2) {
                    $this->error("Errore elim. id={$p->id}: " . $e2->getMessage());
                    Log::error("Errore forceDelete id={$p->id} codice={$p->codice} - " . $e2->getMessage());
                }
            }
        }

        $this->info("CheckGiacenza terminato alle " . \Carbon\Carbon::now());
        return 0;
    }
}
