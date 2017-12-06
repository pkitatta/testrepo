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

use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

//Route::get('/test', function () {
//    $hangout = \App\Hangout::find(1);
//    $user = App\User::find(1);
//
//    $userList = [];
//
//
//
//    foreach ($hangout->users as $friend){
//        if ($user->isFriendWith($friend)){
//            array_push($userList, $friend);
//        }
//    }
//
//    return $userList;
//});

// usage inside a laravel route
//Route::get('/test', function()
//{
//    $img = Image::make('foo.jpg')->resize(300, 200);
//
//    return $img->response('jpg');
//});

Route::get('remote', function(){
    return 'remote connection works';
});

Route::get('/test', function()
{
//    $roles = Role::lists('name','id')->all();
    $user = App\User::find(1);

    $fs = $user->getFriends();

//    foreach($fs as $f)
//        echo $f. '<br>';
    return $fs;

//    return response()->json([
//        'data' => $fs->pluck('name','id')
//    ], 200);
});
