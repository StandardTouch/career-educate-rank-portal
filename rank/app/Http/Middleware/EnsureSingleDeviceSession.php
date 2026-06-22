<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleDeviceSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();

        if (! $user->current_session_id) {
            $user->forceFill(['current_session_id' => $sessionId])->save();

            return $next($request);
        }

        if (! hash_equals($user->current_session_id, $sessionId)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['phone' => 'Your account was logged in on another device. Please login again to continue.']);
        }

        return $next($request);
    }
}
