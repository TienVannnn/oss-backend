<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(["namespace" => "App\Http\Controllers\DataScraping", "prefix" => "scraping"], function () {
    Route::get("/category", "ScrapingController@scrapingCategories");
    Route::get("/comics", "ScrapingController@scrapingComics");
});

Route::view("/otp", "otp");
