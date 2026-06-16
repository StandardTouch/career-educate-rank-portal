<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePaidPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user->is_admin || $user->payment_status === 'paid') {
                return $next($request);
            }

            return redirect()->route('plans.index')
                ->with('warning', 'Please choose a package to access the results and predictor tool.');
        }

        return redirect()->route('login');
    }
}
