<?php

namespace App\Api\V1\Controllers;

use App\Event;
use App\Photo;
use App\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class EventPlotController extends Controller
{

    /**
     * This is commented out because authentication is at the routes
     * uncomment if you want authentication at the controller
     */
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
        //header('Access-control-allow-origin: *');
        $events = Event::select('id','title','description','entry_price','beer_price','event_date','event_time','thumbnail_id','user_id')
            ->with('thumbnail', 'user')
            ->where('events_type', 2)->get();

        foreach($events as $event){
            $status = Invitation::where(['event_id' => $event->id,'invited_id'=>Auth::id()])->first();
            $status = $status ? 1 : 0;
            $event->status = $status;
        }

//        return view('events.index', compact('events'));

//        return dd($events);

        return response()->json([
            'data' => $this->transformCollection($events),
            'friends' => 'more data'
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
		$formdata = $request->all();
        //
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_date' => 'required',
            'event_time' => 'required',
            'city_id' => 'required',
            'venue' => 'required',
        ]);


        //find the object of loggedin user
        $user = Auth::user();
		
        //if then is a photo from the form
        if($imgfile = $request->file('photo_id')){

            //create the name of the img

            $filename = 'img'.time().$imgfile->getClientOriginalName();
            $thumbname = 'thumb'.time().$imgfile->getClientOriginalName();

            //get the image and file then move it and its thumb to images and image/thumbnails fold(create one if not there)
            $thumbfile = Image::make($imgfile->getRealPath());
            $thumbfile->resize(80, 80, function ($constraint) {
                $constraint->aspectRatio();
            })->save('images/thumbnails/'.$thumbname);

            $imgfile->move('images',$filename);

            //store the name in the photo table in the database
            $photo = Photo::create(['file'=>$filename]);

            $formdata['photo_id'] = $photo->id;
            $photo->thumbnail()->create(['thumb'=>$thumbname]);
        }

        $formdata['events_type'] = 2;

        //create event
        $event = $user->event()->create($formdata);

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
            'data' => $event
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

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_date' => 'required',
            'event_time' => 'required',
        ]);

        if($imgfile = $request->file('photo_id')){

            //create the name of the img

            $filename = 'img'.time().$imgfile->getClientOriginalName();
            $thumbname = 'thumb'.time().$imgfile->getClientOriginalName();

            //get the image and file then move it and its thumb to images and image/thumbnails fold(create one if not there)

            $thumbfile = Image::make($imgfile->getRealPath());
            $thumbfile->resize(80, 80, function ($constraint) {
                $constraint->aspectRatio();
            })->save('images/thumbnails/'.$thumbname);

            $imgfile->move('images',$filename);

            //store the name in the photo table in the database
            $photo = Photo::create(['file'=>$filename]);

            $input['photo_id'] = $photo->id;
            $photo->thumbnail()->create(['thumb'=>$thumbname]);
        }

//        $event = $user->event()->create($formdata);

        Auth::user()->event()->whereId($id)->update($input);

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
            'id' => $event['id'],
            'title' => $event['title'],
            'description' => $event['description'],
            'entry_price' => $event['entry_price'],
            'beer_price' => $event['beer_price'],
            'event_date' => $event['event_date'],
			'event_time' => $event['event_time'],
            'user_id' => $event['user_id'],
            'thumb' => 'images/thumbnails/'.$event['thumbnail']['thumb'],
            'status' => $event['status']

        ];
    }
}
