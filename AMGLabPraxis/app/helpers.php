<?php
// app/helpers.php

if (! function_exists('__campoLabel')) {
    function __campoLabel(string $campo): string
    {
        $mappa = [
            'cliente_nome' => 'Cliente',
            'tipo_pratica' => 'Tipo di pratica',
            'caso'          => 'Caso',
            'stato'         => 'Stato',
            'data_arrivo'   => 'Data di arrivo',
            'data_scadenza' => 'Data di scadenza',
            'note'          => 'Note',
            // aggiungi altri se serve
        ];
        return $mappa[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
    }
}
