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

    public static function toV3($tweet){
        return [
            "id" => $tweet->id,
            "text" => $tweet->text,
            "retweet_count" => $tweet->retweet_count,
            "favorite_count" => $tweet->favorite_count,
            "retweeted" => $tweet->retweeted,
            "lang" => $tweet->lang,
            "user" => [
                "id" => $tweet->user->id,
                "name" => $tweet->user->name,
                "screen_name" => $tweet->user->screen_name,
                "followers_count" => $tweet->user->followers_count,
                "favourites_count" => $tweet->user->favourites_count,
                "location" => $tweet->user->location
            ],
            "retweeted_status" => ($tweet->retweeted_status ?? false) ? [
                "id" => $tweet->retweeted_status->id,
                "text" => $tweet->retweeted_status->text,
                "retweet_count" => $tweet->retweeted_status->retweet_count,
                "favorite_count" => $tweet->retweeted_status->favorite_count,
                "user" => [
                    "id" => $tweet->retweeted_status->user->id,
                    "name" => $tweet->retweeted_status->user->name,
                    "screen_name" => $tweet->retweeted_status->user->screen_name,
                    "followers_count" => $tweet->retweeted_status->user->followers_count,
                    "location" => $tweet->retweeted_status->user->location,
                    "favourites_count" => $tweet->retweeted_status->user->favourites_count
                ]
            ] : null,
        ];
    }
}
