<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Dashboard extends Model
{
    use SoftDeletes;

    const LIMIT_POS_PROCESS = 150;

    const IGNORED_MOST_USED_WORDS = [
        "at",
        "de",
        "em",
        "ao",
        "para",
        "pela",
        "se",
        "no",
        "na",
        "com",
        "uma",
        "um",
        "as",
        "ja",
        "por",
        "https",
        "so",
        "os",
        "foi",
        "pra",
        "dos",
        "da",
        "do",
        "nao",
        "que",
        "rt",
    ];

    public $table = 'dashboards';

    protected $fillable = [
        'name',
        'query'
    ];

    protected $infos = null;

    private function parseQueryIntoSql(){
        $query = json_decode($this->query);

        $sql = DB::table('tweets');

        // Parsing queries
        $sql->whereIn('query',$query->queries);

        // Parsing periods
        if(!is_null($query->period_from)){
            $sql->where(
                'created_at',
                '>=',
                Carbon::createFromFormat('Y-m-d',$query->period_from)->startOfDay()
            );
        }
        if(!is_null($query->period_to)){
            $sql->where(
                'created_at',
                '<=',
                Carbon::createFromFormat('Y-m-d',$query->period_to)->endOfDay()
            );
        }

        // Parsing metadata
        foreach($query->metadata_rules as $metadata_rule){
            $parsedQuery = array_filter(
                explode(' ',$metadata_rule->query)
            );

            $metadataIndex = $metadata_rule->metadata;

            $sql->where(function($where) use ($parsedQuery,$metadataIndex){
                $whereFn = 'where';

                foreach($parsedQuery as $parsedWord){
                    $parsedWord = str_replace('%',' ',$parsedWord);

                    if(strtoupper($parsedWord) == "OR"){
                        $whereFn = "orWhere";
                        continue;
                    }

                    $where->$whereFn(
                        'data->'.str_replace('.','->',$metadataIndex),
                        'like',
                        '%'.$parsedWord.'%'
                    );

                    $whereFn = "where";
                }
            });
        }

        return $sql;
    }

    public function process(){
        $query = $this->parseQueryIntoSql()->select('id');

        $perPage = 1000;
        $tweets = $query->paginate($perPage);

        $lastPage = $tweets->lastPage();

        DB::table('dashboard_tweets')->where('dashboard_id',$this->id)->delete();

        for($page = 1;$page <= $lastPage;$page++){
            if($page > 1){
                $tweets = $query->simplePaginate($perPage,['*'],'page',$page);
            }

            foreach ($tweets->items() as $tweet){
                DB::table('dashboard_tweets')->insert([
                    'dashboard_id' => $this->id,
                    'tweet_id' => $tweet->id
                ]);
            }
        }

        $this->posProcessData();
    }

    public function posProcessData(){
        $data = [
            'most_user_words' => $this->posProcessMostUsedWords(),
            'most_rt_status' => $this->posProcessMostRTStatus(),
            'most_favorite_status' => $this->posProcessMostFavoriteStatus(),
            'most_used_locations' => $this->posProcessMostUsedLocations(),
            'most_followed_users' => $this->posProcessMostFollowedUsers(),
            'most_favorites_users' => $this->posProcessMostFavoriteUsers()
        ];

        file_put_contents(
            storage_path('app/dashboards/'.$this->id.".json"),
            json_encode($data,JSON_PRETTY_PRINT)
        );
    }

    private function posProcessMostUsedWords(){
        $words = [];
        $perPage = 1000;

        $tweets_query = DB
            ::table('dashboard_tweets AS dt')
            ->join('tweets AS t','t.id','=','dt.tweet_id')
            ->select([
                't.data->text as text'
            ]);
        $tweets = $tweets_query
            ->paginate($perPage);

        $lastPage = $tweets->lastPage();

        for($page = 1;$page <= $lastPage;$page++){
            if($page > 1){
                $tweets = $tweets_query->simplePaginate($perPage,['*'],'page',$page);
            }

            foreach ($tweets->items() as $tweet){
                $tweet = Str::slug($tweet->text,' ');

                $exploded_tweet = array_filter(explode(' ',$tweet),function($tweet_word){
                    return
                        (strlen($tweet_word) > 1) &&
                        (!in_array($tweet_word,self::IGNORED_MOST_USED_WORDS)) &&
                        substr($tweet_word,0,6) != 'httpst';
                });

                foreach ($exploded_tweet as $tweet_word){
                    $word_index = $tweet_word;

                    if(!isset($words[$word_index])){
                        $words[$word_index] = [
                            'word' => $tweet_word,
                            'count' => 1
                        ];
                    }else{
                        $words[$word_index]['count']++;
                    }
                }
            }
        }

        return (collect($words)->sortByDesc('count')->take(self::LIMIT_POS_PROCESS)->toArray());
    }

    private function posProcessMostRTStatus($fromRT = false){
        if($fromRT){
            $fieldId = 't.data->retweeted_status->id';
            $fieldText = 't.data->retweeted_status->text';
            $fieldOrder = 't.data->retweeted_status->retweet_count';
            $fieldOrderRaw = "'$.\"retweeted_status\".\"retweet_count\"'";

        }else{
            $fieldId = 't.data->id';
            $fieldText = 't.data->text';
            $fieldOrder = 't.data->retweet_count';
            $fieldOrderRaw = "'$.\"retweet_count\"'";
        }

        $tweets = DB
            ::table('dashboard_tweets AS dt')
            ->join('tweets AS t','t.id','=','dt.tweet_id')
            ->select([
                $fieldId.' as id',
                $fieldText.' as text',
                $fieldOrder.' as rt',
            ])
            ->orderByRaw("CAST(json_unquote(json_extract(`t`.`data`, $fieldOrderRaw)) AS unsigned) DESC")
            ->limit(self::LIMIT_POS_PROCESS)
            ->get()
            ->unique('id')
            ->toArray();

        return $tweets;
    }

    private function posProcessMostFavoriteUsers(){
        $fieldId = 't.data->user->id';
        $fieldName = 't.data->user->name';
        $fieldScreenName = 't.data->user->screen_name';
        $fieldOrder = 't.data->user->favourites_count';
        $fieldOrderRaw = "'$.\"user\".\"favourites_count\"'";

        $tweets = DB
            ::table('dashboard_tweets AS dt')
            ->join('tweets AS t','t.id','=','dt.tweet_id')
            ->select([
                $fieldId.' as id',
                $fieldName.' as name',
                $fieldScreenName.' as screenName',
                $fieldOrder.' as favorite',
            ])
            ->orderByRaw("CAST(json_unquote(json_extract(`t`.`data`, $fieldOrderRaw)) AS unsigned) DESC")
            ->limit(self::LIMIT_POS_PROCESS)
            ->get()
            ->unique('id')
            ->toArray();

        return $tweets;
    }

    private function posProcessMostFollowedUsers(){
        $fieldId = 't.data->user->id';
        $fieldName = 't.data->user->name';
        $fieldScreenName = 't.data->user->screen_name';
        $fieldOrder = 't.data->user->followers_count';
        $fieldOrderRaw = "'$.\"user\".\"followers_count\"'";

        $tweets = DB
            ::table('dashboard_tweets AS dt')
            ->join('tweets AS t','t.id','=','dt.tweet_id')
            ->select([
                $fieldId.' as id',
                $fieldName.' as name',
                $fieldScreenName.' as screenName',
                $fieldOrder.' as followers',
            ])
            ->orderByRaw("CAST(json_unquote(json_extract(`t`.`data`, $fieldOrderRaw)) AS unsigned) DESC")
            ->limit(self::LIMIT_POS_PROCESS)
            ->get()
            ->unique('id')
            ->toArray();

        return $tweets;
    }

    private function posProcessMostFavoriteStatus($fromRT = false){
        if($fromRT){
            $fieldId = 't.data->retweeted_status->id';
            $fieldText = 't.data->retweeted_status->text';
            $fieldOrder = 't.data->retweeted_status->favorite_count';
            $fieldOrderRaw = "'$.\"retweeted_status\".\"favorite_count\"'";
        }else{
            $fieldId = 't.data->id';
            $fieldText = 't.data->text';
            $fieldOrder = 't.data->favorite_count';
            $fieldOrderRaw = "'$.\"favorite_count\"'";
        }

        $tweets = DB
            ::table('dashboard_tweets AS dt')
            ->join('tweets AS t','t.id','=','dt.tweet_id')
            ->select([
                $fieldId.' as id',
                $fieldText.' as text',
                $fieldOrder.' as favorite',
            ])
            ->orderByRaw("CAST(json_unquote(json_extract(`t`.`data`, $fieldOrderRaw)) AS unsigned) DESC")
            ->limit(self::LIMIT_POS_PROCESS)
            ->get()
            ->unique('id')
            ->toArray();

        return $tweets;
    }

    public function posProcessMostUsedLocations($fromRT = false){
        if($fromRT){
            $fieldId = 't.data->retweeted_status->id';
            $fieldText = 't.data->retweeted_status->text';
            $fieldGroup = 't.data->retweeted_status->user->location';
        }else{
            $fieldId = 't.data->id';
            $fieldText = 't.data->text';
            $fieldGroup = 't.data->user->location';
        }

        $tweets =
            DB::select("SELECT a.location,a.count FROM (".
            DB
            ::table('dashboard_tweets AS dt')
            ->join('tweets AS t','t.id','=','dt.tweet_id')
            ->select([
                $fieldGroup." as location",
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy($fieldGroup)
            ->toSql().') AS a WHERE a.location IS NOT NULL AND a.location NOT IN ("","\n") ORDER BY a.count DESC LIMIT '.self::LIMIT_POS_PROCESS);


        return $tweets;
    }

    public function infos(){
        if(!is_null($this->infos)) return $this->infos;

        $filePath = storage_path('app/dashboards/'.$this->id.".json");

        if(file_exists($filePath)){
            return $this->infos = json_decode(file_get_contents($filePath));
        }

        return false;
    }
}
