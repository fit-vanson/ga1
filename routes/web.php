<?php

use App\Http\Controllers\CronController;
use App\Http\Controllers\GoogleAdModController;
use App\Http\Controllers\GooglePlayController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\DashboardController;


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

// Main Page Route
// Route::get('/', [DashboardController::class,'dashboardEcommerce'])->name('dashboard-ecommerce')->middleware('verified');

Route::get('/', [DashboardController::class,'index'])->name('home-index')->middleware('CheckLogout');

//Auth::routes(['verify' => true]);



Route::group(['prefix'=>'login','middleware'=>'CheckUser'], function (){
    Route::get('/',[HomeController::class,'getLogin']);
    Route::post('/',[HomeController::class,'postLogin'])->name('login');
});
Route::get('logout',[HomeController::class,'logout'])->name('logout');


/* Route Dashboards */
Route::group(['prefix' => 'dashboard','middleware'=>'CheckLogout'], function () {
   Route::get('index', [DashboardController::class,'index'])->name('home-index');
});
/* Route Dashboards */

/* Route API */

Route::group(['prefix' => 'api','middleware'=>'CheckLogout'], function () {
    Route::group(['prefix' => 'admod'], function () {
        Route::get('/', [GoogleAdModController::class, 'index'])->name('api-admod')->middleware('can:admod-index');
        Route::get('/get-list', [GoogleAdModController::class, 'get_admod_list'])->name('get-admod-list')->middleware('can:admod-show');
        Route::post('/post-list', [GoogleAdModController::class, 'post_admod_list'])->name('post-admod-list')->middleware('can:admod-show');
        Route::get('get-token-callback', [GoogleAdModController::class, 'get_get_token_callback'])->middleware('can:admod-index');
//        Route::post('post-get-token-callback', [GoogleAdsenseController::class, 'get_get_token_callback']);
        Route::get('get-ga-token', [GoogleAdModController::class, 'get_get_ga_token'])->middleware('can:admod-index');
        Route::get('get_add_ga/{id}', [GoogleAdModController::class, 'get_add_ga'])->name('get_add_ga')->middleware('can:admod-edit');
        Route::post('post_add_ga', [GoogleAdModController::class, 'post_add_ga'])->name('post_add_ga')->middleware('can:admod-add');

        Route::get('show', [GoogleAdModController::class, 'showAdmod'])->name('showAdmod')->middleware('can:admod-index');
        Route::post('report-today', [GoogleAdModController::class, 'getReportDay'])->name('report-today')->middleware('can:admod-index');
        Route::post('report-date', [GoogleAdModController::class, 'getReportDate'])->name('report-date')->middleware('can:admod-index');
        Route::post('report-month', [GoogleAdModController::class, 'getReportMonth'])->name('report-month')->middleware('can:admod-index');
        Route::post('report-app', [GoogleAdModController::class, 'getReportApp'])->name('report-app')->middleware('can:admod-index');
        Route::post('report-country', [GoogleAdModController::class, 'getReportCountry'])->name('report-country')->middleware('can:admod-index');
        Route::post('report-ad_unit', [GoogleAdModController::class, 'getReportAd_unit'])->name('report-ad_unit')->middleware('can:admod-index');
        Route::get('delete/{id}', [GoogleAdModController::class, 'delete'])->middleware('can:admod-delete');

    });

    Route::group(['prefix' => 'play-console'], function () {
        Route::get('/', [GooglePlayController::class, 'index'])->name('api-play-console');
//        Route::get('/get-list', [GoogleAdModController::class, 'get_admod_list'])->name('get-admod-list')->middleware('can:admod-show');
        Route::post('/post-list', [GooglePlayController::class, 'postIndex'])->name('play-console_post-list');
//        Route::get('get-token-callback', [GoogleAdModController::class, 'get_get_token_callback'])->middleware('can:admod-index');
////        Route::post('post-get-token-callback', [GoogleAdsenseController::class, 'get_get_token_callback']);
//        Route::get('get-ga-token', [GoogleAdModController::class, 'get_get_ga_token'])->middleware('can:admod-index');
//        Route::get('get_add_ga/{id}', [GoogleAdModController::class, 'get_add_ga'])->name('get_add_ga')->middleware('can:admod-edit');
//        Route::post('post_add_ga', [GoogleAdModController::class, 'post_add_ga'])->name('post_add_ga')->middleware('can:admod-add');
//
//        Route::get('show', [GoogleAdModController::class, 'showAdmod'])->name('showAdmod')->middleware('can:admod-index');
//        Route::get('report-date', [GoogleAdModController::class, 'getReportDate'])->name('report-date')->middleware('can:admod-index');
//        Route::get('report-month', [GoogleAdModController::class, 'getReportMonth'])->name('report-month')->middleware('can:admod-index');
//        Route::get('report-app', [GoogleAdModController::class, 'getReportApp'])->name('report-app')->middleware('can:admod-index');
//        Route::get('report-country', [GoogleAdModController::class, 'getReportCountry'])->name('report-country')->middleware('can:admod-index');
//        Route::get('report-ad_unit', [GoogleAdModController::class, 'getReportAd_unit'])->name('report-ad_unit')->middleware('can:admod-index');
//        Route::get('delete/{id}', [GoogleAdModController::class, 'delete'])->middleware('can:admod-delete');

    });

});

/* Route API */

//Cron Route
Route::get('/cron', [CronController::class, 'getIndex']);
// locale Route
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// User
Route::group(['prefix'=>'user'], function (){
    Route::get('/', [UserController::class, 'index'])->name('user-index');
//    Route::get('/', [UserController::class, 'index'])->name('user-index')->middleware('can:user-index');
//    Route::get('/get-list', [UserController::class, 'get_user_list'])->name('get-user-list');
    Route::post('/post-list', [UserController::class, 'post_user_list'])->name('post-user-list')->middleware('can:user-show');
    Route::get('get_add_user/{id}', [UserController::class, 'get_add_user'])->name('get_add_user')->middleware('can:user-edit');
    Route::post('post_add_user', [UserController::class, 'post_add_user'])->name('post_add_user')->middleware('can:user-add');
    Route::post('post_add_user', [UserController::class, 'post_add_user'])->name('post_add_user');
//    Route::get('/edit/{id}',[UserController::class,'edit'])->name('role.edit')->middleware('can:user-edit');;
    Route::get('delete/{id}', [UserController::class, 'delete'])->middleware('can:user-delete');


});


Route::group(['prefix'=>'role'], function (){
    Route::get('/',[RoleController::class,'index'])->name('role-index')->middleware('can:vai_tro-index');
    Route::get('/getIndex', [RoleController::class, "getIndex"])->name('role-getRole')->middleware('can:vai_tro-index');
    Route::post('/create',[RoleController::class,'create'])->name('role.create')->middleware('can:vai_tro-add');;
    Route::get('/edit/{id}',[RoleController::class,'edit'])->name('role.edit')->middleware('can:vai_tro-edit');;
    Route::get('/show/{id}',[RoleController::class,'show'])->name('role.show')->middleware('can:vai_tro-show');;
    Route::post('/update',[RoleController::class,'update'])->name('role.update')->middleware('can:vai_tro-update');;
    Route::get('/delete/{id}',[RoleController::class,'delete'])->name('role.delete')->middleware('can:vai_tro-delete');;
});
Route::group(['prefix'=>'permission'], function (){
    Route::get('/',[PermissionController::class,'index'])->name('permission-index')->middleware('can:phan_quyen-index');
    Route::get('/getIndex/', [PermissionController::class, "getIndex"])->name('permission-getPer')->middleware('can:phan_quyen-index');
    Route::post('/create',[PermissionController::class,'create'])->name('permission.create')->middleware('can:phan_quyen-add');
    Route::get('/edit/{id}',[PermissionController::class,'edit'])->name('permission.edit')->middleware('can:phan_quyen-edit');
    Route::get('/show/{id}',[PermissionController::class,'show'])->name('permission.show')->middleware('can:phan_quyen-show');
    Route::post('/update',[PermissionController::class,'update'])->name('permission.update')->middleware('can:phan_quyen-update');
    Route::get('/delete/{id}',[PermissionController::class,'delete'])->name('permission.delete')->middleware('can:phan_quyen-delete');
});





