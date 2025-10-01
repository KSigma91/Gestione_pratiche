<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Practice;
use App\Models\NotificaGiacenza;

class GeneraNotificheGiacenza extends Command
{
    protected $signature = 'notifiche:giacenza';
    protected $description = 'Genera notifiche per pratiche in giacenza da troppi giorni';

    public function handle()
    {
        // soglia, ad esempio 15 giorni
        $cutoff = Carbon::now()->subDays(15);

        // seleziona pratiche che sono in giacenza e con data_arrivo <= cutoff
        $pratiche = Practice::where('stato', 'in_giacenza')
            ->whereDate('data_arrivo', '<=', $cutoff)
            ->get();

        foreach ($pratiche as $pr) {
            // verifica se già notificata
            $exists = NotificaGiacenza::where('pratica_id', $pr->id)->exists();
            if (!$exists) {
                NotificaGiacenza::create([
                    'pratica_id' => $pr->id,
                    'letta' => false,
                    'notificata_at' => Carbon::now(),
                ]);
            }
        }

        $this->info('Notifiche di giacenza generate.');
    }
}
