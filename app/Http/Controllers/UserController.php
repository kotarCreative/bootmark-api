<?php

namespace App\Http\Controllers;

use App\Jobs\MailReport;
use App\User, App\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;

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

        /* Checks for authorization parameters in the request */
        if ($request->has('grant_type') &&
            $request->has('client_id') &&
            $request->has('client_secret') &&
            $request->has('username') &&
            $request->has('password')
        ) {
            $authorizer = Authorizer::issueAccessToken();
        } else {
            $authorizer = ['message' => 'Missing parameters (see errors for needed parameters)',
                            'error'  => 'grant_type, client_id, client_secret, username, password'
            ];
        }

        return Response::json([
            'response'      =>  'Success',
            'user_id'       =>  $user->id,
            'message'       =>  'User successfully created',
            'authorization' =>  $authorizer
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
        $reporter_id = Authorizer::getResourceOwnerId();

        /* Retrieves the selected user */
        $user = User::where('id', $userID)->first();
        if ($user == null) {
            return response()->json([
                'response' => 'failure',
                'message' => 'User not found',
            ], 404);
        }

        $reporterInfo = User::find($reporter_id);

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
}
