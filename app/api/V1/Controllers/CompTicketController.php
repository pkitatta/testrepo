<?php

namespace App\Api\V1\Controllers;

use App\HangComps;
use App\Hangout;
use App\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompTicketController extends Controller
{
    //
    public function getComplementaries($id){
        //
        $complementary = HangComps::where([['hangout_id', '=',$id],['quantity','!=',0]])->first();

        if(!is_null($complementary)){
            $comp = Invitation::where([['plotter_id', '=',Auth::id()],['complementary_id','=',$complementary->id]])->first();
            if(!is_null($comp)) {
            $complementary = null;
            }
        }

        return response()->json([
            'data' => $complementary
        ], 200);
    }

    public function compClaim(Request $request){
        //
        $user = Auth::user();

        $comp_id = $request->input('id');
        $hangout_id = $request->input('hangout_id');

        $comp = HangComps::findorfail($comp_id);

        if($comp->quantity == 0)
        {
            return response()->json([
                'Message' => 'Sorry, we have run out of complementary tickets :('
            ], 401);
        }

        $newquantity = $comp->quantity - 1;

        $hangout = Hangout::findorfail($hangout_id);

        $hangout->compTickets()->where('id', '=' ,$comp->id)->first()->update(['quantity'=>$newquantity]);

        //create event
        $event = $user->event()->create([
            'title'=>'Plot @'.$hangout->name,
            'description'=>'You have received a complementary ticket from '.$hangout->name.'. This complementary is valid from '.$comp->start_time.' '.$comp->event_date.' to '.$comp->end_time.' '.$comp->end_date.'. Have fun!! :)',
            'events_type'=> 1
        ]);

        //Insert user invitation record with complementary ticket in invitation table creating plot
        $user->events()->attach($event->id, ['invited_id'=>Auth::id(),'status'=>1,'complementary_id'=>$comp->id,'comp_status'=>0]);

        return response()->json([
            'Message' => 'Successful'
        ], 200);
    }
}
