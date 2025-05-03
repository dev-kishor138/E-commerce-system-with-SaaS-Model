<?php

namespace App\Notifications;

use App\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $plan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Plan $plan)
    {
        $this->plan = $plan;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Plan Updated: ' . $this->plan->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The plan "' . $this->plan->name . '" has been updated.')
            ->line('New Price: ' . $this->plan->getFormattedPrice())
            ->line('Billing Cycle: ' . ucfirst($this->plan->billing_cycle))
            ->line('Status: ' . ucfirst($this->plan->status))
            ->line('Features: ' . (empty($this->plan->features) ? 'No features updated' : implode(', ', $this->plan->features)))
            ->action('View Plan Details', url('/plans/' . $this->plan->id))
            ->line('If you have any questions, please contact our support team.')
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'plan_id' => $this->plan->id,
            'plan_name' => $this->plan->name,
            'message' => 'Plan updated: ' . $this->plan->name,
            'price' => $this->plan->getFormattedPrice(),
            'billing_cycle' => $this->plan->billing_cycle,
            'status' => $this->plan->status,
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}