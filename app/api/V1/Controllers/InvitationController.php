<?php

namespace App\Api\V1\Controllers;

use App\Invitation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{

    /**
     * Invite a friend to an event
     *
     * @param $event_id
     * @param $invited_id
     */
    public function addFriend($event_id,$invited_id){
        $user = Auth::user();
        $user->events()->attach($event_id, ['invited_id'=>$invited_id]);
    }

    /**
     * Remove a friend from your event
     *
     * @param $event_id
     * @param $invited_id
     */
    public function removeFriend($event_id,$invited_id){
        $user = Auth::user();
        $user->events()->detach($event_id, ['invited_id'=>$invited_id]);
    }


    /**
     * Friend deletes/cancels invitation to an event
     *This permanently deletes the invitation record!
     *
     * @param $event_id
     * @param $invited_id
     *
     */
    public function friendRemove($event_id,$invited_id){
        //Proper procedure this next line
        $user = Auth::user();

        //Delete this next line when seriously testing
//        $user = User::find($invited_id);

        $user->invited()->detach($event_id);
    }

    /**
     * Friend updates their status with plot
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function friendUpdate(Request $request){
        $event_id = $request->input('id');
        $status = $request->input('status');

        $user = Auth::user();
        $user->invited()->updateExistingPivot($event_id, ['status'=>$status]);

        return response()->json([
            'Message' => 'Successful'
        ], 200);
    }

    public function addEventToFavoites(Request $request){
//        return $request;
        $plotter_id = $request->input('plotter_id');
        $event_id = $request->input('id');
        $status = $request->input('status');

        $user = Auth::user();
        $user->invited()->attach($event_id,
            ['plotter_id' => $plotter_id,'status' => $status]
        );

        return response()->json([
            'Message' => 'Successful'
        ], 200);
    }

    public function restoreDelete($id)
    {
        //
        Invitation::where(['invited_id'=>Auth::id()],['event_id'=>$id])->restore();
//
//        $record = Invitation::onlyTrashed()->find($id);
//        if($record->invited_id == Auth::id()){
//            $record->restore();
//        } else{
//            return response()->json([
//                'Message' => 'Unauthorized access'
//            ], 401);
//        }

        return response()->json([
            'Message' => 'Successful'
        ], 200);
    }

    public function softDelete($id)
    {
        //
        Invitation::where(['invited_id'=>Auth::id()],['event_id'=>$id])->first()->delete();

        return response()->json([
            'Message' => 'Successful'
        ], 200);
    }
}
