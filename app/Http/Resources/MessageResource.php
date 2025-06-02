<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'body'      => $this->body,
            'sender_id' => $this->sender_id,
            'chat_id'   => $this->chat_id,
        ];
    }
}
