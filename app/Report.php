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

    public static function getEnums() {
        $type = DB::select(DB::raw("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'reports' AND column_name LIKE 'reason'"));
        preg_match("/^enum\\(\\'(.*)\\'\\)$/", $type[0]->COLUMN_TYPE, $matches);
        return explode("','", $matches[1]);
    }
}
