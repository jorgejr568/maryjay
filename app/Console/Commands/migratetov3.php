<?php

namespace App\Console\Commands;

use App\Jobs\GetDataFromTweet;
use App\Tweet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateToV3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:v3 {--per-page=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate application to v3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $perPage = (int) $this->option('per-page');

        $tweetsQuery = DB::table('tweets');//->where('v3',false);
        $tweets = $tweetsQuery->paginate($perPage);


        $lastPage = $tweets->lastPage();

        $progress = $this->output->createProgressBar($tweets->total());

        for($currentPage = 1;$currentPage <= $lastPage;$currentPage++) {
            $insert = [];

            if($currentPage > 1){
                $tweets = $tweetsQuery->simplePaginate($perPage,['*'],'page',$currentPage);
            }

            foreach ($tweets->items() as $tweet){
                $insert[$tweet->id] = (array) $tweet;

                $insert[$tweet->id]['data'] = json_encode(
                    Tweet::toV3(
                        json_decode($tweet->data)
                    )
                );
                $insert[$tweet->id]['v3'] = true;

                $progress->advance();
            }
            $tweets = null;

            DB::table('tweets')->whereIn('id',array_keys($insert))->delete();
            DB::table('tweets')->insert($insert);
        }

        $progress->finish();
    }
}
