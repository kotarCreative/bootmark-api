<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

use App\Models\User;

class oAuthController extends Controller
{

    /**
     * Issues a token response that is generated with oAuth 2.0
     *
     * @return mixed Returns a json object with an access token
     */
    public function issueToken(Request $request)
    {
        $token = Authorizer::issueAccessToken();
        $user = User::where('email',$request->input('username'))->first();

        $response = response()->json([
            'response' => 'success',
            'token' => $token,
            'user' => $user
        ]);

        return $response;
    }

    /**
     * Verifies user credentials for oAuth 2.0
     *
     * @param string $email User email address
     * @param string $password User password
     *
     * @return bool Returns true on success
     */
    public function verify($username, $password)
    {
        $credentials = [
            'email'    => $username,
            'password' => $password,
        ];

        if (Auth::once($credentials)) {
            return Auth::user()->id;
        }

        return false;
    }
}
