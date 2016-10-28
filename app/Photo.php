<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    /**
     * Using a year, month and day, it will ensure the correct directories exist on the disk. If they do not, it will
     * create the directories.
     *
     * The directory structure for the disk will look like this: Year -> Month -> Day
     *
     * @param string $diskName The disk name that will be used for storing the photo
     * @param string $year The year of the top parent directory
     * @param string $month The month of a given year parent directory
     * @param string $day The day of a given month parent directory
     *
     * @return void
     */
    public static function checkPhotoDirectories($diskName, $year, $month, $day)
    {
        /* Check for year directory */
        if (!Storage::disk($diskName)->exists($year)) {
            Storage::disk($diskName)->makeDirectory($year);
        }

        /* Check for 'year -> month' directory */
        if (!Storage::disk($diskName)->exists($year.'/'.$month)) {
            Storage::disk($diskName)->makeDirectory($year.'/'.$month);
        }

        /* Check for 'year -> month -> day' directory */
        if (!Storage::disk($diskName)->exists($year.'/'.$month.'/'.$day)) {
            Storage::disk($diskName)->makeDirectory($year.'/'.$month.'/'.$day);
        }
    }

    /**
     * Takes a file from a HTTP request and will store it onto the disk.
     *
     * @param string $diskName The disk name that will be used for storing the photo
     * @param file $file A photo file that has been uploaded during the HTTP request.
     *
     * @return string Returns the generated filename of the photo
     */
    public static function storePhoto($diskName, $file)
    {
        $filename = md5($file->getClientOriginalName() . microtime());

        $date = Carbon::now('America/Denver');

        $year = $date->year;
        $month = $date->month;
        $day = $date->day;

        Photo::checkPhotoDirectories($diskName, $year, $month, $day);
        Storage::disk($diskName)->put($year.'/'.$month.'/'.$day.'/'.$filename, File::get($file));

        return $year.'/'.$month.'/'.$day.'/'.$filename;
    }

    /**
     * Takes a disk name and file path to determine if it exists on the server.
     *
     * @param string $diskName The disk name that will be used for storing the photo
     * @param string $path Path of the file to check on the disk
     *
     * @return boolean Returns true if the path exists on the disk
     */
    public static function photoExists($diskName, $path)
    {
        return Storage::disk($diskName)->exists($path);
    }

    /**
     *Takes a disk name and file path and returns the associated file on the server
     *
     * @param string $diskName The disk name that will be used for storing the photo
     * @param string $path Path of the file to check on the disk
     *
     * @return file Returns the file of the path from the disk on the server
     */
    public static function getPhoto($diskName, $path)
    {
        return Storage::disk($diskName)->get($path);
    }
}
