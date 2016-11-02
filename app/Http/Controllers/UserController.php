<?php

namespace App\Http\Controllers;

use App\Bootmark;
use App\Follower;
use App\HttpResponse;
use App\Jobs\MailNewUser;
use App\Jobs\MailReport;
use App\Photo;
use App\ProfilePicture;
use App\User, App\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'name'      =>  'required|unique:users',
            'email'     =>  'required|unique:users',
            'password'  =>  'required'
        );

        $messages = array(
            'email.required'    =>  "The 'username' is requried.",
            'email.unique'      =>  "The 'username' has already been taken."
        );

        $validator = Validator::make($request->all(), $rules, $messages);

        /* Process the request */
        if($validator->fails()) {
            return HttpResponse::missingFieldResponse($validator->errors());

        } else {
            $user = new User();

            $user->name = $request->input('name');
            $user->email = $request->input('username');
            $user->password = Hash::make($request->input('password'));
            $user->radius = 2000;

            $user->save();

            dispatch(new MailNewUser($user->id));
        }

        return HttpResponse::userCreationResponse($user->id);
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
            return HttpResponse::userNotFoundResponse('User not found');
        }

        /* Creates a new report */
        $report = new Report;
        $report->reporter_id = $reporter_id;
        $report->user_id = $user->id;
        $report->message = $request->input('message');
        $report->status = "Report received";

        $report->save();

        dispatch(new MailReport($report->id));

        return HttpResponse::generalResponse('Success', 'User has been reported', 200);
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

        return self::generalResponse('Success', $user, 200);
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
        /* Retrieves the logged in user */
        $user = User::find(Auth::user()->id);

        if ($userID != $user->id) {
            return HttpResponse::unauthorizedResponse();
        }

        $acceptedFields = [
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

        foreach ($acceptedFields as $field) {
            if ($request->input($field) != null) {

                $value = $request->input($field);

                if ($this->checkAlreadyExists('name', $value) && $value != $user->name) {
                    return HttpResponse::duplicateEntryResponse('name', $value);
                }

                if ($this->checkAlreadyExists('email', $value) && $value != $user->email) {
                    return HttpResponse::duplicateEntryResponse('email', $value);
                }

                $user->{$field} = $value;
            }
        }

        $user->save();

        /* Return success */
        return HttpResponse::successDataResponse('Information successfully updated', $user);
    }

    private static function checkAlreadyExists($key, $value)
    {
        return User::where($key, $value)->first() != null;
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
            return HttpResponse::notFoundResponse('User not found');
        }

        else {
            /* Gets the current profile photo */
            $profilePicture = ProfilePicture::where('user_id', $user->id)->where('current', 1)->first();

            /* If the photo does not exist */
            if ($profilePicture == null || !Photo::photoExists('profile_uploads', $profilePicture->path)) {
                return HttpResponse::notFoundResponse('User profile picture not found');
            }

            /* Photo does exist */
            $file = Photo::getPhoto('profile_uploads', $profilePicture->path);
            return response($file)->header('Content-Type', $profilePicture->mime_type);
        }
    }

    /**
     * Processes an http request and will store a profile photo on the server for the user.
     *
     * @param Illuminate\Http\Request $request The request object containing all the inputs.
     *
     * @return json Returns a success or failure message and the profile picture if successful.
     */
    public function savePhoto($userID, Request $request)
    {
        $file = $request->file('photo');
        $user = User::find(Auth::user()->id);


        /* Specify the rules */
        $rules = array(
            'current' => 'required',
            'photo' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        /* Process the request */
        if($validator->fails()) {
            return HttpResponse::missingFieldResponse($validator->errors());
        }

        if ($userID != $user->id) {
            return HttpResponse::unauthorizedResponse();
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
        return HttpResponse::successDataResponse('Profile pictures successfully added', $profile_picture);
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

        return HttpResponse::authorizationResponse(
            json_decode((string) $response->getBody(), true),
            User::where('email', $request->input('username'))->first()
        );
    }
}
