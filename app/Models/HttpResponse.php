<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HttpResponse extends Model
{
    public static function duplicateEntryResponse($key, $value)
    {
        return response()->json([
            'response' => 'Failure',
            'message' => "User already exists with '$key': $value"
        ], 422);
    }

    public static function notFoundResponse($message)
    {
        return response()->json([
            'response' => 'Failure',
            'message' => $message,
        ], 404);
    }

    public static function unauthorizedResponse()
    {
        return response()->json([
            'response' => 'Failure',
            'message' => 'Unauthorized',
        ], 401);
    }

    public static function missingFieldResponse($validatorErrors)
    {
        return response()->json([
            'response' => 'Failure',
            'message' => 'There are required fields missing',
            'errors' => $validatorErrors
        ], 422);
    }

    public static function generalResponse($response, $message, $httpCode)
    {
        return response()->json([
            'response' => $response,
            'message' => $message
        ], $httpCode);
    }

    public static function successDataResponse($message, $data)
    {
        return response()->json([
            'response' => 'Success',
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function successResponse($message)
    {
        return response()->json([
            'response' => 'Success',
            'message' => $message
        ]);
    }

    public static function authorizationResponse($token, $user)
    {
        return response()->json([
            'response' => 'Success',
            'token' => $token,
            'user' => $user
        ]);
    }

    public static function userCreationResponse($userID)
    {
        return response()->json([
            'response'      =>  'Success',
            'user_id'       =>  $userID,
            'message'       =>  'User successfully created',
            'authorization' =>  ''
        ]);
    }
}
