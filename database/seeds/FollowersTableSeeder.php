<?php

use Illuminate\Database\Seeder;
use App\Follower;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Empty out the old table */
        DB::table('followers')->delete();

        //User with ID 2 is following user with ID 1
        Follower::create(array(
            'user_id' => '1',
            'follower_id' => '2',
        ));

        //User with ID 3 is following user with ID 1
        Follower::create(array(
            'user_id' => '1',
            'follower_id' => '3',
        ));

        //User with ID 4 is following user with ID 1
        Follower::create(array(
            'user_id' => '1',
            'follower_id' => '4',
        ));

        //User with ID 3 is following user with ID 2
        Follower::create(array(
            'user_id' => '2',
            'follower_id' => '3',
        ));

        //User with ID 1 is following user with ID 2
        Follower::create(array(
            'user_id' => '2',
            'follower_id' => '1',
        ));
    }
}
