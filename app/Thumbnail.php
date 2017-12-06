<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Thumbnail extends Model
{
    //
    protected $uploads = '/images/thumbnails/';

    protected $fillable = ['thumb'];

    public function getFileAttribute($thumb){
        return $this->uploads.$thumb;
    }

    //public function photo(){
    //    return $this->belongsTo('App\Photo');
    //}
	
	//public function event(){
    //    return $this->hasOne('App\Event');
    //}
	
	
}
