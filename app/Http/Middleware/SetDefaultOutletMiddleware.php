<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetDefaultOutletMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // If the user is Super Admin or Owner, they can access all outlets.
            // If they don't have a current outlet selected, we can leave it null (representing "All Outlets") or set it.
            // But for standard users/employees, they MUST have an outlet assigned.
            if (! $user->hasRole(['Super Admin', 'Owner'])) {
                if (! $user->current_outlet_id) {
                    $firstOutlet = $user->outlets()->first();
                    if ($firstOutlet) {
                        $user->update(['current_outlet_id' => $firstOutlet->id]);
                    }
                } else {
                    // Verify employee still has access to their current outlet
                    $hasAccess = $user->outlets()->where('outlets.id', $user->current_outlet_id)->exists();
                    if (! $hasAccess) {
                        $firstOutlet = $user->outlets()->first();
                        $user->update(['current_outlet_id' => $firstOutlet ? $firstOutlet->id : null]);
                    }
                }
            }
        }

        return $next($request);
    }
}
