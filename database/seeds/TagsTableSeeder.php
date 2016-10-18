<?php

use Illuminate\Database\Seeder;
use App\Tag;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Empty out the old table */
        DB::table('tags')->delete();

        Tag::create([
           'tag' => 'YEGshows'
        ]);
    }
}
