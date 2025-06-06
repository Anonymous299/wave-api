<?php

namespace App\Observers;

use App\Models\Chat;
use App\Models\Swipe;

class SwipeObserver
{
    public function created(Swipe $swipe): void
    {
        if ($swipe->isMatch()) {

        }
    }
}
