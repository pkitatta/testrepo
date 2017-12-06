<?php

namespace App\Api\V1\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendshipController extends Controller
{

    public function befriend($id){

        $user = Auth::user();
        $recipient = User::find($id);

        $user->befriend($recipient);
    }

    public function denyFriendRequest($id){
        $user = Auth::user();
        $sender = User::find($id);

        $user->denyFriendRequest($sender);
    }

    public function blockFriend($id){
        $user = Auth::user();
        $friend = User::find($id);

        $user->blockFriend($friend);
    }

    public function unblockFriend($id){
        $user = Auth::user();
        $friend = User::find($id);

        $user->unblockFriend($friend);
    }

    public function getFriends(){
        $user = Auth::user();
//
        $friends = $user->getFriends();

        return response()->json([
            'data' => $this->transformCollection($friends)
        ], 200);

//        $friends = $user->getFriends()->pluck('id','name')->toArray();
//
//        return response()->json([
//            'data' => [$friends]
//        ], 200);
    }

    private function transformCollection($friends){
        return array_map([$this, 'transform'], $friends->toArray());
    }

    private function transform($friends){
        return [
            'fri_id' => $friends['id'],
            'fri_name' => $friends['name'],
        ];
    }
}
