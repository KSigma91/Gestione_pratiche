<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use App\Models\Practice;

Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});

Breadcrumbs::for('admin.pratiche.index', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('Pratiche', route('admin.pratiche.index'));
});

Breadcrumbs::for('admin.pratiche.show', function (BreadcrumbTrail $trail, Practice $pratica) {
    $trail->parent('home');
    $trail->push("Pratica {$pratica->codice} - {$pratica->cliente_nome}", route('admin.pratiche.show', $pratica));
});
