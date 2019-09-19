<?php

namespace App\Console\Commands;

use App\Core\TwitterAuth;
use App\Exceptions\TwitterAuthFailed;
use App\Exceptions\TwitterRequestFailed;
use App\Jobs\GetDataFromTweet;
use App\Tweet;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AcquireTweets extends Command
{
    use TwitterAuth;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acquire_tweets {query*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Acquire tweets';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->bootTwitterAuth();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws TwitterAuthFailed
     */
    public function handle()
    {
        $minutesDispatch = [0,15,30,45];
        $currentHour = now()->startOfHour();
        $fromHour = $currentHour->copy()->subHour();

            foreach ($this->argument('query') as $query) {
                $this->info("GETTING TWEETS FROM {$query}");

                $count = 100;
                $breakDoWhile = false;
                $maxTweetId = null;

                do {
                    $continue = false;
                    try {
                        $res = $this->cb->search_tweets(http_build_query([
                            'q' => $query,
                            'count' => $count,
//                            'until' => now()->format('Y-m-d'),
                            'max_id' => $maxTweetId ?? null,
                        ]));

                        if($res->httpstatus != 200) throw new TwitterRequestFailed(
                            json_encode($res->errors),
                            $res->httpstatus
                            );

                        echo PHP_EOL;
//                        dd($res);

                        $progress = $this->output->createProgressBar(count($res->statuses));

                        foreach ($res->statuses as $status) {
                            $tweetCreatedAt = Carbon::createFromTimeString($status->created_at)->subHours(3);
                            if($tweetCreatedAt->lt($fromHour)){
                                $breakDoWhile = true;
                                break;
                            }
                            $this->info($tweetCreatedAt);

                            if (!Tweet::where('id', $status->id)->exists()) {
                                $tweet = Tweet::create([
                                    'id' => $status->id,
                                    'created_at' => $tweetCreatedAt,
                                    'data' => NULL,
                                    'user_id' => $status->user->id,
                                    'query' => $query,
                                    'pre_data' => $status
                                ]);

                                $maxTweetId = $tweet->id;

                                GetDataFromTweet::dispatch($tweet);
                            }
                            $progress->advance();
                        }

                        if($breakDoWhile){
                            break;
                        }

                    }catch (TwitterRequestFailed $e){
                        if($e->getCode() == 429) {
                            sleep(60 * 5);
                            $continue = true;
                        }
                        else throw new TwitterAuthFailed("Twitter Auth Failed",$e->getCode(),$e);
;
                    }
                } while ($continue || count($res->statuses) == $count);
                $progress->finish();
            }
    }
}
