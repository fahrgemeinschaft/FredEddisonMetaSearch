<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('trip/search', 'TripController@search');
Route::post('trip/search/{id}', 'TripController@search_id');
Route::get('trip/search/{id}', 'TripController@search_id');
Route::get('trip/search', 'TripController@search');
Route::post('trip/poll/{id}', 'TripController@poll_id');

