<?php

use Illuminate\Database\Seeder;
use App\Link;

class LinksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Empty out the old table */
        DB::table('links')->delete();

        Link::create([
            'url' => 'https://www.tripadvisor.ca/Attraction_Review-g154914-d591306-Reviews-Alberta_Legislature_Building-Edmonton_Alberta.html',
            'title' => 'Alberta Legislature Building',
            'meta_description' => 'Alberta Legislature Building, Edmonton: See 410 reviews, articles, and 204 photos of Alberta Legislature Building, ranked No.4 on TripAdvisor among 161 attractions in Edmonton.',
            'image_path' => 'https://media-cdn.tripadvisor.com/media/photo-s/01/69/d4/eb/le-parlement-a-edmonton.jpg'
        ]);
    }
}
