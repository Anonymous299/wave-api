<?php

namespace App\Notifications;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class TextReceived extends Notification
{
    use Queueable;

    public function __construct(private readonly User $textedBy, private readonly string $text, private readonly string $chat_id)
    {
    }

    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {

        $firstImage = $this->textedBy->bio?->images[0] ?? '';

        return (new FcmMessage(
            notification: new FcmNotification(
                title: "{$this->textedBy->name}",
                body: $this->text,
            )
        ))
            ->data([
                'chat_id'         => $this->chat_id,
                'other_user_id' => $this->textedBy->getKey(),
                'type'            => 'text',
                'profile_picture' => $firstImage
            ])
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#0A0A0A',
                        'sound' => 'default',
                    ],
                    'fcm_options'  => [
                        'analytics_label' => 'match_android',
                    ],
                ],
                'apns'    => [
                    'payload'     => [
                        'aps' => [
                            'sound'             => 'default',
                            'content-available' => 1
                        ],
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'match_ios',
                    ],
                ],
            ]);
    }
}
