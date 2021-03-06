<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardRequest;
use App\Dashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboards.create',[
            'queries' => DB::table('tweets')->select('query')->distinct()->get()->pluck('query')->toArray()
        ]);
    }

    private function createQuery(DashboardRequest $request){
        $metadatas = [];

        $queries = [
            'metadata' => $request->input('metadata_rule_metadata'),
            'query' => $request->input('metadata_rule_query')
        ];

        if($queries['metadata'] ?? false) {
            for ($i = 0; $i < count($queries['metadata']); $i++) {
                if (
                    !empty($queries['metadata'][$i]) AND
                    !empty($queries['query'][$i])
                ) {
                    $metadatas[] = [
                        'metadata' => $queries['metadata'][$i],
                        'query' => $queries['query'][$i]
                    ];
                }
            }
        }

        return [
            'period_from' => $request->input('period_from'),
            'period_to' => $request->input('period_to'),
            'queries' => $request->input('queries'),
            'metadata_rules' => $metadatas
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(DashboardRequest $request)
    {
        $dashboard =  Dashboard::create([
            'name' => $request->input('name'),
            'query' => json_encode($this->createQuery($request),JSON_PRETTY_PRINT),
            'to_process' => true
        ]);

        return redirect()->route('dashboards.show',['dashboard' => $dashboard->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dashboard  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function show(Dashboard $dashboard,Request $request)
    {
        if($request->has('download_gephy')){
            return response()
                ->file(storage_path('app/dashboards/'.$dashboard->id."-gephy.csv"),[
                    'Content-type' => "text/csv",
                    'Content-Disposition: ' => 'attachment; filename="'.Str::slug($dashboard->name).'.csv"\''
                ]);
        }
        if($request->has('download_json')){
            return response()
                ->file(storage_path('app/dashboards/'.$dashboard->id."-tweets.json"),[
                    'Content-type' => "application/json",
                    'Content-Disposition: ' => 'attachment; filename="'.Str::slug($dashboard->name).'.json"\''
                ]);
        }
        else {
            return view('dashboards.view', [
                'dashboard' => $dashboard
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Dashboard  $searchDashboard
     * @return \Illuminate\Http\Response
     */
    public function edit(Dashboard $searchDashboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Dashboard  $searchDashboard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dashboard $searchDashboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Dashboard  $searchDashboard
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dashboard $searchDashboard)
    {
        //
    }
}
