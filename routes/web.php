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

Route::get('/', 'SiteController@index');

Route::get('/discogs', 'SiteController@discogs');

Route::get('/spotify', 'SiteController@spotify');
Route::get('/spotify/authorise', 'SiteController@spotifyAuthorise');
Route::get('/spotify/callback', 'SiteController@spotifyCallback');
