<?php

use App\Http\Controllers\Api\Front\Account\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


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


Route::prefix('v1')->group(function()
{
	Route::prefix('front')->group(function()
	{
		Route::middleware('auth:sanctum')->get('exit', [RegisterController::class, 'exit']);
		Route::get('user', [RegisterController::class, 'user']);
		Route::post('register', [RegisterController::class, 'register']);
		Route::post('confirmRegister', [RegisterController::class, 'confirmRegister']);
	});
});
