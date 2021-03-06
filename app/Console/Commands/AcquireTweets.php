<?php

namespace App\Console\Commands;

use App\Core\TwitterAuth;
use App\Exceptions\TwitterAuthFailed;
use App\Exceptions\TwitterRequestFailed;
use App\Tweet;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AcquireTweets extends Command
{
    use TwitterAuth;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acquire_tweets {--period=hour} {--max-tweet-id=} {query*}';

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
        /**
         * Get period before
         * We will get tweets only until $fromPeriod
        */
            switch ($this->option('period')){
                case 'hour':
                    $fromPeriod = now()->subHour()->startOfHour();
                    break;
                case 'day':
                    $fromPeriod = now()->subDay()->startOfDay();
                    break;
                case 'week':
                    $fromPeriod = now()->subWeek()->startOfWeek();
                    break;
                default:
                    $this->error("INVALID PERIOD");
                    die();
                    // Finishing process
            }

            $maxTweetIdDefault = $this->option('max-tweet-id') ? $this->option('max-tweet-id') : null;


            // Foreach query passed as argument to command | globo havan natura
            foreach ($this->argument('query') as $query) {
                // Echo info into terminal
                $this->info("GETTING TWEETS FROM {$query}");
                // Number of tweets that we gonna get in one request
                $count = 100;
                // Variable to control if we are getting only tweets until $fromPeriod
                $breakDoWhile = false;
                // Variable to paginate correctly twitter api
                $maxTweetId = $maxTweetIdDefault;
                do {
                    // Variable to control if has any errors on twitter request

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

                        $statuses = collect($res->statuses);

                        $statusesInDatabase = DB
                            ::table('tweets')
                            ->select('id')
                            ->whereIn('id',$statuses->pluck('id')->toArray())
                            ->get()
                            ->pluck('id')
                            ->toArray();

                        $statusesToInsert = [];

                        foreach ($statuses as $status) {
                            // parsing tweet created_at property to an Carbon object that we can handle easily
                            // Sub hours because value comes in GMT
                            $tweetCreatedAt = Carbon::createFromTimeString($status->created_at)->subHours(3);

                            // checking if tweet created_at is less than $fromPeriod
                            if($tweetCreatedAt->lt($fromPeriod)){
                                // getting out from foreach and do while
                                $breakDoWhile = true;
                                break;
                            }

                            // checking if tweet not exists in database
                            if (!in_array($status->id,$statusesInDatabase)) {
                                // inserting tweet into database
                                $statusesToInsert[] = [
                                    'id' => $status->id,
                                    'created_at' => $tweetCreatedAt,
                                    'data' => json_encode(Tweet::toV3($status)),
                                    'user_id' => $status->user->id,
                                    'query' => $query,
                                    'v3' => true
                                ];
                            }

                            // setting maxTweetId as current tweet id
                            $maxTweetId = $status->id;

                            // adding unit to progressbar
                            $progress->advance();
                        }

                        if(count($statusesToInsert) > 0) DB::table('tweets')->insert($statusesToInsert);
                        $statusesToInsert = null;
                        $statusesInDatabase = null;

                        // finishing progress bar
                        $progress->finish();

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
                            $this->alert("WAITING SOME TIME UNTIL API LIMIT FINISH");
                            sleep(60 * 5);
                        }

                        /*
                         *  if its not, i assumed that was authentication failure
                         *
                         */
                        else throw new TwitterAuthFailed("Twitter Auth Failed",$e->getCode(),$e);
                    }

                    /*
                     * if $continue, means that we reached limit and must try to request again
                     *
                     * if count($res->statuses) == $count, means that last page wasn't reached yet, so we will try to
                     * request again, but with max_tweet_id updated value
                     */
                } while (true);
            }

            // That's all folks
    }
}
