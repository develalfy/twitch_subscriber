<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'auth/twitch'], function(){
    Route::get('/', 'TwitchController@redirectToProvider')->name('twitch.auth');
    Route::get('/callback', 'TwitchController@handleProviderCallback');
});

Auth::routes(['register' => false]);

Route::get('/home', 'HomeController@index');
Route::get('stream', 'TwitchController@stream')->name('stream');