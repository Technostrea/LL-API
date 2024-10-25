<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_name' => $this->agency_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'agency_license' => $this->agency_license,
            'address' => $this->address,
            'logo' => $this->logo ? url($this->logo) : null,
            'description' => $this->description,
            'website' => $this->website,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
