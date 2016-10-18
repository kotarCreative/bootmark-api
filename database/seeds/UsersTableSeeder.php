<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    /* Empty out the old table */
	DB::table('users')->delete();

	User::create(array(
		'name' => 'DaBuss',
		'first_name' => 'David',
		'last_name' => 'Buss',
		'email' => 'd_buss@hotmail.com',
		'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'gender' => 'Male',
		'country' => 'Canada',
		'prov_state' => 'Alberta',
		'city' => 'Edmonton',
		'birthday' => \Carbon\Carbon::createFromDate(1993,07,12)->toDateTimeString(),
		'bio' => 'Loves to eat doughnuts and build cool apps. Usually at the same time',
		'radius' => 100000,
	));


	User::create(array(
		'name' => 'M-Dawg',
		'first_name' => 'Mike',
		'last_name' => 'Buss',
		'email' => 'mikebuss@live.ca',
		'password' => \Illuminate\Support\Facades\Hash::make('password'),
		'gender' => 'Male',
		'country' => 'Canada',
		'prov_state' => 'Alberta',
		'city' => 'Edmonton',
		'birthday' => \Carbon\Carbon::createFromDate(1989,02,21)->toDateTimeString(),
		'bio' => 'Dreams with his head in the clouds and a beer in hand',
		'radius' => 100000,
	));


	User::create(array(
		'name' => 'Sir Watson',
		'first_name' => 'Isaac',
		'last_name' => 'Watson',
		'email' => 'isaac.watson@shaw.ca',
		'password' => \Illuminate\Support\Facades\Hash::make('password'),
		'gender' => 'Male',
		'country' => 'Canada',
		'prov_state' => 'Alberta',
		'city' => 'Edmonton',
		'birthday' => \Carbon\Carbon::createFromDate(1987,04,15)->toDateTimeString(),
		'bio' => 'Making money is a passion and Im really good at it.',
		'radius' => 100000,
	));


	User::create(array(
		'name' => 'Scotty',
		'first_name' => 'Scott',
		'last_name' => 'Varga',
		'email' => 'scottalexandervarga@gmail.com',
		'password' => \Illuminate\Support\Facades\Hash::make('password'),
		'gender' => 'Male',
		'country' => 'Canada',
		'prov_state' => 'Alberta',
		'city' => 'Edmonton',
		'birthday' => \Carbon\Carbon::createFromDate(1989,10,02)->toDateTimeString(),
		'bio' => 'Can be found riding my bike down Sask. Drive looking for the latest IPA.',
		'radius' => 100000,
	));

	User::create(array(
	  'name' => 'codymoorhouse',
	  'first_name' => 'Cody',
	  'last_name' => 'Moorhouse',
	  'email' => 'cody.moorhouse@icloud.com',
	  'password' => \Illuminate\Support\Facades\Hash::make('password'),
	  'gender' => 'Male',
	  'country' => 'Canada',
	  'prov_state' => 'Alberta',
	  'city' => 'Edmonton',
	  'birthday' => \Carbon\Carbon::createFromDate(1989,10,02)->toDateTimeString(),
	  'bio' => 'Can also be found riding my bike down Sask. Drive looking for the latest IPA.',
	  'radius' => 100000,
	));

    }
}
