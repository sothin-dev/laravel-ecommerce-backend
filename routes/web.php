<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The frontend is a Vue.js SPA served by Vite (dev) or a static build.
| Laravel acts purely as the REST API backend. All UI routes live in
| the Vue application; nothing is rendered by Blade anymore.
|
*/

Route::redirect('/', env('FRONTEND_URL', 'http://localhost:5173'));
