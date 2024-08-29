<?php

use Batyukovstudio\ApiatoSwaggerGenerator\Middlewares\Authenticate;
use Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers\SwaggerController;

Route::middleware(['web', Authenticate::class])->group(function () {
    Route::get('swagger', [SwaggerController::class, 'documentation'])->name('swagger');
    Route::get('swagger-callback', [SwaggerController::class, 'callback'])->name('swagger-callback');
});
