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
        'tipo_pratica',
        'stato',
        'data_arrivo',
        'data_scadenza',
        'note',
        'alerted',
        'delete_scheduled_at'
    ];

    protected $dates = ['data_arrivo', 'data_scadenza', 'delete_scheduled_at', 'deleted_at', 'created_at', 'updated_at'];
}
