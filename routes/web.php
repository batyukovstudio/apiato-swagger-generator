<?php

use Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers\SwaggerController;

Route::get('swagger', [SwaggerController::class, 'documentation'])->name('swagger');
Route::get('swagger-callback', [SwaggerController::class, 'callback'])->name('swagger-callback');
