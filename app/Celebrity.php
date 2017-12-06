<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Celebrity extends Model
{
    //
    public function events(){
        return $this->belongsToMany('App\Event','invitations','plotter_id');
    }

    public function invited(){
        return $this->belongsToMany('App\Event','invitations','invited_id');
    }

    public function hangout(){
        return $this->belongsTo('App\Hangout');
    }

    public function photo(){
        return $this->belongsTo('App\Photo');
    }
}
