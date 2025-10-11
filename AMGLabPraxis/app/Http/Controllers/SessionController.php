<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $currentSessionId = session()->getId();

        // prendi le sessioni relative all'utente (driver database)
        $rows = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->get();

        // mappa i risultati in oggetti più comodi per la view
        $sessions = $rows->map(function ($s) use ($currentSessionId) {
            // last_activity è generalmente un timestamp intero (seconds)
            $lastActivity = null;
            if (!empty($s->last_activity) && is_numeric($s->last_activity)) {
                $lastActivity = Carbon::createFromTimestamp($s->last_activity)
                    ->setTimezone(config('app.timezone') ?? config('app.timezone', 'UTC'));
            }

            // prova a estrarre info user-agent (semplice): browser + platform
            $ua = $s->user_agent ?? '';
            $agentLabel = $ua;
            try {
                // evita dipendenze esterne: estrazione semplice
                if (stripos($ua, 'Firefox') !== false) {
                    $agentLabel = 'Firefox';
                } elseif (stripos($ua, 'Chrome') !== false && stripos($ua, 'Edg') === false && stripos($ua, 'OPR') === false) {
                    $agentLabel = 'Chrome';
                } elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
                    $agentLabel = 'Safari';
                } elseif (stripos($ua, 'Edg') !== false) {
                    $agentLabel = 'Edge';
                } elseif (stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false) {
                    $agentLabel = 'Opera';
                } elseif (stripos($ua, 'Android') !== false) {
                    $agentLabel = 'Android';
                } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
                    $agentLabel = 'iOS';
                }
            } catch (\Exception $e) {
                $agentLabel = $ua;
            }

            return (object) [
                'id' => $s->id,
                'is_current' => ($s->id === $currentSessionId),
                'ip_address' => $s->ip_address ?? '-',
                'user_agent_raw' => $ua,
                'user_agent' => $agentLabel,
                'last_activity' => $lastActivity,
            ];
        });

        return view('admin.sessions.index', [
            'sessions' => $sessions
        ]);
    }

    public function destroy(Request $request, $sessionId)
    {
        $userId = auth()->id();

        // non permettere di eliminare la sessione corrente via questa rotta
        if ($sessionId === session()->getId()) {
            return redirect()->back()->with('error','Impossibile terminare la sessione corrente da qui.');
        }

        DB::table('sessions')->where('id', $sessionId)->where('user_id', $userId)->delete();

        return redirect()->back()->with('success','Sessione terminata con successo.');
    }

    public function destroyOthers(Request $request)
    {
        $userId = auth()->id();
        $current = session()->getId();

        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $current)
            ->delete();

        return redirect()->back()->with('success','Tutte le altre sessioni sono state terminate.');
    }
}
