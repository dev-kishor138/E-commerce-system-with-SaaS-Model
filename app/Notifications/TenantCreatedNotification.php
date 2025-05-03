<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Tenant;

class TenantCreatedNotification extends Notification implements ShouldQueue
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
            ->subject('New Tenant Created')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new tenant has been created successfully.')
            ->line('Tenant Name: ' . $this->tenant->name)
            ->line('Tenant ID: ' . $this->tenant->id)
            ->line('Domain: ' . ($this->tenant->domain ?? 'N/A'))
            ->action('View Tenant', url('/tenants/' . $this->tenant->id))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'message' => 'A new tenant has been created: ' . $this->tenant->name,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
