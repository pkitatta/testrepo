<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hangout extends Model
{
    //
    protected $fillable = [
        'name',
        'theme',
        'street_address',
        'city_id',
        'beer_price',
        'entry_price',
        'photo_id',
		'thumbnail_id',
    ];

    public function users(){
        Return $this->hasMany('App\User');
    }

    public function celebs(){
        Return $this->hasMany('App\Celebrity');
    }

    public function compTickets(){
        Return $this->hasMany('App\HangComps','hangout_id');
    }

    public function city(){
        return $this->belongsTo('App\City');
    }

    public function photos(){
        return $this->morphMany('App\Photo','photoable');
    }
	
	public function thumbnail(){
        return $this->belongsTo('App\Thumbnail');
    }
}
