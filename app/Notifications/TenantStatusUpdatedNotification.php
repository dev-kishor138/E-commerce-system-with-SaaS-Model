<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Tenant;

class TenantStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $tenant;

    /**
     * Create a new notification instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
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
            ->subject('Tenant Status Updated')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The status of your tenant has been updated.')
            ->line('Tenant Name: ' . $this->tenant->name)
            ->line('New Status: ' . ucfirst($this->tenant->status))
            ->action('View Tenant', url('/tenants/' . $this->tenant->id))
            ->line('If you have any questions, please contact support.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'status' => $this->tenant->status,
            'message' => 'Tenant status updated to: ' . $this->tenant->status,
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}