<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    public function Images()
    {
        return $this->belongsToMany('App\Image');
    }
}
