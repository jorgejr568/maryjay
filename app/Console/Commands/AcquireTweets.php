<?php

namespace App\Console\Commands;

use App\Core\TwitterAuth;
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
     */
    public function handle()
    {
            foreach ($this->argument('query') as $query) {
                $this->info("GETTING TWEETS FROM {$query}");

                $count = 100;
                do {
                    try {
                        $lastTweetIdOfQuery = Tweet::where('query', $query)->orderBy('created_at')->select('id', 'created_at')->first();

                        $res = $this->cb->search_tweets(http_build_query([
                            'q' => $query,
                            'count' => $count,
                            'until' => now()->format('Y-m-d'),
                            'max_id' => $lastTweetIdOfQuery->id ?? null
                        ]));

                        echo PHP_EOL;

                        if($res->httpstatus == 429) sleep(60 * 5);

                        $this->alert($lastTweetIdOfQuery->id . " - " . $lastTweetIdOfQuery->created_at->format('d/m/Y H:i:s'));

                        $progress = $this->output->createProgressBar(count($res->statuses));

                        foreach ($res->statuses as $status) {
                            if (!Tweet::where('id', $status->id)->exists()) {
                                $tweet = Tweet::create([
                                    'id' => $status->id,
                                    'created_at' => Carbon::createFromTimeString($status->created_at),
                                    'data' => NULL,
                                    'user_id' => $status->user->id,
                                    'query' => $query
                                ]);

                                GetDataFromTweet::dispatch($tweet)->delay(now()->addSeconds(rand(10, 120)));
                            }
                            $progress->advance();
                        }
                    }catch (\Exception $e){
                        if($res->httpstatus == 429) sleep(60 * 5);
                        else break;
                    }
                } while (count($res->statuses) == $count);
                $progress->finish();
            }
    }
}
