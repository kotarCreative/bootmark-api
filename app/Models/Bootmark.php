<?php

namespace App\Models;

use DB;

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
        return $this->hasMany('App\Models\Comment');
    }

    /**
     * Sets the coordinates to a geography object
     *
     * @param array $coordinates (contains lat and lng)
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function setCoordinatesAttribute($coordinates) {
        $lat = $coordinates['lat'];
        $lng = $coordinates['lng'];

        /* Converts to a geometry object */
        $this->attributes['coordinates'] = $this->getGeogFromCoords($lng, $lat);
    }

    /**
     * Converts coordinates into a geography object
     *
     * @param double $lng
     * @param double $lat
     *
     * @return {geog}
     */
    public function getGeogFromCoords($lng, $lat)
    {
        return DB::raw("ST_GeogFromText('POINT(' || $lng || ' ' || $lat || ')')");
    }
}
