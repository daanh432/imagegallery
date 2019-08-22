<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Album extends Model
{
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($album) { // before delete() method call this
            $album->Images()->detach();
        });
    }

    /**
     * @return BelongsToMany
     */
    public function Images()
    {
        return $this->belongsToMany('App\Image');
    }
}
