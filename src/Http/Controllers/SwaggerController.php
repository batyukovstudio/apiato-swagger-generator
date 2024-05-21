<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class SwaggerController
 * @package Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers
 */
class SwaggerController extends BaseController
{
    public function documentation(): View
    {
        $urlToDocs = route('swagger-callback');
        return view('swagger::index', compact('urlToDocs'));
    }

    public function callback(): Response
    {
        $documentation = Storage::get(config('swagger.storage_endpoint'));
        return response($documentation);
    }

}
