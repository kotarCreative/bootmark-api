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
use DB;

class UserController extends Controller
{
    /**
     * Registers a new user.
     *
     * Checks the new users information and creates a new user
     * if the email and username havent been taken.
     *
     * @param Request $request The post request.
     *
     * @return HttpResponse mixed
     */
    public function store(Request $request)
    {
        /* Specify the rules */
        $rules = array(
            'name'      =>  'required|unique:users',
            'username'     =>  'required|unique:users,email',
            'password'  =>  'required'
        );

        $messages = array(
            'username.required'    =>  "The email is required.",
            'username.unique'      =>  "The email has already been taken."
        );

        $validator = Validator::make($request->all(), $rules, $messages);

        /* Process the request */
        if($validator->fails()) {
            return HttpResponse::missingFieldResponse($validator->errors());

        } else {
            $user = new User();

            $user->name = $request->input('name');
            $user->email = strtolower($request->input('username'));
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
     * @param int $user The user that is being reported.
     * @param Request $request The request object containing all the inputs.
     *
     * @return HttpResponse Return a success message or a failure message.
     */
    public function report($user, Request $request)
    {
        $reporter_id = Auth::user()->id;

        $enums = Report::getEnums();

        /* Retrieves the selected user */
        $user = User::where('id', $user)->first();
        if ($user == null) {
            return HttpResponse::notFoundResponse('User not found');
        }

        if (!in_array($request->input('reason'), $enums)) {
            return HttpResponse::generalResponse('Failure', "Reason must be either 'spam' or 'inappropriate'", 422);
        }

        /* Creates a new report */
        $report = new Report;
        $report->reporter_id = $reporter_id;
        $report->user_id = $user->id;
        $report->message = $request->input('message');
        $report->status = "Report received";
        $report->reason = $request->input('reason');

        $report->save();

        dispatch(new MailReport($report->id));

        return HttpResponse::successResponse('User has been reported');
    }

    /**
     * Returns the specified user information with the total bootmark count, followers count and following count.
     *
     * @param Integer $user users id that is passed during the api call e.g. .../users/{userID}
     * 
     * @return HttpResponse Return a general message response
     */
    public function show($user)
    {
        $user = User::find($user);

        $bootmark_count = Bootmark::where("user_id", $user->id)->count();
        $follower_count = Follower::where("user_id", $user->id)->count();
        $karma_count = Bootmark::where("user_id", $user->id)->sum('karma');
        $following = Auth::user()->following()->where('user_id', $user->id)->get();
        if(!$following->isEmpty()) {
            $user->following = true;
        } else {
            $user->following = false;
        }

        $user->bootmarks = $bootmark_count;
        $user->followers = $follower_count;
        $user->karma = $karma_count;

        return HttpResponse::generalResponse('Success', $user, 200);
    }

    /**
     * Soft deletes a user and all its activity from the database.
     *
     * @param int $user The user id to be deleted.
     *
     * @return HttpResponse mixed
     */
    public function destroy($user)
    {

    }

    /**
     * Updates an existing user.
     *
     * @param int $user The user id to be updated.
     * @param Request $request The Request object with all the inputs.
     *
     * @return HttpResponse mixed
     */
    public function update($user, Request $request)
    {
        $id = $user;
        /* Retrieves the logged in user */
        $user = User::find(Auth::user()->id);

        if ($id != $user->id) {
            return HttpResponse::unauthorizedResponse();
        }

        $accepted_fields = [
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

        foreach ($accepted_fields as $field) {
            if ($request->input($field) != null) {

                $value = $request->input($field);

                if ($this->checkAlreadyExists('name', $value) && $value != $user->name) {
                    return HttpResponse::duplicateEntryResponse('name', $value);
                }

                if ($this->checkAlreadyExists('email', $value) && $value != $user->email) {
                    return HttpResponse::duplicateEntryResponse('email', $value);
                }

                if ($field == 'password') {
                    $value = Hash::make($value);
                }

                $user->{$field} = $value;
            }
        }

        $user->save();

        /* Return success */
        return HttpResponse::successDataResponse('Information successfully updated', $user);
    }

    /**
     * Checks the database to see if there exists a User who has the specified key:value pairing
     *
     * @param string $key Column in the users table
     * @param string $value Value associated with the column
     *
     * @return bool Returns true if there is already a user with the associate key:value pairing
     */
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
            $profile_picture = ProfilePicture::where('user_id', $user->id)->where('current', 1)->first();

            /* If the photo does not exist */
            if ($profile_picture == null || !Photo::photoExists('profile_uploads', $profile_picture->path)) {
                return HttpResponse::notFoundResponse('User profile picture not found');
            }

            /* Photo does exist */
            $file = Photo::getPhoto('profile_uploads', $profile_picture->path);
            return response($file)->header('Content-Type', $profile_picture->mime_type);
        }
    }

    /**
     * Processes an http request and will store a profile photo on the server for the user.
     *
     * @param Request $request The request object containing all the inputs.
     *
     * @return HttpResponse mixed
     */
    public function savePhoto($user, Request $request)
    {
        $file = $request->file('photo');
        $id = $user;
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

        if ($id != $user->id) {
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
     * @param Request $request The request object containing all the inputs.
     *
     * @return HttpResponse Returns a response with a token and user info
     */
    public function auth(Request $request)
    {
        $http = new Client();

        $response = $http->post(env('AUTH_ROUTE'), [
            'form_params' => [
                'grant_type' => $request->input('grant_type'),
                'client_id' => $request->input('client_id'),
                'client_secret' => $request->input('client_secret'),
                'username' => strtolower($request->input('username')),
                'password' => $request->input('password'),
                'scope' => '',
            ],
        ]);

        return HttpResponse::authorizationResponse(
            json_decode((string) $response->getBody(), true),
            User::where('email', $request->input('username'))->first()
        );
    }

    /**
     * Follows or unfollows a user depending on the current following status.
     *
     * @param int user The id of the user to change following status for.
     *
     * @return Response Returns a response with the new follower status.
     */
    public function follow($user)
    {
        $user = User::find($user);

        if($user) {
            $following = Follower::where('user_id', $user->id)->where('follower_id', Auth::user()->id)->first();
            if($following != null) {
                $following->delete();
                return response()->json([
                'message' => 'User was successfully unfollowed',
                'following' => 'False'
            ]);
            } else {
                $follower = new Follower;
                $follower->user_id = $user->id;
                $follower->follower_id = Auth::user()->id;
                $follower->save();
                return response()->json([
                    'message' => 'User was successfully followed',
                    'following' => 'True'
                ]);
            }
        }
    }

    /**
     * Retrieves all the bootmarks for a user sorted by time.
     *
     * @param int $user The id of the user to get bootmarks for.
     * @param Request $request The request containing all the required fields
     *
     * @return Response Returns a response containing all the bootmarks for the user.
     */
    public function bootmarks($user, Request $request)
    {
        $this->validate($request,[
            'lat' => 'required',
            'lng' => 'required',
        ]);

        $user = User::find($user);

        if($user) {
            /* Create the join for media and links meta data */
            $bootmarks = DB::table('bootmarks')
                ->leftJoin('media','bootmarks.media_id','=','media.id')
                ->leftJoin('links','bootmarks.link_id','=','links.id');

            $bootmarks = $bootmarks
                ->leftJoin(DB::raw("(select * from votes where votes.user_id = $user->id) v"),'bootmarks.id', '=', 'v.bootmark_id')
                ->distinct();

            $distance_select = "earth_distance(ll_to_earth($lat,$lng), ll_to_earth(lat, lng)) as distance_from_current";
            $bootmarks->orderBy('bootmarks.created_at','desc');

            /* Select required data and paginate results */
            $bootmarks = $bootmarks->select(
                DB::raw($distance_select),
                'users.name',
                'bootmarks.*',
                'links.url',
                'links.title',
                'links.meta_description',
                'links.image_path',
                'media.media_type',
                'media.path',
                'media.mime_type',
                'v.vote')->simplePaginate(20);

            return response()->json([
                'response' => 'success',
                'bootmarks' => $bootmarks
            ]);
        } else {
            return response()->json([
                'response' => 'failure',
                'message' => 'The user you have requested does not exist'
            ], 404);
        }
    }
}
