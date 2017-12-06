<?php

//namespace App\Http\Controllers;

namespace App\Api\V1\Controllers;

use App\Hangout;
use App\Photo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HangoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $hangouts = Hangout::select('id','name','beer_price','entry_price','thumbnail_id')
            ->with('thumbnail','users')->get();

        $userlist = [];

        //Get all the user friends in hangout
        foreach($hangouts as $hang){
            $fr = $this->hangFriends($hang->id);
            $hang->friendCount = $fr;
        }

        //Get all the percentage of users in a hangout
        foreach($hangouts as $hang){
            $percentage = $this->HangoutUserPercentage($hang->id);
            $hang->userPer = $percentage;
        }

        return response()->json([
        'data' => $this->transformCollection($hangouts)
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('hangouts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $formdata = $request->all();

        $request->validate([
            'name' => 'required',
            'street_address' => 'required',
            'city_id' => 'required',
            'contact_email' => 'required',
            'passwords.password' => 'required|confirmed'
        ]);

//        $this->validate($request, [
//            'password' => 'required_with:new_password|password|max:8',
//            'new_password' => 'confirmed|max:8',
//        ]);



        //find the object of loggedin user
        $user = Auth::user();
//
        //if then is a photo from the form
        if($file = $request->file('photo_id')){

            //create the name of the img
            $name = time().$file->getClientOriginalName();

            //get the image file then move it to images fold(create one if not there)
            $file->move('images',$name);

            //store the name in the photo table in the database
            $photo = Photo::create(['file'=>$name]);

            $formdata['photo_id'] = $photo->id;
        }

        $hangout = Hangout::create($formdata);
//
        $password = bcrypt($formdata['passwords']['password']);

        $hangoutId = 100;

        $user->admin_hangouts()->attach($hangoutId, ['password' => $password,'level'=>2]);

//        $user->admin_hangouts()->attach($hangout->id, ['password' => $password,'level'=>2]);

        return response()->json([
            'Message' => 'Successful'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $hangout = Hangout::find($id);

//        return dd($hangout);

//        return response()->json([
//            'data' => $hangout
//        ], 200);

        return response()->json([
            'data' => $this->transform2($hangout)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $input = $request->all();

        if($file = $request->file('photo_id')){
            $name = time().$file->getClientOriginalName();

            $file->move('images',$name);

            $photo = Photo::create(['file'=>$name]);

            $input['photo_id'] = $photo->id;
        }

        Auth::user()->hangout()->whereId($id)->first()->update($input);

        return response()->json([
            'Message' => 'Successful'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $hangout = Hangout::findOrFail($id);

        if(isset($hangout->photo->file))
            unlink(public_path().$hangout->photo->file);

        $hangout->delete();

        return response()->json([
            'Message' => 'Successful'
        ], 200);

    }

    /**
     * Calculate user percentages
     *
     * @param $id
     * @return int
     */
    public function HangoutUserPercentage($id){

        $userList = User::whereNotNull('hangout_id')->get()->count();

        $users = $this->countHangoutUsers($id);

        $userPer = (($users/$userList)*100);

        return $userPer;
    }

    /**
     * Count Users in hangouts
     *
     * @param $id
     * @return int
     */
    public function countHangoutUsers($id){

        $hangout = Hangout::findOrFail($id);

        $userList = collect($hangout->users);

        return $userList->count();
    }

    /**
     * Count friends in a hangout
     *
     * @param $id
     * @return int
     */
    public function hangFriends($id)
    {
        //
        $hangout = Hangout::findOrFail($id);

        $user = Auth::user();

        $userList = [];

        foreach ($hangout->users as $friend){
            if ($user->isFriendWith($friend)){
                array_push($userList, $friend);
            }
        }

        $collection = collect($userList);

        return $collection->count();

    }

    /**
     * Get friends in hangout
     *
     * @param Request $input
     * @return array
     */
    public function getFriendsForHangout(Request $input){
        $hangouts = Hangout::pluck('id');

        $list = [];
        foreach($hangouts as $hang){
            $fr = $this->hangFriends($hang);
            array_push($list,['hangout_id'=>$hang, 'friendCount'=>$fr]);
        }

        return $list;
    }
	
	public function getUserPerForHangout(Request $input){
        $hangouts = Hangout::pluck('id');

        $list = [];
        foreach($hangouts as $hang){
            $fr = $this->HangoutUserPercentage($hang);
            array_push($list,['hangout_id'=>$hang, 'userPer'=>$fr]);
        }

        return $list;
    }


    private function transformCollection($hangouts){
        return array_map([$this, 'transform'], $hangouts->toArray());
    }

    private function transform($hangout){
        return [
            'id' => $hangout['id'],
            'name' => $hangout['name'],
            'beer' => $hangout['beer_price'],
            'entry' => $hangout['entry_price'],
            'photo' => $hangout['thumbnail']['thumb'],
            'friendCount' => $hangout['friendCount'],
            'userPer' => $hangout['userPer'],
        ];
    }

    private function transform2($hangout){
        return [
            'id' => $hangout['id'],
            'name' => $hangout['name'],
            'address' => $hangout['street_address'],
            'beer' => $hangout['beer_price'],
            'entry' => $hangout['entry_price'],
            'city' => $hangout['city']['name'],
            'city_id' => $hangout['city_id'],
            'country' => $hangout['city']['country']['name'],
            'theme' => $hangout['theme'],
            'email' => $hangout['contact_address'],
        ];
    }
}
