<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user_one'   => [
                'id'        => $this->userOne->id,
                'name'      => $this->userOne->name,
                'image_url' => $this->userOne->bio->image_url ?? null,
            ],
            'user_two'   => [
                'id'        => $this->userTwo->id,
                'name'      => $this->userTwo->name,
                'image_url' => $this->userTwo->bio->image_url ?? null,
            ],
            'created_at' => $this->created_at,
            'messages'   => $this->messages()->limit(5)->latest()->get(),
        ];
    }
}
