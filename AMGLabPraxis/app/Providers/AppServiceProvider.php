<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Practice;
use App\Models\NotificaGiacenza;
use App\Observers\PracticeObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // imposta la locale per Carbon su italiano
        Carbon::setLocale('it');

        // opzionale: imposta timezone se non già impostata
        date_default_timezone_set(config('app.timezone', 'Europe/Rome'));

        View::composer('*', function ($view) {
            try {
                $notifiche = NotificaGiacenza::where('letta', false)
                    ->with('pratica')
                    ->orderBy('notificata_at', 'desc')
                    ->get();

            } catch (\Exception $e) {
                $notifiche = collect();
            }

            $view->with('global_notifiche_giacenza', $notifiche);

            try {
                $trashCount = Practice::onlyTrashed()->count();

                // Numero totale di pratiche in giacenza (senza filtro di data)
                $giacenzaCount = Practice::where('stato', 'in_giacenza')->count();

                // Lista recente (o limitata) di pratiche in giacenza
                $recentAlerts = Practice::where('stato', 'in_giacenza')
                    ->orderBy('data_arrivo', 'asc')
                    ->limit(5)
                    ->get(['id', 'codice', 'cliente_nome', 'data_arrivo']);
            } catch (\Exception $e) {
                $trashCount = 0;
                $giacenzaCount = 0;
                $recentAlerts = collect();
            }

            Practice::observe(PracticeObserver::class);

            $view->with('global_trash_count', $trashCount);
            $view->with('global_giacenza_count', $giacenzaCount);
            $view->with('global_recent_alerts', $recentAlerts);
        });
    }
}
