<?php

namespace SmartGuyCodes\Billing\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;
use function getenv;

class ReferenceGenerator
{
    public static function generate(?string $prefix = null): string
    {
        $prefix = strtoupper($prefix ?? getenv('BILLING_REFERENCE_PREFIX') ?: 'TXN');
        $length = getenv('BILLING_REFERENCE_LENGTH') ?: 12;
        $timestamp = Carbon::now()->format('ymdHis');
        $random    = strtoupper(Str::random(4));

        return "{$prefix}-{$timestamp}-{$random}";
    }

    public static function generateInvoice(?string $prefix = null): string
    {
        $prefix = strtoupper($prefix ?? getenv('BILLING_INVOICE_PREFIX') ?: 'INV');
        $year   = Carbon::now()->format('Y');
        $seq    = str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$seq}";
    }
}