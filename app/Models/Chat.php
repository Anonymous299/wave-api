<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function usersHaveBlockedEachOther(): bool
    {
        return $this->userOne->hasBlocked($this->userTwo)
            || $this->userTwo->hasBlocked($this->userOne);
    }

    public function hasParticipant(User $user): bool
    {
        return $this->user_one_id === $user->getKey()
            || $this->user_two_id === $user->getKey();
    }
}
