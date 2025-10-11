<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

class Practice extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'pratiche';

    protected $fillable = [
        'codice',
        'cliente_nome',
        'caso',
        'tipo_pratica',
        'stato',
        'stato_fattura',
        'stato_pagamento',
        'data_arrivo',
        'data_scadenza',
        'note',
        'alerted',
    ];

    protected $dates = ['data_arrivo', 'data_scadenza', 'deleted_at', 'created_at', 'updated_at'];

    protected static $logAttributes = [
        'codice',
        'cliente_nome',
        'tipo_pratica',
        'caso',
        'stato',
        'stato_fattura',
        'stato_pagamento',
        'data_arrivo',
        'data_scadenza',
        'note',
    ];

    protected static $logOnlyDirty = true;  // logga solo quando cambiano

    protected static $submitEmptyLogs = false;

    public static function findPotentialDuplicates(array $attributes, ?int $days = null, $excludeId = null): Collection
    {
        $days = $days ?? (int) (config('pratiche.duplicate_days', 1) ?: 1);

        $cliente = isset($attributes['cliente_nome']) ? mb_strtolower(trim($attributes['cliente_nome'])) : null;
        $tipo = isset($attributes['tipo_pratica']) ? mb_strtolower(trim($attributes['tipo_pratica'])) : null;
        $casoRaw = array_key_exists('caso', $attributes) ? $attributes['caso'] : null;
        $caso = is_null($casoRaw) || $casoRaw === '' ? null : mb_strtolower(trim($casoRaw));

        $query = static::query();

        // confronto cliente_nome se fornito
        if (!empty($cliente)) {
            $query->whereRaw('LOWER(TRIM(cliente_nome)) = ?', [$cliente]);
        }

        // confronto tipo_pratica se fornito
        if (!empty($tipo)) {
            $query->whereRaw('LOWER(TRIM(tipo_pratica)) = ?', [$tipo]);
        }

        // confronto caso: se passato null => cerca null/empty, altrimenti confronta testo
        if (!is_null($casoRaw)) {
            if (is_null($caso)) {
                $query->where(function($q){
                    $q->whereNull('caso')->orWhereRaw("TRIM(caso) = ''");
                });
            } else {
                $query->whereRaw('LOWER(TRIM(caso)) = ?', [$caso]);
            }
        }

        // data_arrivo: se fornita, limita a finestra +/- $days
        if (!empty($attributes['data_arrivo'])) {
            try {
                $d = Carbon::parse($attributes['data_arrivo']);
                $from = (clone $d)->subDays($days)->startOfDay();
                $to = (clone $d)->addDays($days)->endOfDay();
                $query->whereBetween('data_arrivo', [$from->toDateTimeString(), $to->toDateTimeString()]);
            } catch (\Exception $e) {
                // ignore if parse fails
            }
        }

        // escludi ID (utile per update che non vogliamo confrontare con se stesso)
        if (!empty($excludeId)) {
            $query->where('id', '!=', $excludeId);
        }

        // ignora soft-deleted by default (cambia con withTrashed() se vuoi includerle)
        return $query->orderBy('data_arrivo', 'desc')->limit(50)->get();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        $codice = $this->codice ?? ('#' . $this->id);

        switch ($eventName) {
            case 'created':  return "Creata pratica {$codice}";
            case 'updated':  return "Modificata pratica {$codice}";
            case 'deleted':  return "Eliminata pratica {$codice}";
            case 'restored': return "Ripristinata pratica {$codice}";
            default:         return "Azione {$eventName} su pratica {$codice}";
        }
    }

    // arricchiscie le properties con info utili
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->log_name = 'pratiche';

        // base descrizione (già presente in description)
        $descrizione = $activity->description ?? $this->getDescriptionForEvent($eventName);

        // prova a ottenere le properties generate automaticamente (old/attributes)
        $props = $activity->properties;

        // se properties è stringa, prova a decodificare
        if (is_string($props) && $props !== '') {
            $decoded = json_decode($props, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $props = $decoded;
            } else {
                $props = ['raw' => $props];
            }
        }

        // costruisci un messaggio leggibile
        $messageParts = [];

        // metti sempre la descrizione iniziale
        $messageParts[] = $descrizione;

        // se sono presenti 'old' e 'attributes', elabora le modifiche
        if (!empty($props['old']) && !empty($props['attributes']) && is_array($props['old']) && is_array($props['attributes'])) {
            foreach ($props['old'] as $field => $oldValue) {
                $newValue = data_get($props['attributes'], $field, null);
                // non mostrare changed se uguali o entrambi vuoti
                if ($oldValue != $newValue) {
                    $messageParts[] = "Campo \"{$field}\": \"{$oldValue}\" → \"{$newValue}\"";
                }
            }
        } elseif (!empty($props) && is_array($props)) {
            // se ci sono altre properties utili (es. note), mostriamole come key: value
            foreach ($props as $k => $v) {
                // evita ripetere 'message' se già presente
                if ($k === 'message') continue;
                if (is_array($v)) {
                    $messageParts[] = "{$k}: " . json_encode($v, JSON_UNESCAPED_UNICODE);
                } else {
                    $messageParts[] = "{$k}: {$v}";
                }
            }
        } elseif (!empty($props) && is_string($props)) {
            $messageParts[] = $props;
        }

        // arricchisci con codice pratica/cliente
        $codice = $this->codice ?? ('#' . $this->id);
        $cliente = $this->cliente_nome ?? null;
        $messageParts[] = "Riferimento: {$codice}" . ($cliente ? " — {$cliente}" : '');

        // ultimo step: sovrascrivo properties con una struttura semplice che contiene il message
        $messageText = implode(' | ', $messageParts);
        $activity->properties = ['message' => $messageText];
    }


}
