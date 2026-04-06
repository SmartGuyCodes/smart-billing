<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SmartGuyCodes\Billing\Models\BillingPlan;

class PlanController extends Controller
{
    public function add()
    {
        return view('billing::admin.plans.add');
    }

    public function index()
    {
        $plans = BillingPlan::withCount(['subscriptions' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->paginate(config('billing.admin.per_page', 25));

        return view('billing::admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('billing::admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'slug'       => 'required|string|unique:' . config('billing.tables.plans') . ',slug',
            'description'=> 'nullable|string',
            'price'      => 'required|numeric|min:0',
            'interval'   => 'required|in:daily,weekly,monthly,yearly',
            'trial_days' => 'nullable|integer|min:0',
            'features'   => 'nullable|array',
            'is_active'  => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['currency']   = config('billing.currency', 'KES');
        $validated['features']   = $request->features ?? [];
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['is_popular'] = $request->boolean('is_popular', false);

        BillingPlan::create($validated);

        return redirect()->route('billing.admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(BillingPlan $plan)
    {
        return view('billing::admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, BillingPlan $plan)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'description'=> 'nullable|string',
            'price'      => 'required|numeric|min:0',
            'interval'   => 'required|in:daily,weekly,monthly,yearly',
            'trial_days' => 'nullable|integer|min:0',
            'features'   => 'nullable|array',
            'is_active'  => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['features']   = $request->features ?? [];
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['is_popular'] = $request->boolean('is_popular', false);

        $plan->update($validated);

        return redirect()->route('billing.admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(BillingPlan $plan)
    {
        if ($plan->subscriptions()->active()->exists()) {
            return back()->with('error', 'Cannot delete a plan with active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('billing.admin.plans.index')
            ->with('success', 'Plan deleted.');
    }

    public function toggle(BillingPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        return back()->with('success', 'Plan status updated.');
    }
}