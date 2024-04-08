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
    error_log(print_r($request->query('new', 0), true));
    error_log(print_r($request->query('new', 0), true));
    if ($request->query('new', 0) || !Storage::disk('local')->exists("cams/$request->port.jpg")) {
        $curl = curl_init();

        $username = "Donaldedward1921";
        $password = "Props&rotors@5755";

        curl_setopt_array($curl, array(
            // CURLOPT_URL => "http://185.10.80.33:8082/record/current.jpg",
            CURLOPT_URL => "http://98.97.103.67:$request->port/record/current.jpg",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic " . base64_encode($username . ":" . $password)
            ),
            CURLOPT_TIMEOUT_MS => 10000
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            Storage::disk('local')->put("cams/$request->port.jpg", $response);
        } else {
            if (Storage::disk('local')->exists("cams/$request->port.jpg"))
                return response()->file(storage_path("app/cams/$request->port.jpg"), [
                    "cam-server-status" => "no response",
                    "Access-Control-Expose-Headers"=>"cam-server-status" 
                ]);
        }
    }

    if (Storage::disk('local')->exists("cams/$request->port.jpg"))
        return response()->file(storage_path("app/cams/$request->port.jpg", [
            "cam-server-status" => "successful-response",
            "Access-Control-Expose-Headers"=>"cam-server-status" 
        ]));
    else return response()->status(404);
});
