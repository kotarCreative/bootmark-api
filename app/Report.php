<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message', 'reason'
    ];

    /**
     * Grabs the enums from the reports table.
     *
     * @return array Returns an array of the enums from the reports table
     */
    public static function getEnums() {
        return ['spam', 'inappropriate'];
    }
}
