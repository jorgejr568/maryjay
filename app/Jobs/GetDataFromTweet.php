<?php

namespace App\Jobs;

use App\Core\TwitterAuth;
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

    /**
     * Create a new job instance.
     *
     * @param Tweet $tweet
     */
    public function __construct(Tweet $tweet)
    {
        //
        $this->tweet = $tweet;
        $this->bootTwitterAuth();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $res = $this->cb->statuses_show_ID('id='.$this->tweet->id);

        $this->tweet->update([
            'data' => json_encode($res)
        ]);
    }
}
