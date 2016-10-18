<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WebsiteController@home');


Route::group(['prefix' => 'api/v1'], function() {
    Route::post('authenticate', 'Auth\oAuthController@issueToken');
    Route::post('register', 'UserController@store');
});

Route::group(['prefix' => 'api/v1', 'middleware' => 'oauth'], function () {
    /* Resources */
    Route::resource('bootmarks', 'BootmarkController', ['except' => ['create', 'show', 'edit']]);

    /* Post Requests */
    Route::post('bootmarks/{bootmarks}/vote','BootmarkController@vote');
    Route::post('bootmarks/{bootmarkID}/report','BootmarkController@report');
    Route::post('comments/{commentID}/report','CommentController@report');
    Route::post('users/{userID}/report','UserController@report');

    /* Get Requests */
    Route::get('bootmarks/{bootmarkID}/photo', 'BootmarkController@getPhoto');
});
