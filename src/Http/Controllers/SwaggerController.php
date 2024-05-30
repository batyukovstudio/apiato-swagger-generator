<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers;

use Batyukovstudio\ApiatoSwaggerGenerator\Services\SwaggerGeneratorService;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Storage;
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
//        $data = Http::get('https://api-docs.aviakassa.com/docs?token=partner-7896f8fa69398c56d86a65357615c41f');
//        $decoded = json_decode($data, associative: true);
//        $decoded['openapi'] = '3.1.0';
//        return response(json_encode($decoded));

//        dd($swaggerGeneratorService->generate());
        return response(json_encode($swaggerGeneratorService->generate()));
//        dd($swaggerGeneratorService->generate());
        $documentation = Storage::get(config('swagger.storage_endpoint'));
        return response($documentation);
    }

}
