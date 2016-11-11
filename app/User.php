<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'first_name',
        'last_name',
        'city',
        'prov_state',
        'country',
        'birthday',
        'bio',
        'radius',
        'notification_key'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Returns all the users that are following this user.
     *
     * @return array Returns the array of followers.
     */
    public function followers()
    {
	    return $this->hasMany('App\Follower');
    }

    /**
     * Gets all of the users discovered bootmarks
     *
     * @return array Returns an array of discovered_bootmark relations.
     */
    public function discoveredBootmarks()
    {
        return $this->belongsTo('App\DiscoveredBootmark');
    }
}
