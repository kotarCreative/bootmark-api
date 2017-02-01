<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    /**
     * Returns a Follower object if a user is following another user
     *
     * @param int $user_id The id of the user to check for following status.
     *
     * @return App\Follower Returns a follower object or null if not user is found
     */
    public function scopeIsFollowing($query, $user_id) {
        return $query->where('user_id', $user_id);
    }
}
