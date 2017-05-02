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
    Route::post('bootmarks/cluster','BootmarkController@cluster');
    Route::post('bootmarks/{bootmark}/vote','BootmarkController@vote');
    Route::post('bootmarks/{bootmark}/report','BootmarkController@report');
    Route::resource('bootmarks', 'BootmarkController', ['except' => ['create', 'show', 'edit']]);

    /* Comment Requests */
    Route::post('comments/{comment}/report','CommentController@report');
    Route::post('bootmarks/{bootmark}/comment', 'CommentController@store');
    Route::get('bootmarks/{bootmark}/comment', 'CommentController@index');

    /* User Requests */
    Route::get('users/{user}/follow', 'UserController@getFollowers');
    Route::get('users/{user}/following', 'UserController@getFollowing');
    Route::resource('users', 'UserController', ['only' => ['show', 'update', 'destroy']]);
    Route::post('users/search', 'UserController@search');
    Route::get('users/{user}/photo', 'UserController@getPhoto');
    Route::post('users/{user}/photo', 'UserController@savePhoto');
    Route::post('users/{user}/report','UserController@report');
    Route::post('users/{user}/follow', 'UserController@follow');
    Route::get('users/{user}/bootmarks', 'UserController@bootmarks');
    Route::resource('users', 'UserController', ['only' => ['show', 'update', 'destroy']]);
});
