<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Codebird\Codebird;

Route::get('/',function (){
    return view('welcome');
});

Route::group(['prefix' => 'twitter','namespace' => 'Twitter'],function (){
    Route::get('/', "AuthController@redirect")->name('redirect');
    Route::get('/callback', "AuthController@callback")->name('callback');
    Route::get('/success', "AuthController@success")->name('success');
});

Auth::routes(['register' => true]);

Route::get('/home', 'HomeController@index')->name('home');
