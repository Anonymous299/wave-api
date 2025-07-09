<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;
    use HasUuids;

    public const INTENTIONS = [
        'intimacy',
        'business',
        'friendship'
    ];

    public const INTENTION_EMOJI_MAP = [
        'intimacy'   => '💜',
        'business'   => '💼',
        'friendship' => '🤝'
        // add more as needed
    ];

    protected $with = ['bio'];

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'location' => Point::class,
        'id'       => 'string',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function swipes(): HasMany
    {
        return $this->hasMany(Swipe::class, 'swiper_id');
    }

    public function swipesReceived(): HasMany
    {
        return $this->hasMany(Swipe::class, 'swipee_id');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'user_one_id')
            ->orWhere('user_two_id', $this->getKey());
    }

    public function bio(): HasOne
    {
        return $this->hasOne(Bio::class);
    }

    public function matches(): HasMany
    {
        return $this->swipes()
            ->where('direction', 'right')
            ->whereHas('swipee', function ($query) {
                $query->whereHas('swipes', function ($q) {
                    $q->where('direction', 'right')
                        ->where('swipee_id', $this->getKey());
                });
            });
    }

    /**
     * Specifies the user's FCM token
     *
     * @return string|array|null
     */
    public function routeNotificationForFcm(): array|string|null
    {
        return $this->fcm_token ?: null;
    }

    public function blockedUsers(): HasMany
    {
        return $this->hasMany(BlockedUser::class);
    }

    public function block(User|string $blocked): void
    {
        $idToBlock = $blocked instanceof User ? $blocked->getKey() : $blocked;

        if ($this->getKey() === $idToBlock) {
            throw new \Exception("You can't block yourself.");
        }

        $this->blockedUsers()->create(['blocked_id' => $idToBlock]);
    }

    public function unblock(User|string $blocked): void
    {
        $idToUnblock = $blocked instanceof User ? $blocked->getKey() : $blocked;

        if ($this->getKey() === $idToUnblock) {
            throw new \Exception("You can't unblock yourself.");
        }

        $this->blockedUsers()->where('blocked_id', $idToUnblock)->delete();
    }

    public function hasBlocked(User|string $blocked): bool
    {
        $blockedId = $blocked instanceof User ? $blocked->getKey() : $blocked;

        return $this->blockedUsers()
            ->where('blocked_id', $blockedId)
            ->exists();
    }
}
