<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/excel', [ProductController::class, 'generateExcel']);
Route::get('/excel-job', [ProductController::class, 'generateExcelJob']);
Route::get('/pdf', [ProductController::class, 'generatePdf']);
Route::get('/exceptions', [ProductController::class, 'throwExceptions']);
Route::get('/errors', [ProductController::class, 'throwErrors']);
