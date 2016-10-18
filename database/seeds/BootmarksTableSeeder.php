<?php

use Illuminate\Database\Seeder;
use App\Bootmark;

class BootmarksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Empty out the old table */
        DB::table('bootmarks')->delete();

        Bootmark::create([
            'user_id' => 1,
            'media_id' => 1,
            'type' => 'photo',
            'location' => 'Hawrelak Park',
            'karma' => 30,
            'description' => 'Come check out heritage days!!',
            'lat' => 53.528390,
            'lng' => -113.549547,
            'remote' => 0,
            'discoverable' => 0
        ]);

        Bootmark::create([
            'user_id' => 2,
            'link_id' => 1,
            'type' => 'link',
            'location' => 'The Ledge',
            'karma' => 12,
            'description' => 'Such a fascinating history.',
            'lat' => 53.5335925,
            'lng' => -113.508658,
            'remote' => 1,
            'discoverable' => 0
        ]);

        Bootmark::create([
            'user_id' => 3,
            'media_id' => 2,
            'type' => 'media',
            'location' => 'Needle Vinyl Tavern',
            'karma' => 200,
            'description' => 'Show starts in ten minutes.',
            'lat' => 53.5409455,
            'lng' => -113.5040144,
            'remote' => 0,
            'discoverable' => 1
        ]);

        Bootmark::create([
            'user_id' => 4,
            'type' => 'text',
            'location' => 'Bootmark HQ',
            'karma' => -20,
            'description' => 'Always more work to be done but boy do we love it.',
            'lat' => 53.5831833,
            'lng' => -113.3820231,
            'remote'=> 0,
            'discoverable' => 0
        ]);
    }
}
