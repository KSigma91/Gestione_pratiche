<?php

use Illuminate\Support\Str;


return [

    /*
    |------------------------------------------------------------------------
    | Mappa route_name => generator (closure) | labels & url
    |------------------------------------------------------------------------
    | Ogni key è il nome della route. Il valore è una closure che riceve
    | l'array dei parametri della route e deve restituire un array di
    | elementi ['label' => 'Testo', 'url' => '...'].
    |
    | L'ultimo elemento sarà considerato "active" (senza link).
    */

    'map' => [

        // dashboard
        'admin.dashboard' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
        ],
        // pratiche index
        'admin.pratiche.index' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
            ['label' => 'Lista Pratiche', 'url' => 'route:admin.pratiche.index'],
        ],

        'admin.pratiche.create' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
            ['label' => 'Lista Pratiche', 'url' => 'route:admin.pratiche.index'],
            ['label' => 'Nuova pratica', 'url' => null],
        ],
        // edit — label dinamica: verrà risolta a runtime (es. "PRAT-00123 — Mario Rossi")
        'admin.pratiche.edit' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
            ['label' => 'Lista Pratiche', 'url' => 'route:admin.pratiche.index'],
            // placeholder: provider deve risolvere {codice} e {cliente} caricando il modello se necessario
            ['label' => '{codice} — {cliente}', 'url' => null],
        ],

        'admin.pratiche.archive' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
            ['label' => 'Archivio', 'url' => 'route:admin.pratiche.archive'],
        ],
        // archive view: placeholders {month_name} e {year}
        'admin.pratiche.archive.view' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
            ['label' => 'Archivio', 'url' => 'route:admin.pratiche.archive'],
            ['label' => '{month_name} {year}', 'url' => null],
        ],

        'admin.pratiche.trash' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
            ['label' => 'Cestino', 'url' => 'route:admin.pratiche.trash'],
        ],

        'admin.logs' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
            ['label' => 'Log attività', 'url' => 'route:admin.logs'],
        ],
        // export PDF per anno — placeholder {year}
        'admin.pratiche.export.year.pdf' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
            ['label' => 'Archivio', 'url' => 'route:admin.pratiche.archive'],
            ['label' => 'Esporta {year}', 'url' => null],
        ],
        // show (visualizza pratica) — label dinamica come in edit
        'admin.pratiche.show' => [
            ['label' => 'Dashboard', 'url' => 'route:admin.dashboard'],
            ['label' => 'Lista Pratiche', 'url' => 'route:admin.pratiche.index'],
            ['label' => '{codice} — {cliente}', 'url' => null],
        ],
    ],

    /*
    | fallback_locale_labels => boolean
    | se true: se una route non è mappata, generiamo breadcrumb automaticamente
    | derivata dal route name (es. admin.pratiche.edit -> Admin / Pratiche / Edit)
    */
    'enable_fallback' => true,
];
