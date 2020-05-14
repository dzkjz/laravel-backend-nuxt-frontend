<?php

use \Illuminate\Support\Facades\Route;

Route::post('register', 'AuthController@register')->middleware('guest');
Route::post('login', 'AuthController@login')->middleware('guest');
Route::get('user', 'AuthController@user')->middleware('auth');
Route::post('logout', 'AuthController@logout')->middleware('auth');
