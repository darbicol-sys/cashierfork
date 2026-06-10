<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequireRole
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware([\App\Http\Middleware\RequireRole::class . ':admin'])
     */
    
public function handle(Request $request, Closure $next, $role)
{
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    $roleName = null;

    if (!empty($user->role_id)) {
        $roleName = DB::table('roles')
            ->where('id', $user->role_id)
            ->value('name');
    }

    $currentRole = strtolower(trim($roleName ?? $user->position ?? ''));

    // normalize
    $requiredRole = strtolower(trim($role));

    if ($currentRole !== $requiredRole) {

        // redirect based on actual role
        switch ($currentRole) {

            case 'maker':
                return redirect()->route('dashboard')
                    ->with('error', 'Unauthorized access.');

            case 'reviewer':
                return redirect()->route('reviewer')
                    ->with('error', 'Unauthorized access.');

            case 'approver':
                return redirect()->route('accountant.approval')
                    ->with('error', 'Unauthorized access.');

            case 'admin':
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Unauthorized access.');

            default:
                Auth::logout();
                return redirect()->route('login');
        }
    }

    return $next($request);
}


}
