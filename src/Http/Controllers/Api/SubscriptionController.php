<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SmartGuyCodes\Billing\Models\BillingPlan;
use SmartGuyCodes\Billing\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $service) {}

    public function current(Request $request): JsonResponse
    {
        $subscription = $request->user()->activeSubscription();

        if (!$subscription) {
            return response()->json(['subscription' => null], 200);
        }

        $subscription->load('plan');

        return response()->json([
            'subscription' => [
                'id'                   => $subscription->id,
                'status'               => $subscription->status,
                'plan'                 => [
                    'name'     => $subscription->plan->name,
                    'slug'     => $subscription->plan->slug,
                    'price'    => $subscription->plan->price,
                    'interval' => $subscription->plan->interval,
                    'features' => $subscription->plan->features,
                ],
                'trial_ends_at'        => $subscription->trial_ends_at,
                'current_period_start' => $subscription->current_period_start,
                'current_period_end'   => $subscription->current_period_end,
                'days_until_renewal'   => $subscription->daysUntilRenewal(),
                'on_trial'             => $subscription->isTrialing(),
            ],
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan'       => 'required|string',
            'trial_days' => 'nullable|integer|min:0',
        ]);

        $plan = BillingPlan::where('slug', $validated['plan'])
            ->orWhere('id', $validated['plan'])
            ->active()
            ->firstOrFail();

        $subscription = $this->service->subscribe(
            $request->user(),
            $plan,
            ['trial_days' => $validated['trial_days'] ?? null]
        );

        return response()->json([
            'message'      => 'Subscribed successfully.',
            'subscription' => $subscription->load('plan'),
        ], 201);
    }

    public function cancel(Request $request): JsonResponse
    {
        $subscription = $request->user()->activeSubscription();

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription.'], 404);
        }

        $immediately = $request->boolean('immediately', false);
        $this->service->cancel($subscription, $immediately);

        return response()->json([
            'message' => $immediately
                ? 'Subscription cancelled immediately.'
                : 'Subscription will end on ' . $subscription->current_period_end->format('d M Y') . '.',
        ]);
    }

    public function resume(Request $request): JsonResponse
    {
        $subscription = $request->user()
            ->billingSubscriptions()
            ->where('status', 'cancelled')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json(['message' => 'No cancelled subscription found.'], 404);
        }

        try {
            $this->service->resume($subscription);
            return response()->json(['message' => 'Subscription resumed.']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
