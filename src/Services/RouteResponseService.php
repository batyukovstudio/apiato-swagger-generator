<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Contracts\Tests\TestRouteInterface;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ApiatoRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DefaultRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ResponseValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RouteResponseService
{
    private static array $RESPONSES;
    private array $loadedResponses;
    private bool $isFirstLoad;
    private const RESPONSES_FILENAME = 'test-responses.json';

    public function __construct()
    {
        $this->loadedResponses = [];
        $this->isFirstLoad = true;
    }

    public function pushResponse(Request $request, JsonResponse $response): void
    {
        $responseContent = json_decode($response->getContent(), associative: true);

        if (isset($responseContent['data'])) {
            $responseContent = $responseContent['data'];
        }

        $pathInfo = $request->getPathInfo();

        if (false === isset(self::$RESPONSES[$pathInfo])) {
            self::$RESPONSES[$pathInfo] = [];
        }

        $statusCode = (string)$response->getStatusCode();
        self::$RESPONSES[$pathInfo][$statusCode] = $responseContent;
    }

    public function saveResponsesToDisk(): void
    {
        Storage::disk('swagger')->put(self::RESPONSES_FILENAME, json_encode(self::$RESPONSES));
    }

    public function getResponses(DefaultRouteValue|ApiatoRouteValue $routeInfo): Collection
    {
        $responses = new Collection();
        $loadedResponseGroups = $this->loadResponses();

        $pathInfo = $routeInfo->getPathInfo();
        $responseGroup = $loadedResponseGroups[$pathInfo] ?? [];

        foreach ($responseGroup as $status => $content) {
            $responses->push(ResponseValue::run()
                ->setStatus($status)
                ->setContent($content));
        }

        return $responses;
    }

    private function loadResponses(): array
    {
        if ($this->isFirstLoad === true) {
            $loaded = Storage::disk('swagger')->get(self::RESPONSES_FILENAME);
            $this->loadedResponses = json_decode($loaded, associative: true);
            $this->isFirstLoad = false;
        }

        return $this->loadedResponses;
    }
}
