<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bootmark extends Model
{
    /**
     * Retrieves all the bootmarks comments.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function comments()
    {
        return $this->hasMany('App\Comment');
    }
}
