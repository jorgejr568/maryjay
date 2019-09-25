<?php

namespace App\Http\Controllers;

use App\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home',[
            "acquisitions" => [
                "total" => Tweet::count(),
//                "processed" => Tweet::whereNotNull('data')->count(),
                "per_query" => Tweet::select(['query',DB::raw('COUNT(*) as count')])->groupBy('query')->orderBy('query')->get()
            ]
        ]);
    }
}
