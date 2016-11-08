<?php

namespace App\Http\Controllers;

use App\Bootmark;
use App\Follower;
use App\Jobs\MailNewUser;
use App\Jobs\MailReport;
use App\Photo;
use App\ProfilePicture;
use App\User, App\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class UserController extends Controller
{
    /**
     * Registers a new user.
     *
     * Checks the new users information and creates a new user
     * if the email and username havent been taken.
     *
     * @param Illuminate\Http\Request $request The post request.
     *
     * @return json A Json response.
     */
    public function store(Request $request)
    {
        /* Specify the rules */
        $rules = array(
            'name' => 'required|unique:users',
            'password' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        /* Process the request */
        if($validator->fails()) {
            return Response::json([
                'response' => 'Failure',
                'message' => 'There are required fields missing',
                'errors' => $validator->errors()
            ], 422);

        } else {
            $user = new User();

            $user->name = $request->input('name');
            $user->email = $request->input('username');
            $user->password = Hash::make($request->input('password'));
            $user->radius = 2000;

            $user->save();

            dispatch(new MailNewUser($user->id));
        }

        return Response::json([
            'response'      =>  'Success',
            'user_id'       =>  $user->id,
            'message'       =>  'User successfully created',
            'authorization' =>  ''
        ]);
    }


    /**
     * Generates a report for the specified user
     *
     * @param int $userID The user that is being reported.
     * @param Illuminate\Http\Request $request The request object containing all the inputs.
     *
     * @return Returns a success message or a failure message.
     */
    public function report($user, Request $request)
    {
        $reporter_id = Auth::user()->id;

        /* Retrieves the selected user */
        $user = User::where('id', $user)->first();
        if ($user == null) {
            return response()->json([
                'response' => 'failure',
                'message' => 'User not found',
            ], 404);
        }

        /* Creates a new report */
        $report = new Report;
        $report->reporter_id = $reporter_id;
        $report->user_id = $user->id;
        $report->message = $request->input('message');
        $report->status = "Report received";

        $report->save();

        dispatch(new MailReport($report->id));

        return response()->json([
            'response' => 'success',
            'message' => 'User has been reported',
        ]);
    }

    /**
     * Returns the specified user information with the total bootmark count, followers count and following count.
     *
     * @param Integer $userID users id that is passed during the api call e.g. .../users/{userID} 
     * 
     * @return mixed Returns a json array of the user info.
     */
    public function show($user)
    {
        $user = User::find($user);

        $bootmark_count = Bootmark::where("user_id", $user->id)->count();
        $follower_count = Follower::where("user_id", $user->id)->count();
        $karma_count = Bootmark::where("user_id", $user->id)->sum('karma');

        $user->bootmarks = $bootmark_count;
        $user->followers = $follower_count;
        $user->karma = $karma_count;

        return response()->json([
            'response' => 'success',
            'user' => $user
        ]);
    }

    /**
     * Soft deletes a user and all its activity from the database.
     *
     * @param int $userID The user id to be deleted.
     *
     * @return json Returns a success or failure message.
     */
    public function destroy($user)
    {

    }

    /**
     * Updates an existing user.
     *
     * @param int $userID The user id to be updated.
     * @param Illuminate\Http\Request $request The Request object with all the inputs.
     *
     * @return json Returns a success or failure message.
     */
    public function update($user, Request $request)
    {

    }

    /**
     * Retrieves a users profile photo from the server.
     *
     * @param int $userID The user id to be updated.
     *
     * @return \Illuminate\Http\JsonResponse showing error or the photo being retrieved.
     */
    public function getPhoto($user)
    {
        /* Retrieves the selected user */
        $user = User::where('id', $user)->first();

        /* Checks if the user exists  */
        if ($user == null) {
            return response()->json([
                'response' => 'Failure',
                'message' => 'User not found'
            ] , 404);
        }

        else {
            /* Gets the current profile photo */
            $profilePicture = ProfilePicture::where('user_id', $user->id)->where('current', 1)->first();

            /* If the photo exists */
            if (Photo::photoExists('profile_uploads', $profilePicture->path)) {
                $file = Photo::getPhoto('profile_uploads', $profilePicture->path);
                return response($file)->header('Content-Type', $profilePicture->mime_type);

            /* If the photo does not exist */
            } else {
                return response()->json([
                    'response' => 'Failure',
                    'message' => 'User profile picture not found'
                ], 404);
            }
        }
    }

    /**
     * Processes an http request and will store a profile photo on the server for the user.
     *
     * @param Illuminate\Http\Request $request The request object containing all the inputs.
     *
     * @return json Returns a success or failure message and the profile picture if successful.
     */
    public function savePhoto(Request $request)
    {
        $file = $request->file('photo');

        /* Check if file was given */
        if ($file == null) {
            return response()->json([
                'response' => 'Failure',
                'message' => "missing or invalid 'photo' parameter with attached file"
            ], 422);
        }

        /* Check for current parameter */
        if ($request->input('current') == null) {
            return response()->json([
                'response' => 'Failure',
                'message' => "missing or invalid 'current' parameter"
            ], 422);
        }

        /* Update old profile picture current status */
        if ($request->input('current') == 1) {
            $current_picture = ProfilePicture::where('user_id', Auth::user()->id)->where('current', 1)->first();
	    if ($current_picture != null) {
                $current_picture->current = 0;
                $current_picture->save();
	    }
        }

        $profile_picture = new ProfilePicture();

        $profile_picture->user_id = Auth::user()->id;
        $profile_picture->path = Photo::storePhoto('profile_uploads', $file);
        $profile_picture->mime_type = $file->getClientMimeType();
        $profile_picture->current = $request->input('current');

        $profile_picture->save();

        /* Return success */
        return response()->json([
            'response' => 'Success',
            'message' => 'Profile pictures successfully added',
            'data' => $profile_picture
        ]);
    }

    /**
     * Authorizes a user using passport oauth and will return a token.
     *
     * @param Illuminate\Http\Request $request The request object containing all the inputs.
     *
     * @return mixed Returns a json response with a token and user info
     */
    public function auth(Request $request)
    {
        $http = new Client();

        $response = $http->post('http://dev.bootmark.ca/oauth/token', [
            'form_params' => [
                'grant_type' => $request->input('grant_type'),
                'client_id' => $request->input('client_id'),
                'client_secret' => $request->input('client_secret'),
                'username' => $request->input('username'),
                'password' => $request->input('password'),
                'scope' => '',
            ],
        ]);

        return response()->json([
            'response' => 'success',
            'token' => json_decode((string) $response->getBody(), true),
            'user' => User::where('email', $request->input('username'))->first(),
        ]);
    }
}
