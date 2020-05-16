<?php

use \Illuminate\Support\Facades\Route;

Route::post('register', 'AuthController@register')->middleware('guest');
Route::post('login', 'AuthController@login')->middleware('guest');
Route::get('user', 'AuthController@user')->middleware('auth');
Route::post('logout', 'AuthController@logout')->middleware('auth');

Route::group(['prefix' => 'password'], function () {
    Route::post('create', 'PasswordResetController@create');
    Route::get('find/{token}', 'PasswordResetController@find');
    Route::post('reset', 'PasswordResetController@reset');
});

Route::group(['prefix' => 'topics'], function () {
    Route::post('/', 'TopicController@store')->middleware('auth');
    Route::get('/', 'TopicController@index');
    Route::get('/{topic}', 'TopicController@show');
    Route::patch('/{topic}', 'TopicController@update')->middleware('auth');
    Route::delete('/{topic}', 'TopicController@destroy')->middleware('auth');


    //post group
    Route::group(['prefix' => '/{topic}/posts'], function () {
        Route::get('/{post}', 'PostController@show');
        Route::post('/', 'PostController@store')->middleware('auth');
        Route::patch('/{post}', 'PostController@update')->middleware('auth');
        Route::delete('/{post}', 'PostController@destroy')->middleware('auth');

        //like group
        Route::group(['prefix' => '/{post}/likes'], function () {
            Route::post('/', 'PostLikeController@store')->middleware('auth');
        });
    });


});


