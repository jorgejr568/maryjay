<?php

namespace App\Jobs;

use App\Core\TwitterAuth;
use App\Exceptions\TwitterAuthFailed;
use App\Exceptions\TwitterRequestFailed;
use App\Tweet;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetDataFromTweet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,TwitterAuth;
    /**
     * @var Tweet
     */
    private $tweet;

    public $timeout = 60 * 20;

    /**
     * Create a new job instance.
     *
     * @param Tweet $tweet
     */
    public function __construct(Tweet $tweet)
    {
        //
        $this->tweet = $tweet;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws TwitterAuthFailed
     */
    public function handle()
    {
        $this->bootTwitterAuth();
        do {
            try {
                $res = $this->cb->statuses_show_ID('id=' . $this->tweet->id);

                if($res->httpstatus == 200){
                    break;
                }else throw new TwitterRequestFailed("Twitter Request Failed",$res->httpstatus);

            }catch (TwitterRequestFailed $e){
                if($e->getCode() == 429) {
                    sleep(60 * 5);
                }
                else throw new TwitterAuthFailed("Twitter Auth Failed",$e->getCode(),$e);
            }
        }while(true);
        $this->tweet->update([
            'data' => json_encode($res)
        ]);
    }
}
