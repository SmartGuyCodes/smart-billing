<?php

namespace SmartGuyCodes\Billing\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use SmartGuyCodes\Billing\Models\BillingSubscription;

class RenewalReminderNotification extends Notification
{
    public function __construct(
        public readonly BillingSubscription $subscription,
        public readonly int $daysUntilRenewal,
    ) {}

    public function via(object $notifiable): array
    {
        return config('billing.reminders.channels', ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $plan     = $this->subscription->plan;
        $renewsOn = $this->subscription->current_period_end->format('d M Y');
        $amount   = config('billing.currency_symbol') . ' ' . number_format($plan->price, 2);

        return (new MailMessage)
            ->subject("Your {$plan->name} plan renews in {$this->daysUntilRenewal} day(s)")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your **{$plan->name}** subscription renews on **{$renewsOn}**.")
            ->line("Amount: **{$amount}** will be charged automatically via M-Pesa.")
            ->line("Ensure your M-Pesa wallet has sufficient funds.")
            ->action('Manage Subscription', url('/billing'))
            ->line('Thank you for being a valued customer!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'renewal_reminder',
            'subscription_id'  => $this->subscription->id,
            'plan_name'        => $this->subscription->plan->name,
            'days_remaining'   => $this->daysUntilRenewal,
            'renewal_date'     => $this->subscription->current_period_end->toDateString(),
            'amount'           => $this->subscription->plan->price,
        ];
    }
}