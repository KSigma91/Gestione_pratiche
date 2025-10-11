<?php

namespace App\Providers;

use App\Models\Practice;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BreadcrumbsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        view()->composer('*', function ($view) {
            if (!$view->offsetExists('breadcrumbs')) {
                return;
            }

            $breadcrumbs = $view->breadcrumbs;
            $isArray = is_array($breadcrumbs);

            $collection = collect($breadcrumbs)->map(function ($crumb) {
                if (isset($crumb['label'])) {
                    $label = $crumb['label'];

                    // placeholder {codice} / {cliente}
                    if (strpos($label, '{codice}') !== false || strpos($label, '{cliente}') !== false) {
                        $practice = null;
                        if (request()->route('practice')) {
                            $practice = request()->route('practice');
                        } elseif (request()->route('id')) {
                            $practice = Practice::find(request()->route('id'));
                        }
                        if ($practice) {
                            $label = str_replace(
                                ['{codice}', '{cliente}'],
                                [$practice->codice, ($practice->cliente_nome ?? '')],
                                $label
                            );
                        }
                    }

                    // placeholder {month_name} e {year}
                    if (strpos($label, '{month_name}') !== false || strpos($label, '{year}') !== false) {
                        $year = request()->route('year') ?? request()->get('year') ?? null;
                        $month = request()->route('month') ?? request()->get('month') ?? null;

                        if ($year !== null) {
                            $label = str_replace('{year}', $year, $label);
                        }

                        if (strpos($label, '{month_name}') !== false && $month !== null) {
                            // usa Carbon per tradurre il mese
                            try {
                                $monthInt = (int)$month;
                                $monthName = Carbon::create()->month($monthInt)->translatedFormat('F');
                            } catch (\Exception $e) {
                                $monthName = $month;
                            }
                            $label = str_replace('{month_name}', ucfirst($monthName), $label);
                        }
                    }

                    $crumb['label'] = $label;
                }
                return $crumb;
            });

            if ($isArray) {
                $breadcrumbs = $collection->all();
            } else {
                $breadcrumbs = $collection;
            }

            $view->with('breadcrumbs', $breadcrumbs);
        });
    }
}
