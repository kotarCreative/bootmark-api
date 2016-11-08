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
    Route::get('bootmarks/{bootmark}/photo', 'BootmarkController@getPhoto');
    Route::post('bootmarks/{bootmark}/vote','BootmarkController@vote');
    Route::post('bootmarks/{bootmark}/report','BootmarkController@report');
    Route::resource('bootmarks', 'BootmarkController', ['except' => ['create', 'show', 'edit']]);

    /* Comment Requests */
    Route::post('comments/{comment}/report','CommentController@report');

    /* User Requests */
    Route::resource('users', 'UserController', ['only' => ['show', 'update', 'destroy']]);
    Route::get('users/{user}/photo', 'UserController@getPhoto');
    Route::post('users/{user}/photo', 'UserController@savePhoto');
    Route::post('users/{user}/report','UserController@report');
});
