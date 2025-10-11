<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Practice;
use Illuminate\Support\Str;
use App\Models\NotificaGiacenza;
use App\Observers\PracticeObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
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
// BREADCRUMBS
            $breadcrumbs = [];

            try {
                $route = Route::current();
                $routeName = $route ? $route->getName() : null;
                $routeParams = $route ? $route->parameters() : [];

                $config = config('breadcrumbs', []);
                $map = $config['map'] ?? [];
                $enableFallback = $config['enable_fallback'] ?? true;

                // primo: se la rotta è mappata staticamente nel config (array) => usalo
                if ($routeName && isset($map[$routeName])) {
                    $entry = $map[$routeName];

                    // entry statica come array di elementi ['label'=>..., 'url'=>...]
                    if (is_array($entry)) {
                        $items = [];
                        foreach ($entry as $it) {
                            $label = $it['label'] ?? null;
                            $urlSpec = $it['url'] ?? null;
                            $url = null;

                            // supporto a urlSpec tipo "route:route.name" oppure "url:/path"
                            if (is_string($urlSpec) && Str::startsWith($urlSpec, 'route:')) {
                                $candidateRoute = substr($urlSpec, 6);
                                if (Route::has($candidateRoute)) {
                                    try { $url = route($candidateRoute, $routeParams); } catch (\Exception $e) { $url = null; }
                                }
                            } elseif (is_string($urlSpec) && Str::startsWith($urlSpec, 'url:')) {
                                $url = substr($urlSpec, 4);
                            } else {
                                // se è già un url assoluto o null lo accettiamo così com'è
                                $url = $urlSpec;
                            }

                            $items[] = ['label' => $label, 'url' => $url];
                        }
                        $breadcrumbs = $items;
                    }
                    // fallback: se per qualche motivo è callable (non raccomandato nel config),
                    // lo invochiamo (attenzione: Closure in config impedirà config:cache).
                    elseif (is_callable($entry)) {
                        try {
                            $breadcrumbs = call_user_func($entry, $routeParams);
                        } catch (\Exception $e) {
                            $breadcrumbs = [];
                        }
                    }
                }
                // gestione dinamica per show/edit/archive.view di pratiche (etichetta con codice/cliente)
                elseif ($routeName && in_array($routeName, ['admin.pratiche.edit','admin.pratiche.show','admin.pratiche.archive.view'])) {
                    if ($routeName === 'admin.pratiche.archive.view') {
                        $year = $routeParams['year'] ?? null;
                        $month = $routeParams['month'] ?? null;
                        $label = ($month && $year)
                            ? \Carbon\Carbon::create()->month((int)$month)->translatedFormat('F') . " {$year}"
                            : 'Archivio mese';

                        $breadcrumbs = [
                            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                            ['label' => 'Archivio', 'url' => route('admin.pratiche.archive')],
                            ['label' => $label, 'url' => null],
                        ];
                    } else {
                        // edit/show
                        $id = $routeParams['id'] ?? $routeParams['pratica'] ?? null;
                        $labelPratica = $id ? "Pratica #{$id}" : 'Pratica';

                        if ($id) {
                            try {
                                $pr = \App\Models\Practice::withTrashed()->find($id);
                                if ($pr) {
                                    $labelPratica = ($pr->codice ?? "Pratica #{$id}") . ' — ' . Str::limit($pr->cliente_nome, 30);
                                }
                            } catch (\Exception $e) {
                                // fallback: rimane il label base
                            }
                        }

                        $breadcrumbs = [
                            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                            ['label' => 'Gestionale Pratiche', 'url' => route('admin.pratiche.index')],
                            ['label' => $labelPratica, 'url' => null],
                        ];
                    }
                }
                // fallback generico: genera breadcrumb dal route name traducendo le parti
                elseif ($enableFallback && $routeName) {
                    // mappa parole chiave in italiano
                    $partTranslations = [
                        'register' => 'Registrati',
                        'login' => 'Accedi',
                        'reset' => 'Reimposta Password',
                        'request' => 'Richiesta Link Reset',
                        'sessions' => 'Sessioni',
                        'admin' => 'Dashboard',
                        'pratiche' => 'Pratiche',
                        'archive' => 'Archivio',
                        'trash' => 'Cestino',
                        'logs' => 'Log Attività',
                        'dashboard' => 'Dashboard',
                        'index' => 'Elenco',
                        'create' => 'Nuova',
                        'store' => 'Salva',
                        'edit' => 'Modifica',
                        'show' => 'Visualizza',
                        'update' => 'Aggiorna',
                        'delete' => 'Elimina',
                        'export' => 'Esporta',
                        'pdf' => 'PDF',
                        'csv' => 'CSV',
                        'excel' => 'Excel',
                        'word' => 'Word',
                    ];

                    $fallbackVerbs = [
                        'show' => 'Visualizza','edit' => 'Modifica','create' => 'Nuova',
                        'index' => 'Elenco','archive' => 'Archivio','trash' => 'Cestino'
                    ];

                    $parts = explode('.', $routeName);
                    $items = [];
                    $accumParts = [];

                    foreach ($parts as $i => $part) {
                        $accumParts[] = $part;
                        $candidate = implode('.', $accumParts);

                        if (array_key_exists($part, $partTranslations)) {
                            $label = $partTranslations[$part];
                        } else {
                            $label = ucfirst(str_replace(['_', '-'], ' ', $part));
                            if (isset($fallbackVerbs[$part])) {
                                $label = $fallbackVerbs[$part];
                            }
                        }

                        if (Route::has($candidate)) {
                            try {
                                $url = route($candidate, $routeParams);
                            } catch (\Exception $e) {
                                $url = null;
                            }
                            $items[] = ['label' => $label, 'url' => $url];
                        } else {
                            $items[] = ['label' => $label, 'url' => null];
                        }
                    }

                    $breadcrumbs = $items;
                }
            } catch (\Exception $e) {
                $breadcrumbs = [];
            }

            $view->with('breadcrumbs', $breadcrumbs);
        });
    }
}
