<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'swagger-doc')->name('documentation.index');

Route::get('/documentation/json', function () {
    $openapi = \OpenApi\Generator::scan([app_path()]);
    return response()
        ->json($openapi)
        ->header('Content-Type', 'application/json');
})->name('documentation.json');

Route::get('/documentation/yml', function () {
    $openapi = \OpenApi\Generator::scan(['../app']);
    return response()
        ->json($openapi->toYaml())
        ->header('Content-Type', 'application/yaml');
})->name('documentation.yaml');
