<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    protected $fillable = [
        'plotter_id',
        'invited_id',
        'event_id',
        'status',
        'complementary_id',
        'comp_status'
    ];
    //
    use softDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function plotter(){
        return $this->belongsTo('App\User','plotter_id');
    }

    public function event(){
        return $this->belongsTo('App\Event','event_id');
    }
}
