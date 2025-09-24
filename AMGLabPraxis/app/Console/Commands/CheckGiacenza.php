<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Practice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
    protected $description = 'Controlla pratiche in giacenza >= 15 giorni e marca alert; esegue eliminazioni programmate.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cutoff = Carbon::now()->subDays(15)->toDateString();

        $pratiche = Practice::where('stato', 'in_giacenza')
            ->whereDate('data_arrivo', '<=', $cutoff)
            ->where('alerted', 0)
            ->get();

        foreach ($pratiche as $p) {
            $p->alerted = 1;
            $p->save();
            // Log o creazione record in pratiche_alerts
            DB::table('pratiche_alerts')->insert([
                'pratica_id' => $p->id,
                'alerted_at' => Carbon::now()->toDateTimeString(),
                'note' => 'Alert automatico: giacenza >= 15 giorni'
            ]);
            $this->info('Alert creato per pratica id='.$p->id.' codice='.$p->codice);
        }

        // Esegui eliminazioni programmate
        $toDelete = Practice::whereNotNull('delete_scheduled_at')
            ->where('delete_scheduled_at', '<=', Carbon::now())
            ->get();

        foreach ($toDelete as $d) {
            $this->info('Eliminazione programmata: prat. id='.$d->id.' codice='.$d->codice);
            $d->forceDelete();
        }

        return 0;
    }
}
