<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
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
