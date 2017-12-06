<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HangComps extends Model
{
    //
    use softDeletes;

    protected $fillable = [
        'hangout_id',
        'type',
        'quantity',
        'title',
        'start_time',
        'end_time',
        'date',
    ];

    protected $table = 'hangout_complementary_tickets';
    protected $dates = ['deleted_at'];
}
