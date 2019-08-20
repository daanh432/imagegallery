<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'url' => route('users.images.show', [$this->user_id, $this->url]),
            'thumbUrl' => route('users.images.show', [$this->user_id, $this->thumbUrl]),
            'timestamp' => Carbon::parse($this->date)->timestamp * 1000,
        ];
    }
}
