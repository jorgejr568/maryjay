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

        Cache::put($this->TWITTER_AUTH_CACHE_NAME,[
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

        Cache::put($this->TWITTER_AUTH_CACHE_NAME,[
            'token' => $reply->oauth_token,
            'secret' => $reply->oauth_token_secret,
        ],now()->addYear());

        return redirect('/twitter/success');
    }

    public function success(){
        return response()->json([
            'success' => true,
            'auth' => $this->getTwitterAuthCache()
        ]);
    }
}
