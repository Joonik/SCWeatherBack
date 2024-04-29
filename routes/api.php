<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Aws\SecretsManager\SecretsManagerClient;

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


//Main weather route to access camera ip pictures
//Required params: api_key, new and port
Route::get('/img', function (Request $request) {
    error_log(print_r($request->query('new', 0), true));
    error_log(print_r($request->query('new', 0), true));
    //validate API key
    if(env('API_KEY') !== $request->query('api_key')) return response('', 403);
    //validate request params
    if ($request->query('new', 0) || !Storage::disk('local')->exists("cams/$request->port.jpg")) {
        //init curl
        $curl = curl_init();
        //get aws secret
        $client = new SecretsManagerClient([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ]);
        //get secret value
        $result = $client->getSecretValue([
            'SecretId' => 'weather-camera-credentials',
        ]);
        //set response object
        $camera = (object) json_decode($result['SecretString'], true);
        //curl settings
        curl_setopt_array($curl, array(
            // CURLOPT_URL => "http://185.10.80.33:8082/record/current.jpg",
            CURLOPT_URL => "http://$camera->ip:$request->port/record/current.jpg",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic " . base64_encode($camera->username . ":" . $camera->password)
            ),
            CURLOPT_TIMEOUT_MS => 10000
        ));
        //execute and close curl request
        $response = curl_exec($curl);
        curl_close($curl);
        //store camera picture
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
    //get existing camera picture
    if (Storage::disk('local')->exists("cams/$request->port.jpg"))
        return response()->file(storage_path("app/cams/$request->port.jpg", [
            "cam-server-status" => "successful-response",
            "Access-Control-Expose-Headers"=>"cam-server-status" 
        ]));
    //no camera picture found
    else 
        return response('', 404);
});
