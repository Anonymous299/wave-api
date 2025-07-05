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


class UserWaved extends Notification
{
    use Queueable;

    public function __construct(private readonly User $wavedBy)
    {
    }

    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $intention = $this->wavedBy->intention ?? '';
        $emoji = User::INTENTION_EMOJI_MAP[strtolower($intention)] ?? '';
        $firstImage = Arr::get($this->wavedBy->bio?->images, '0', '');

        return (new FcmMessage(
            notification: new FcmNotification(
                title: "You've received a Wave! " . ($emoji ? "$emoji" : ''),
                body: "{$this->wavedBy->name} waved at you!",
            )
        ))
            ->data([
                'user_id'         => $this->wavedBy->getKey(),
                'profile_picture' => $firstImage,
                'type'            => 'wave'
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
