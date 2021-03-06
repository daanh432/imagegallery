<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Image extends Model
{
    protected $fillable = ['name', 'description', 'user_id', 'url', 'thumbUrl', 'date'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($image) { // before delete() method call this
            $image->Albums()->detach();
        });
    }

    /**
     * @return Album[]|Collection
     */
    public function AvailableAlbums()
    {
        return Album::all()->diff($this->Albums()->get());
    }

    /**
     * @return BelongsToMany
     */
    public function Albums()
    {
        return $this->belongsToMany('App\Album');
    }
}
