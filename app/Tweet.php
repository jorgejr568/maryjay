<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    public $timestamps = false;

    public $primaryKey = 'id';

    public $incrementing = false;

    protected $dates = [
        'created_at'
    ];
    protected $fillable = [
        'id',
        'user_id',
        'data',
        'query',
        'created_at'
    ];
}
