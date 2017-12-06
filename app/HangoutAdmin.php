<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HangoutAdmin extends Model
{
    //
    protected $fillable = [
        'password',
        'level'
    ];

    public function user(){
        return $this->hasMany('App\Hangout');
    }
}
