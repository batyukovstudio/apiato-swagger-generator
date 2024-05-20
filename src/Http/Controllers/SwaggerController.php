<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Batyukovstudio\ApiatoSwaggerGenerator\Services\SwaggerGeneratorService;

/**
 * Class SwaggerController
 * @package Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers
 */
class SwaggerController extends BaseController
{
    public function documentation(): Response
    {
        return ResponseFacade::make(view('swagger::index', [
            'secure'            =>  true,
            'urlToDocs'         =>  route('swagger-callback')
        ]), 200);

    }

    public function callback(SwaggerGeneratorService $swaggerGeneratorService): Response
    {
        $content = $swaggerGeneratorService->generate();
        return ResponseFacade::make($content, 200, [
            'Content-Type' => 'application/json',
        ]);
    }

}
