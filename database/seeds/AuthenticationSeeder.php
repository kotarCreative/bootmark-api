<?php

use Illuminate\Database\Seeder;
use App\oAuthClient;

class AuthenticationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Empty out the old table */
        DB::table('oauth_clients')->delete();

        oauthClient::create(array(
            'id' => env('CLIENT_ID'),
            'secret' => env('CLIENT_SECRET'),
            'name' => 'api',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ));
    }
}
