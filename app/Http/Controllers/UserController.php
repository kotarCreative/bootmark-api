<?php

namespace App\Http\Controllers;

use App\Jobs\MailReport;
use App\User, App\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Registers a new user.
     *
     * Checks the new users information and creates a new user
     * if the email and username havent been taken.
     *
     * @param request $request The post request.
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
        }

        Mail::send('emails.users.welcome', ['username' => $user->name], function ($message) {
            $message->to($user->email, $user->name)->subject('Welcome to Bootmark');
        });

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
     * @param request $request The request object containing all the inputs.
     *
     * @return Returns a success message or a failure message.
     */
    public function report($userID, Request $request)
    {
        $reporter_id = Auth::user()->id;

        /* Retrieves the selected user */
        $user = User::where('id', $userID)->first();
        if ($user == null) {
            return response()->json([
                'response' => 'failure',
                'message' => 'User not found',
            ], 404);
        }

        /* Creates a new report */
        $report = new Report;
        $report->reporter_id = $reporter_id;
        $report->user_id = $userID;
        $report->message = $request->input('message');
        $report->status = "Report received";

        $report->save();

        dispatch(new MailReport($report->id));

        return response()->json([
            'response' => 'success',
            'message' => 'User has been reported',
        ]);
    }

    public function auth(Request $request) {
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
