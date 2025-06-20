<?php
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
         $emojiMap = [
    'intimacy' => '💜',
    'business' => '💼',
    'friendship' => '🤝'
    // add more as needed
];

$intention = $this->wavedBy->intention ?? '';
$emoji = $emojiMap[strtolower($intention)] ?? '';

        return (new FcmMessage(
            notification: new FcmNotification(
                title: "You've received a Wave! " . ($emoji ? " $emoji" : ''),
                body: "{$this->matchedWith->name} waved at you!",
                image: 'https://placehold.co/400'
            )
        ))
            ->data([
                'user_id' => $this->matchedWith->getKey(),
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
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'match_ios',
                    ],
                ],
            ]);
    }
}
