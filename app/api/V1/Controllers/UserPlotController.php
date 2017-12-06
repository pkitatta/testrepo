<?php

namespace App\Api\V1\Controllers;

use App\Event;
use App\Invitation;
use App\Photo;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class UserPlotController extends Controller
{

    use Helpers;

    public function __construct()
    {
        $this->middleware('api.auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Get all the events the user has created and those to which he was invited to.
        $id = $this->auth->user()->id;

//        $id = Auth::id();
        $events = Invitation::where([['plotter_id', '=',$id],['invited_id','=',$id]])
            ->orWhere('invited_id', $id)
            ->with('event:id,title,description,created_at,event_date,event_time', 'plotter.thumbnail')
            ->get();

//        return dd($events);

        return response()->json([
            'data' => $this->transformCollection($events)
        ], 200);
    }

    public function getDeletedPlots()
    {
        //Get all the events the user has created and those to which he was invited to.
        $id = $this->auth->user()->id;

//        $id = Auth::id();
        $events = Invitation::onlyTrashed()
            ->where([['plotter_id', '=',$id],['invited_id','=',$id]])
            ->orWhere('invited_id', $id)
            ->whereNotNull('deleted_at')
            ->with('event:id,title,description,created_at,event_date,event_time', 'plotter.thumbnail')
            ->get();

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
        $user = Auth::user();
        $friends = $user->getFriends();

//        return $friends->pluck('name','id');
//        return view('plot.create');

        return response()->json([
            'data' => $friends->pluck('name','id')
        ], 200);
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

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_date' => 'required',
            'event_time' => 'required',
        ]);

        //find the object of loggedin user
        $user = Auth::user();

        //if this is a photo from the form
        if($imgfile = $request->file('photo_id')){

            //create the name of the img
            $filename = 'img'.time().$imgfile->getClientOriginalName();
            $thumbname = 'thumb'.time().$imgfile->getClientOriginalName();

            //get the image and file then move it and its thumb to images and image/thumbnails fold(create one if not there)
            $imgfile->move('images',$filename);

            $thumbfile = Image::make($imgfile->getRealPath());
            $thumbfile->resize(80, 80, function ($constraint) {
                $constraint->aspectRatio();
            })->save('images/thumbnails',$thumbname);


            //store the name in the photo table in the database
            $photo = Photo::create(['file'=>$filename]);

            $formdata['photo_id'] = $photo->id;
            $photo->thumbnail()->create(['thumb'=>$thumbfile]);
        }

        $formdata['events_type'] = 1;

        //create event
        $event = $user->event()->create($formdata);

        //Make first entry in invitation table to mark creator
        $user->events()->attach($event->id, ['invited_id'=>Auth::id(),'status'=>1]);
//
        $formdata['fri_id'] = $formdata['fri_id'] ? $formdata['fri_id'] : [];
//        array_unshift($formdata['fri_id'], Auth::id());

        foreach($formdata['fri_id'] as $fid){
            Invitation::create([
                'plotter_id' => Auth::id(),
                'invited_id' => $fid,
                'event_id' => $event->id,
                'status' => 0
//                'status' => Auth::id() == $fid ? 1 : 0
            ]);
        }

        return response()->json([
            'Message' => 'Successful'
        ], 200);

//        return redirect('plot');

//        return response()->json(['message' => 'Event Created Succesfully']);
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
        $plot = Event::find($id);

        return response()->json([
            'data' => $plot
        ], 200);

//        return view('plot.show', compact('plot'));
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
        $plot = Event::find($id);

        return response()->json([
            'data' => $plot
        ], 200);
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

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_date' => 'required',
            'event_time' => 'required',
        ]);


        if($file = $request->file('photo_id')){
            $name = time().$file->getClientOriginalName();

            $file->move('images',$name);

            $photo = Photo::create(['file'=>$name]);

            $input['photo_id'] = $photo->id;
        }

        $this->auth->user()->event()->whereId($id)->first()->update($input);

        return response()->json([
            'Message' => 'Successful'
        ], 200);

//        return redirect('plot');
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
        $plot = Event::findOrFail($id);

        if(isset($plot->photo->file))
            unlink(public_path().$plot->photo->file);

        $plot->delete();

        return redirect('plot');
    }

    private function transformCollection($events){
        return array_map([$this, 'transform'], $events->toArray());
    }

    private function transform($event){
        return [
			'inv_id' => $event['id'],
			'id' => $event['event']['id'],
            'title' => $event['event']['title'],
            'description' => $event['event']['description'],
            'submitted_by' => $event['plotter']['name'],
            'event_date' => $event['event']['event_date'],
            'event_time' => $event['event']['event_time'],
			'photo' => $event['plotter']['thumbnail']['thumb'],
            'created_at' => $event['event']['created_at'],
			'status' => $event['status'],
        ];
    }

    private function transform2($event){
        return [
            'title' => $event['event']['title'],
            'description' => $event['event']['description'],
            'submitted_by' => $event['plotter']['name'],
            'created_at' => $event['event']['created_at'],
        ];
    }

    private function transform3($friend){
        return [
            'id' => $friend['id'],
            'name' => $friend['name'],
        ];
    }
}
