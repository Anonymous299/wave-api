<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id"         => $this->id,
            "gender"     => $this->gender,
            "age"        => $this->age,
            "job"        => $this->job,
            "company"    => $this->company,
            "education"  => $this->education,
            "about"      => $this->about,
            "user_id"    => $this->user_id,
        ];
    }
}
