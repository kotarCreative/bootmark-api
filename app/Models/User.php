<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, CanResetPassword;

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
        'password', 'remember_token', 'notification_key'
    ];

    /**
     * Returns all the users that are following this user.
     *
     * @return array Returns an array of App\Models\Follower
     */
    public function followers()
    {
	    return $this->belongsToMany('App\Models\User', 'followers', 'user_id', 'follower_id');
    }

    /**
     * Gets all of the users discovered bootmarks
     *
     * @return array Returns an array of discovered_bootmark relations.
     */
    public function discoveredBootmarks()
    {
        return $this->belongsTo('App\Models\DiscoveredBootmark');
    }

    /**
     * Returns all the users that the current user is following
     *
     * @return array Returns an array of App\Models\Follower
     */
    public function following()
    {
        return $this->belongsToMany('App\Models\User', 'followers', 'follower_id', 'user_id');
    }
}
