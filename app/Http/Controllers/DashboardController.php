<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(){
        return view('dashboards.create',[
            'queries' => DB::table('tweets')->select('query')->distinct()->get()->pluck('query')->toArray()
        ]);
    }
}
