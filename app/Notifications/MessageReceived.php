<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class MessageReceived extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: 'This is a test notification',
            body: 'This message is brought to you by Shadow Wizard Money Gang',
            image: 'https://placehold.co/400'
        )))->custom([
            'android' => [
                'notification' => [
                    'color' => '#0A0A0A',
                    'sound' => 'default',
                ],
                'fcm_options'  => [
                    'analytics_label' => 'analytics',
                ],
            ],
            'apns'    => [
                'payload'     => [
                    'aps' => [
                        'sound' => 'default'
                    ],
                ],
                'fcm_options' => [
                    'analytics_label' => 'analytics',
                ],
            ],
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
