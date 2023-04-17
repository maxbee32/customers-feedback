<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});

Route::group(['middleware'=>'api',
              'prefix'=>'auth'
],function($router){

Route:: post("branch-signup","App\Http\Controllers\AdminController@branchSignUp");
Route:: post("admin-signup","App\Http\Controllers\AdminController@adminSignUp");
Route:: post("admin-login","App\Http\Controllers\AdminController@adminLogin");
Route:: post("cust-allfeed","App\Http\Controllers\AdminController@getAllComplains");
Route:: post("cust-feednow","App\Http\Controllers\AdminController@getComplainToday");
Route:: post("all-branch","App\Http\Controllers\AdminController@getAllBranch");
Route:: post("all-feedchart","App\Http\Controllers\AdminController@allfeedbackchart");
Route:: post("feedchart-periods","App\Http\Controllers\AdminController@feedbackChartPeriod");
Route:: post("card-data7","App\Http\Controllers\AdminController@cardData");
Route:: post("card-data30","App\Http\Controllers\AdminController@cardData1");
Route:: post("card-data90","App\Http\Controllers\AdminController@cardData2");
Route:: post("card-data365","App\Http\Controllers\AdminController@cardData3");
Route:: post("cust-feedforsevendays","App\Http\Controllers\AdminController@commentfor7");
Route:: post("cust-feedforthirtydays","App\Http\Controllers\AdminController@commentfor30");
Route:: post("cust-feedforninetydays","App\Http\Controllers\AdminController@commentfor90");
Route:: get("cust-feedforthreesixfivedays","App\Http\Controllers\AdminController@commentfor365");
Route::delete("delete-user/{id}","App\Http\Controllers\AdminController@deleteUser");
Route::post("single-data","App\Http\Controllers\AdminController@singleBranch");
Route::post("single-branch-date","App\Http\Controllers\AdminController@singleBranchWithDate");
});


Route::group(['middleware'=>'api',
              'prefix'=>'branch'
],function($router){

Route:: post("branch-login","App\Http\Controllers\UserController@userLogin");
Route:: post("cust-feedback","App\Http\Controllers\UserController@custFeedback");

});

Route::post('send',function(){
    return Artisan::call('sendemail:cron');
});

