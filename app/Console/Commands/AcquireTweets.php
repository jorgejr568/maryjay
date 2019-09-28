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
        //** Get current hour */
        $currentHour = now()->startOfHour();
        /**  Get hour before */
        $fromHour = $currentHour->copy()->subHour();
        // We will get tweets only until $fromHour

            // Foreach query passed as argument to command | globo havan natura
            foreach ($this->argument('query') as $query) {
                // Echo info into terminal
                $this->info("GETTING TWEETS FROM {$query}");
                // Number of tweets that we gonna get in one request
                $count = 100;
                // Variable to control if we are getting only tweets until $fromHour
                $breakDoWhile = false;
                // Variable to paginate correctly twitter api
                $maxTweetId = null;
                do {
                    // Variable to control if has any errors on twitter request
                    $continue = false;

                    try {
                        // doing twitter request
                        $res = $this->cb->search_tweets(http_build_query([
                            'q' => $query,
                            'count' => $count,
                            'max_id' => $maxTweetId ?? null,
                        ]));

                        // checking if request wasn't ok
                        if($res->httpstatus != 200) throw new TwitterRequestFailed(
                            json_encode($res->errors),
                            $res->httpstatus
                            );

                        // creating progressbar for terminal
                        $progress = $this->output->createProgressBar(count($res->statuses));

                        foreach ($res->statuses as $status) {
                            // parsing tweet created_at property to an Carbon object that we can handle easily | Sub hours because value comes in GMT
                            $tweetCreatedAt = Carbon::createFromTimeString($status->created_at)->subHours(3);

                            // checking if tweet created_at is less than $fromHour
                            if($tweetCreatedAt->lt($fromHour)){
                                // getting out from foreach and do while
                                $breakDoWhile = true;
                                break;
                            }

                            // checking if tweet not exists in database
                            if (!Tweet::where('id', $status->id)->exists()) {
                                // inserting tweet into database
                                $tweet = Tweet::create([
                                    'id' => $status->id,
                                    'created_at' => $tweetCreatedAt,
                                    'data' => NULL,
                                    'user_id' => $status->user->id,
                                    'query' => $query,
                                    'pre_data' => json_encode($status)
                                ]);

                                /**
                                 * getting data from tweet if truncate added to a queue job
                                 * u don't really need to do this if u don't want to
                                 */
                                GetDataFromTweet::dispatch($tweet);
                            }

                            // setting maxTweetId as current tweet id
                            $maxTweetId = $status->id;

                            // adding unit to progressbar
                            $progress->advance();
                        }

                        // checking if we must leave do while
                        if($breakDoWhile){
                            break;
                        }

                    }catch (TwitterRequestFailed $e){
                        /*
                            * if request code is 429 means that our limit was reached
                            * so let's wait 5 minutes using sleep()
                            * and then try this loop again using $continue control
                        */
                        if($e->getCode() == 429) {
                            sleep(60 * 5);
                            $continue = true;
                        }

                        /*
                         *  if its not, i assumed that was authentication failure
                         *
                         */
                        else throw new TwitterAuthFailed("Twitter Auth Failed",$e->getCode(),$e);
                    }

                    // finishing progress bar
                    $progress->finish();

                    /*
                     * if $continue, means that we reached limit and must try to request again
                     *
                     * if count($res->statuses) == $count, means that last page wasn't reached yet, so we will try to
                     * request again, but with max_tweet_id updated value
                     */
                } while ($continue || count($res->statuses) == $count);
            }

            // That's all folks
    }
}
