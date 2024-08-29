<?php

use Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers\SwaggerController;

Route::get('swagger', [SwaggerController::class, 'documentation'])
    ->middleware(['auth:web'])
    ->name('swagger');

Route::get('swagger-callback', [SwaggerController::class, 'callback'])
    ->middleware(['auth:web'])
    ->name('swagger-callback');
