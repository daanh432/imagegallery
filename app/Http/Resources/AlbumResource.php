<?php

namespace App\Http\Resources;

use App\Images;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $images = $this->Images()->get();
        $randomImage = $images->count() != 0 ? $images->random() : null;
        if ($randomImage === null) {
            $randomImage = new Images();
            $randomImage->url = $randomImage->thumbUrl = asset('assets/img/placeholder.jpg');
            $randomImage->name = 'Placeholder';
            $randomImage->description = null;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'images' => ImageResource::collection($this->Images()->get()),
            'randomImage' => $randomImage
        ];
    }
}
