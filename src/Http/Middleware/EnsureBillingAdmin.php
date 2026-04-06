<?php

namespace SmartGuyCodes\Billing\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureBillingAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Default: only super-admins. Override by publishing and editing.
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check for a `is_billing_admin` attribute or a `billing-admin` gate
        $isAdmin = method_exists($user, 'isBillingAdmin')
            ? $user->isBillingAdmin()
            : ($user->is_admin ?? false);

        if (!$isAdmin) {
            abort(403, 'Unauthorized: Billing admin access required.');
        }

        return $next($request);
    }
}