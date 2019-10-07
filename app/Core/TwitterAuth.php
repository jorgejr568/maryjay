<?php


namespace App\Core;


use Codebird\Codebird;
use Illuminate\Support\Facades\Cache;

trait TwitterAuth
{
    protected $TWITTER_AUTH_CACHE_NAME="twitter_auth";

    protected $cb;

    public function bootTwitterAuth()
    {
        $this->cb = Codebird::getInstance();
        $this->cb->setToken($this->getTwitterAuthCache()['token'],$this->getTwitterAuthCache()['secret']);
        $this->cb->setConnectionTimeout(15000);
        $this->cb->setTimeout(30000);
    }

    private function getTwitterAuthCache(){
        return Cache::get($this->TWITTER_AUTH_CACHE_NAME);
    }
}
