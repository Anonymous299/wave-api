<?php

namespace App\Notifications;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
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
        $intention = $this->matchedWith->intention ?? '';
        $emoji = User::INTENTION_EMOJI_MAP[strtolower($intention)] ?? '';
        $firstImage = Arr::get($this->matchedWith->bio?->images, '0', '');

        return (new FcmMessage(
            notification: new FcmNotification(
                title: 'Successful connection' . ($emoji ? "$emoji" : ''),
                body: "You and {$this->matchedWith->name} have connected.",
            ),
        ))
            ->data([
                'chat_id'   => $this->chat->id,
                'other_user_id' => $this->matchedWith->getKey(),
                'intention' => $this->matchedWith->intention ?? '',
                'profile_picture' => $firstImage,
                'type'      => 'match'
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
                            'sound' => 'default',
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

