<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Swipe extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    public function isMatch(): bool
    {
        return Swipe::query()
            ->where('swiper_id', $this->swipee->getKey())
            ->where('swipee_id', $this->swiper->getKey())
            ->where('direction', 'right')
            ->exists() && $this->direction === 'right';
    }

    public function swiper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swiper_id');
    }

    public function swipee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swipee_id');
    }
}
