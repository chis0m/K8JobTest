<?php

namespace App\Notifications;

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductExportNotification extends Notification
{
    use Queueable;

    protected $url;
    protected $zipUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($url, $zipUrl)
    {
        $this->url = $url;
        $this->zipUrl = $zipUrl;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Product Export')
            ->line('You can download your product export from the following links:')
            ->line('[Download Excel](' . $this->url . ')')
            ->line('[Download Zipped Excel](' . $this->zipUrl . ')')
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
