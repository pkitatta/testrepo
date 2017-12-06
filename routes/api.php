<?php

use Illuminate\Http\Request;

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
$api = app('Dingo\Api\Routing\Router');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//$api->version('v1', function ($api) {
//    $api->resource('events', 'App\Http\Controllers\EventPlotController');
//});

//$api->version('v1', function ($api) {
//    $api->resource('events', 'App\Api\V1\Controllers\EventPlotController');
//});

//$api->group(['middleware' => 'jwt.auth'], function(Router $api) {
//    $api->get('protected', function() {
//        return response()->json([
//            'message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.'
//        ]);
//    });
//
//    $api->get('refresh', [
//        'middleware' => 'jwt.refresh',
//        function() {
//            return response()->json([
//                'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
//            ]);
//        }
//    ]);
//});

$api->version('v1', function ($api) {

    $api->post('authenticate', 'App\Api\V1\Controllers\AuthenticateController@authenticate');
    $api->get('authenticate/user', 'App\Api\V1\Controllers\AuthenticateController@getAuthenticatedUser');

    $api->group(['middleware' => 'api.auth'], function($api) {

        //Plot controller
        $api->get('plots/deletedplots', 'App\Api\V1\Controllers\UserPlotController@getDeletedPlots');
        $api->resource('plots', 'App\Api\V1\Controllers\UserPlotController');

        //Events controller
        $api->resource('events', 'App\Api\V1\Controllers\EventPlotController');

        //Hangout photo controller
        $api->post('hangouts/userphotos', 'App\Api\V1\Controllers\HangoutMediaController@getAdminPhotos');

        //Hangout controller
        $api->get('hangouts/userper', 'App\Api\V1\Controllers\HangoutController@getUserPerForHangout');
		$api->get('hangouts/frcount', 'App\Api\V1\Controllers\HangoutController@getFriendsForHangout');
        $api->resource('hangouts', 'App\Api\V1\Controllers\HangoutController');


        //Invitations controller
        $api->get('plot/add/{event_id}/{invited_id}', 'App\Api\V1\Controllers\InvitationController@addFriend')->where(['event_id' => '[0-9]+', 'invited_id' => '[0-9]+']);
        $api->get('plot/removef/{event_id}/{invited_id}', 'App\Api\V1\Controllers\InvitationController@removeFriend')->where(['event_id' => '[0-9]+', 'invited_id' => '[0-9]+']);
        $api->get('plot/fremove/{event_id}/{invited_id}', 'App\Api\V1\Controllers\InvitationController@friendRemove')->where(['event_id' => '[0-9]+', 'invited_id' => '[0-9]+']);
        $api->post('plot/fupdate', 'App\Api\V1\Controllers\InvitationController@friendUpdate')->where(['event_id' => '[0-9]+', 'status' => '[0-9]+']);
		$api->post('plot/addeventtofavorite', 'App\Api\V1\Controllers\InvitationController@addEventToFavoites')->where(['event_id' => '[0-9]+','submitted_id' => '[0-9]+', 'status' => '[0-9]+']);
        $api->delete('plot/softdelete/{id}', 'App\Api\V1\Controllers\InvitationController@softDelete')->where(['id' => '[0-9]+']);
        $api->get('plot/restoredeletedplot/{id}', 'App\Api\V1\Controllers\InvitationController@restoreDelete')->where(['id' => '[0-9]+']);


        //Friendship controller
        $api->get('friendship/addfr/{id}', 'App\Api\V1\Controllers\FriendshipController@befriend');
        $api->get('friendship/denyfr/{id}', 'App\Api\V1\Controllers\FriendshipController@denyFriendRequest');
        $api->get('friendship/blockfr/{id}', 'App\Api\V1\Controllers\FriendshipController@blockFriend');
        $api->get('friendship/unblockfr/{id}', 'App\Api\V1\Controllers\FriendshipController@unblockFriend');
        $api->get('friendship/getfr', 'App\Api\V1\Controllers\FriendshipController@getFriends');

        //Location controller
        $api->get('cities', 'App\Api\V1\Controllers\GeoLocationController@getCities');


        $api->post('user/hangupdate', 'App\Api\V1\Controllers\UserProfileController@hangoutUpdate');

        //Complementary ticket controller
        $api->get('plot/hangoutcomplementary/{id}', 'App\Api\V1\Controllers\CompTicketController@getComplementaries');
        $api->post('plot/hangoutcomplementary', 'App\Api\V1\Controllers\CompTicketController@compClaim');
    });
});