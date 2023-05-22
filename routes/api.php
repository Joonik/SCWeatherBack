<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::get('/img', function (Request $request) {
    error_log($request->query('new',0));
    if ($request->query('new',0)) {
        $curl = curl_init();

        $username = "username";
        $password = "password";

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://185.10.80.33:8082/record/current.jpg",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic " . base64_encode($username . ":" . $password)
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            Storage::disk('local')->put('cams/img.jpg', $response);
        }
    }

    if (Storage::disk('local')->exists("cams/img.jpg"))
        return response()->file(storage_path("app/cams/img.jpg"));
    else return response()->status(404);
});
