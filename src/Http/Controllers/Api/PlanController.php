<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SmartGuyCodes\Billing\Models\BillingPlan;
use SmartGuyCodes\Billing\Services\SubscriptionService;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = BillingPlan::active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'slug'        => $p->slug,
                'description' => $p->description,
                'price'       => $p->price,
                'currency'    => $p->currency,
                'interval'    => $p->interval,
                'trial_days'  => $p->trial_days,
                'features'    => $p->features,
                'is_popular'  => $p->is_popular,
                'formatted_price' => $p->formatted_price,
            ]);

        return response()->json(['plans' => $plans]);
    }
}
