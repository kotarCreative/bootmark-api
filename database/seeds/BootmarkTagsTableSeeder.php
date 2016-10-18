<?php

use Illuminate\Database\Seeder;
use App\BootmarkTag;

class BootmarkTagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Empty out the old table */
        DB::table('bootmark_tags')->delete();

        BootmarkTag::create([
            'bootmark_id' => 3,
            'tag_id' => 1,
        ]);
    }
}
