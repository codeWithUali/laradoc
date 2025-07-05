<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;

// Redirect old routes to new ones
Route::redirect('/documentation', '/docs');
Route::redirect('/install', '/docs#installation');
Route::redirect('/guide', '/docs'); 