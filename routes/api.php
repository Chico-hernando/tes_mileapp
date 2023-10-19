<?php

use App\Http\Controllers\Api\PackageController\PackageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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

Route::get('ping', function (Request  $request) {    
    $connection = DB::connection('mongodb');
    $msg = 'MongoDB is accessible!';
    $id1 = Str::uuid()->toString();
    $id2 = Str::uuid()->toString();
    $id3 = $id1;
    try {  
        $connection->command(['ping' => 1]);  
        } catch (\Exception  $e) {  
        $msg = 'MongoDB is not accessible. Error: ' . $e->getMessage();
    }
    return ['msg' => $msg, 'id' => [$id1, $id2, $id3]];
    });


Route::post('package',[PackageController::class,'createPackage']);
Route::get('package',[PackageController::class,'getPackage']);
Route::get('package/{id}',[PackageController::class,'getPackageById']);
Route::put('package/{id}',[PackageController::class,'updateSinglePackage']);
Route::patch('package/{id}',[PackageController::class,'updatePackage']);
Route::delete('package/{id}',[PackageController::class,'deletePackage']);