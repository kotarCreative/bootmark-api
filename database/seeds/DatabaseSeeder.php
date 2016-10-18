<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(AuthenticationSeeder::class);
        $this->call(MediaTableSeeder::class);
        $this->call(LinksTableSeeder::class);
        $this->call(BootmarksTableSeeder::class);
        $this->call(CommentsTableSeeder::class);
        $this->call(TagsTableSeeder::class);
        $this->call(BootmarkTagsTableSeeder::class);
        $this->call(FollowersTableSeeder::class);
    }
}
