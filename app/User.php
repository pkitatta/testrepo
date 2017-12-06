<?php

namespace App;

use Hootlex\Friendships\Traits\Friendable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;
    use Friendable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'hangout_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
	
//	 /**
//     * Automatically creates hash for the user password.
//     *
//     * @param  string  $value
//     * @return void
//     */
//    public function setPasswordAttribute($value)
//    {
//        $this->attributes['password'] = Hash::make($value);
//    }

    public function events(){
        return $this->belongsToMany('App\Event','invitations','plotter_id');
    }

    public function invited(){
        return $this->belongsToMany('App\Event','invitations','invited_id');
    }

    public function event(){
        return $this->hasMany('App\Event', 'user_id');
    }

    public function comps(){
        return $this->belongsToMany('HangComps', 'invitations','plotter_id','complementary_id');
    }

    public function admin_hangouts(){
        return $this->belongsToMany('App\Hangout', 'hangout_admins','user_id','hangout_id');
    }

    public function hangout(){
        return $this->belongsTo('App\Hangout');
    }

    public function photo(){
        return $this->belongsTo('App\Photo');
    }

    public function photos(){
        return $this->hasMany('App\Photo');
    }
	
	 public function thumbnail(){
        return $this->belongsTo('App\Thumbnail');
    }
	
	public function city(){
        return $this->belongsTo('App\City');
    }
}
