<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::group(
    [
        'namespace' => 'App\Http\Controllers\Admin\\',
        'as' => 'admin.',
        'prefix' => 'admin',
    ],
    function () {
        $prefix = 'category';
        $controller = ucfirst($prefix) . 'Controller';
        Route::group(['prefix' => $prefix, 'as' => $prefix . '.'], function () use ($controller) {
            Route::get('create-root', "$controller@createRoot")->name('createRoot');
        });

        Route::resource($prefix, $controller)->parameters([$prefix => 'item']);
    }
);
