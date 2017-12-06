<?php

namespace App\Http\Controllers;

use App\Event;
use App\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventPlotController extends Controller
{

//    public function __construct(){
//        $this->middleware('jwt.auth');
//    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $events = Event::select('title','description','entry_price','beer_price','city_id','organised_by')
            ->with('city' )
            ->where('events_type', 2)->get();

//        return view('events.index', compact('events'));

//        return dd($events);

        return response()->json([
            'data' => $this->transformCollection($events)
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
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $formdata = $request->all();

        //find the object of loggedin user
        $user = Auth::user();

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

        $formdata['events_type'] = 2;

        //create event
        $event = Event::create($formdata);

        //Make first entry in invitation table to mark creator
        $user->events()->attach($event->id, ['invited_id'=>Auth::id(),'status'=>1]);

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
        $event = Event::find($id);

//        return dd($event);

        return response()->json([
            'data' => $this->transform($event)
        ], 200);

//        return view('events.show', compact('event'));
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
        $event = Event::find($id);

//        return dd($plot);

        return view('events.edit', compact('event'));
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

        Auth::user()->events()->whereId($id)->first()->update($input);

//        return redirect('events');

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
        $event = Event::findOrFail($id);

        if(isset($event->photo->file))
            unlink(public_path().$event->photo->file);

        $event->delete();

//        return redirect('events');

        return response()->json([
            'Message' => 'Successful'
        ], 200);
    }

    private function transformCollection($events){
        return array_map([$this, 'transform'], $events->toArray());
    }

    private function transform($event){
        return [
            'event_title' => $event['title'],
            'event_des' => $event['description'],
            'entry_price' => $event['entry_price'],
            'beer_price' => $event['beer_price'],
            'city' => $event['city']['name'],
            'organiser' => $event['organised_by']

        ];
    }
}
