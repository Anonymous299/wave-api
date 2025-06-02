<?php

namespace App\Observers;

use App\Models\Chat;
use App\Models\Swipe;

class SwipeObserver
{
    public function created(Swipe $swipe): void
    {
        if ($swipe->isMatch()) {
            Chat::query()->create([
                'user_one_id' => $swipe->swiper->getKey(),
                'user_two_id' => $swipe->swipee->getKey(),
            ]);
        }
    }
}
