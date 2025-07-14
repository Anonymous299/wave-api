<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = auth()->user();
        $hasMatched = false;
        $isBlocked = false;

        if ($user && $user->getKey() !== $this->id) {
            $hasMatched = $user->hasMatchedWith($this->id);

            $isBlocked = $user->hasBlocked($this->id);
        }

        return [
            "id"                => $this->id,
            "name"              => $this->name,
            "email"             => $this->email,
            "location"          => $this->location,
            "distance"          => $this->distance / 1000,
            "intention"         => $this->intention,
            "bio"               => new BioResource($this->bio),
            "has_matched"       => $hasMatched,
            "is_blocked"       => $isBlocked,
        ];
    }
}
