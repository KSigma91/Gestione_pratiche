<?php
namespace App\Listeners;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User;

class UpdateLastLogin
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Login $event)
    {
        // Ensure $user is an Eloquent model
        $user = User::find($event->user->id);

        try {
            // aggiorna users
            if ($user) {
                $user->last_login_at = Carbon::now();
                $user->last_login_ip  = $this->request->ip();
                $user->save();
                Log::info("UpdateLastLogin: updated users for user_id={$user->id}");
            } else {
                Log::warning("UpdateLastLogin: User model not found for user_id={$event->user->id}");
            }
        } catch (\Exception $e) {
            Log::error("UpdateLastLogin users update failed: " . $e->getMessage());
        }

        // se usi driver database, aggiorna/crea la row della sessione
        try {
            if (config('session.driver') === 'database') {
                $sessionId = session()->getId();
                if ($sessionId) {
                    DB::table('sessions')->updateOrInsert(
                        ['id' => $sessionId],
                        [
                            'user_id'      => $user->id,
                            'ip_address'   => $this->request->ip(),
                            'user_agent'   => substr($this->request->header('User-Agent') ?? 'unknown', 0, 500),
                            'last_activity'=> time(),
                        ]
                    );
                    Log::info("UpdateLastLogin: sessions updated for session_id={$sessionId}");
                } else {
                    Log::warning("UpdateLastLogin: session()->getId() returned null");
                }
            }
        } catch (\Exception $e) {
            Log::error("UpdateLastLogin sessions update failed: " . $e->getMessage());
        }
    }
}
