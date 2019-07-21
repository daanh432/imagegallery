<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Albums extends Model
{
    public function Images()
    {
        return $this->hasMany('App\AlbumImages', 'album_id', 'id');
    }
}
