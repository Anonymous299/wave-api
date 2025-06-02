<?php

namespace App\Notifications;

use App\Models\Swipe;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MatchMade extends Notification
{
    use Queueable;

    public function __construct(
        public Swipe $swipeA,
        public Swipe $swipeB,
        public User  $userA,
        public User  $userB
    )
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'swipe_a' => $this->swipeA,
            'swipe_b' => $this->swipeB,
            'user_a'  => $this->userA,
            'user_b'  => $this->userB,
        ];
    }
}
