<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $userDetail;

    /**
     * Create a new notification instance.
     */
    public function __construct($userDetail)
    {
        $this->userDetail = $userDetail;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Your Profile Has Been Updated')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('Your profile details have been updated successfully.')
                    ->line('Updated Full Name: ' . $this->userDetail->full_name)
                    ->line('Updated Phone Number: ' . ($this->userDetail->phone_number ?? 'N/A'))
                    ->action('View Profile', url('/profile'))
                    ->line('If you did not make this change, please contact support.');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Your profile was updated.',
            'full_name' => $this->userDetail->full_name,
            'phone_number' => $this->userDetail->phone_number,
            'updated_at' => $this->userDetail->updated_at->toDateTimeString(),
        ];
    }
}