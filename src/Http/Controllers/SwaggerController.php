<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers;

use Batyukovstudio\ApiatoSwaggerGenerator\Services\SwaggerGeneratorService;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
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

    public function callback(SwaggerGeneratorService $swaggerGeneratorService): Response
    {
//        dd($swaggerGeneratorService->generate());
//        return response(json_encode($swaggerGeneratorService->generate()));
        $documentation = Storage::disk('swagger')->get(config('swagger.documentation_filename'));
        return response($documentation);
    }

}
