<?php

namespace App\Console\Commands;

use App\Jobs\GetDataFromTweet;
use App\Tweet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateToV2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate application to v2';

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
        $clearTables = [
            'jobs',
            'failed_jobs',
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring'
        ];

        foreach ($clearTables as $clearTable) DB::statement("DELETE FROM  ".$clearTable." WHERE 1");

        $perPage = 1500;

        $tweets = DB::table('tweets')->paginate($perPage);


        $lastPage = $tweets->lastPage();

        $progress = $this->output->createProgressBar($tweets->total());

        for($currentPage = 1;$currentPage <= $lastPage;) {
            foreach ($tweets->items() as $tweet){
                if(!is_null($tweet->data)){
                    DB
                        ::table('tweets')
                        ->where('id',$tweet->id)
                        ->update([
                            'pre_data' => NULL
                        ]);
                }else{
                    if(!is_null($tweet->pre_data)){
                        DB
                            ::table('tweets')
                            ->where('id',$tweet->id)
                            ->update([
                                'data' => $tweet->pre_data,
                                'pre_data' => NULL
                            ]);
                    }else{
                        GetDataFromTweet::dispatch(Tweet::find($tweet->id));
                    }
                }
                $progress->advance();
            }

            $tweets = null;
            $tweets = DB::table('tweets')->simplePaginate($perPage,['*'],'page',++$currentPage);
        }
        $progress->finish();
    }
}
