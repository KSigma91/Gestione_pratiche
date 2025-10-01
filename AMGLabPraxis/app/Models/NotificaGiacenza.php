<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificaGiacenza extends Model
{
    protected $table = 'notifiche_giacenza';

    protected $fillable = [
        'pratica_id',
        'letta',
        'notificata_at',
    ];

    public function pratica(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Practice::class, 'pratica_id');
    }
}

