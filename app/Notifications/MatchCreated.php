<?php

namespace App\Notifications;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class MatchCreated extends Notification
{
    use Queueable;

    public function __construct(private readonly User $matchedWith, private readonly Chat $chat)
    {
    }

    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return (new FcmMessage(
            notification: new FcmNotification(
                title: 'It’s a Match!',
                body: "You and {$this->matchedWith->name} liked each other.",
                image: 'https://placehold.co/400' // Optional, replace with matched user's avatar if available
            )
        ))->custom([
            'chat_id'   => $this->chat->getKey(),
            'intention' => $this->matchedWith->intention,
        ]);
    }
}

