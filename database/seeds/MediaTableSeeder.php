<?php

use Illuminate\Database\Seeder;
use App\Media;

class MediaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Empty out the old table */
        DB::table('media')->delete();

        Media::create([
            'media_type' => 'photo',
            'path' => '/2016/8/1/811785230eea55a785dc6d85c812d864',
            'mime_type' => 'image/jpg'
        ]);

        Media::create([
            'media_type' => 'youtube',
            'path' => 'https://youtube.com/embed/5_gL1Vo-RUo',
        ]);
    }
}
