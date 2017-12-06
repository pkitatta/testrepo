<?php

namespace App\Api\V1\Controllers;

use App\Photo;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class HangoutMediaController extends Controller
{
    //
    public function getAdminPhotos(Request $request){

//        return $request->all();

        //
        //if then is a photo from the form
        if($imgfile = $request->file('photo_id')) {

            //create the name of the img

            $filename = 'img' . time() . $imgfile->getClientOriginalName();
            $thumbname = 'thumb' . time() . $imgfile->getClientOriginalName();

            //get the image and file then move it and its thumb to images and image/thumbnails fold(create one if not there)

            $thumbfile = Image::make($imgfile->getRealPath());
            $thumbfile = $thumbfile->resize(100, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save('images/thumbnails/' . $thumbname);

            $imgfile->move('images', $filename);

            //store the name in the photo table in the database
            $photo = Photo::create(['file' => $filename]);

            $formdata['photo_id'] = $photo->id;
            $photo->thumbnail()->create(['thumb' => $thumbname]);
        }
    }


    public function getUsersPhotos(){
        //
    }

    public function storeUsersPhotos(){
        //
    }
}
