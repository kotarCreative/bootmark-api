<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function() {
    Route::post('authenticate', 'UserController@auth');
    Route::post('register', 'UserController@store');
});

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function () {
    /* Bootmark Requests */
    Route::get('bootmarks/{bootmarkID}/photo', 'BootmarkController@getPhoto');
    Route::post('bootmarks/{bootmarks}/vote','BootmarkController@vote');
    Route::post('bootmarks/{bootmarkID}/report','BootmarkController@report');
    Route::resource('bootmarks', 'BootmarkController', ['except' => ['create', 'show', 'edit']]);

    /* Comment Requests */
    Route::post('comments/{commentID}/report','CommentController@report');

    /* User Requests */
    Route::get('users/{userID}', 'UserController@show'); // TODO: Convert to resource
    Route::post('users/{userID}/report','UserController@report');
});
