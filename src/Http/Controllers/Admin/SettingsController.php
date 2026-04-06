<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        $config = [
            'default_driver'   => config('billing.default_driver'),
            'currency'         => config('billing.currency'),
            'dunning'          => config('billing.dunning'),
            'reminders'        => config('billing.reminders'),
            'invoice'          => config('billing.invoice'),
            'drivers'          => [
                'mpesa' => [
                    'environment' => config('billing.drivers.mpesa.environment'),
                    'shortcode'   => config('billing.drivers.mpesa.shortcode'),
                    'type'        => config('billing.drivers.mpesa.type'),
                ],
            ],
        ];

        return view('billing::admin.settings.index', compact('config'));
    }

    public function update(Request $request)
    {
        // In a real app, write to .env or a settings table
        // For now, just redirect with a notice
        return back()->with('info', 'Update the billing.php config file or .env to apply changes.');
    }
}