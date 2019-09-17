<?php

namespace App\Http\Controllers\Twitter;

use App\Core\TwitterAuth;
use Carbon\Carbon;
use Codebird\Codebird;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    use TwitterAuth;

    public function __construct()
    {
        $this->bootTwitterAuth();;
    }


    public function redirect(){
        $reply = $this->cb->oauth_requestToken([
            'oauth_callback' => config('services.twitter.callback')
        ]);

        $this->cb->setToken($reply->oauth_token, $reply->oauth_token_secret);

        Cache::put(self::TWITTER_AUTH_CACHE_NAME,[
            'token' => $reply->oauth_token,
            'secret' => $reply->oauth_token_secret
        ],now()->addYear());

        // redirect to auth website
        $auth_url = $this->cb->oauth_authorize();
        return redirect($auth_url);
    }

    public function callback(Request $request){
        $reply = $this->cb->oauth_accessToken([
            'oauth_verifier' => $request->input('oauth_verifier')
        ]);

        Cache::put(self::TWITTER_AUTH_CACHE_NAME,[
            'token' => $reply->oauth_token,
            'secret' => $reply->oauth_token_secret,
        ],now()->addYear());

        return redirect('/twitter/success');
    }

    public function success(){
        $res = $this->cb->search_tweets('q=globo&count=100');

        dd($res->statuses[0]);
        $res = $this->cb->statuses_show_ID('id='.$res->statuses[0]->id);

        dd(Carbon::createFromTimeString($res->created_at),$res);
        return response()->json([
            'success' => true,
            'auth' => $this->getTwitterAuthCache()
        ]);
    }
}
