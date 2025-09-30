<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;

class PracticeArchive extends Model
{
    protected $table = 'practice_archives';

    protected $fillable = [
        'practice_id',
        'codice',
        'cliente_nome',
        'tipo_pratica',
        'caso',
        'stato',
        'data_arrivo',
        'data_scadenza',
        'note',
        'action',
        'action_by',
        'action_by_name',
        'action_note',
    ];

    protected $casts = [
        'data_arrivo' => 'datetime',
        'data_scadenza' => 'datetime',
    ];

    public function practice()
    {
        return $this->belongsTo(Practice::class, 'practice_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'action_by');
    }
}
