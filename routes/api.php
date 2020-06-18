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

// Route::routes();

Route::get('/fix', function(){
    DB::table('users')->update(['gender' => 'Male']);

});

Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');
Route::get('/verify/{token}', 'AuthController@verify')->name('verify');
Route::post('/password/forgot', 'AuthController@requestReset');
Route::get('/password/reset/{token}', 'AuthController@findResetToken');
Route::post('/password/reset', 'AuthController@resetPassword');

Route::get('profile/{user}', 'ProfileController@index');

Route::group(['middleware' => 'auth:api'], function () {


    Route::post('/password/update', 'AuthController@updatePassword');
    Route::post('/logout', 'AuthController@logout');
    Route::get('/clear_session', 'AuthController@clear_session');


    Route::get('user/task/', 'TasksController@intern_view_track_task');



    Route::resource('posts', 'PostsController');
    Route::get('categories/posts/{id}', 'PostsController@view_posts_in_category');


    // NOTIFICATION
    Route::get('notifications', 'NotificationController@index');
    Route::delete('notifications', 'NotificationController@destroy');
    Route::post('notifications/markasread', 'NotificationController@markAsRead');
    Route::post('notifications/read', 'NotificationController@markOneAsRead');
    Route::get('notifications/notification_count', 'NotificationController@notification_count');

});
