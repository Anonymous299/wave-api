<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'body'       => $this->body,
            'sender'     => User::query()
                ->findOrFail($this->sender_id)
                ->toResource(),
            'chat_id'    => $this->chat_id,
            'created_at' => $this->created_at,
        ];
    }
}
