<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Practice extends Model
{
    use SoftDeletes;

    protected $table = 'pratiche';

    protected $fillable = [
        'codice',
        'cliente_nome',
        'caso',
        'tipo_pratica',
        'stato',
        'data_arrivo',
        'data_scadenza',
        'note',
        'alerted',
    ];

    protected $dates = ['data_arrivo', 'data_scadenza', 'deleted_at', 'created_at', 'updated_at'];
}
