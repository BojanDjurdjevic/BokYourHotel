<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSupplierAccountActive
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->supplier_deactivated_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Supplier account access has been deactivated. Please contact support.');
        }

        return $next($request);
    }
}
