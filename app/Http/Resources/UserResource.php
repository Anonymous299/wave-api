<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            "id"                => $this->id,
            "name"              => $this->name,
            "email"             => $this->email,
            "email_verified_at" => $this->email_verified_at,
            "location"          => $this->location,
            "created_at"        => $this->created_at,
            "updated_at"        => $this->updated_at,
            "fcm_token"         => $this->fcm_token,
            "distance"          => $this->distance / 1000,
            "bio"               => new BioResource($this->bio)
        ];
    }
}
