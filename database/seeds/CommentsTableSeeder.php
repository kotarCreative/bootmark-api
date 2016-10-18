<?php

use Illuminate\Database\Seeder;
use App\Comment;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Empty out the old table */
        DB::table('comments')->delete();

        Comment::create([
            'user_id' => 1,
            'bootmark_id' => 2,
            'comment' => 'So intriguing. Thanks for posting this article.'
        ]);
    }
}
