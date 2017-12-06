<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [
        'title',
        'description',
        'events_type',
        'organised_by',
        'venue',
        'photo_id',
        'plotter_id',
		'event_time',
		'event_date',
		'city_id'
        ];

    public function users(){
        return $this->belongsToMany('App\User','invitations','event_id');
    }

    public function user(){
        return $this->belongsTo('App\User','user_id');
    }

    public function photo(){
        return $this->belongsTo('App\Photo');
    }

    public function city(){
        return $this->belongsTo('App\City');
    }
	
	public function thumbnail(){
        return $this->belongsTo('App\Thumbnail');
    }
}
