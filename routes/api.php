<?php

use Illuminate\Http\Request;

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

Route::prefix('')->group(function () {
    Route::get('get', 'C_DataVitz@get');
    Route::delete('remove-file/{file}', 'C_DataVitz@removeFile');
    Route::get('get-files', 'C_DataVitz@getFiles');
    Route::get('generate/{file}/{report}', 'C_DataVitz@generate');
    Route::post('upload', 'C_DataVitz@uploadFile');
});
