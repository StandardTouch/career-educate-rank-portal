<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalysisAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Allow access if fully authenticated and paid/admin
        if (Auth::check()) {
            return $next($request);
        }

        // Otherwise, check if they have verified OTP specifically for analysis
        if ($request->session()->get('analysis_verified')) {
            return $next($request);
        }

        // If neither, redirect to analysis login
        return redirect()->route('analysis.login')
            ->withErrors(['phone' => 'Please verify your mobile number to access analysis data.']);
    }
}
