<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Practice;
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
        View::composer('*', function ($view) {
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
