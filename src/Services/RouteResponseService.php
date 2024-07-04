<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Contracts\Tests\TestRouteInterface;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ApiatoRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DefaultRouteValue;
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

        self::$RESPONSES[$pathInfo][(string) $response->getStatusCode()] = [
            'data' => $responseContent,
        ];
    }

    public function saveResponsesToDisk(): void
    {
        Storage::disk('swagger')->put(self::RESPONSES_FILENAME, json_encode(self::$RESPONSES));
    }

    public function getResponses(DefaultRouteValue|ApiatoRouteValue $routeInfo): ?array
    {
        $responses = $this->loadResponses();

        $pathInfo = $routeInfo->getPathInfo();

        return $responses[$pathInfo] ?? null;
    }

    private function loadResponses(): array
    {
        $responses = [];

        if (empty($this->loadedResponses) && $this->isFirstLoad === true) {
            $loaded = Storage::disk('swagger')->get(self::RESPONSES_FILENAME);

            if ($loaded !== null) {
                $this->loadedResponses = json_decode($loaded, associative: true);
            } else {
                $this->isFirstLoad = false;
            }
        }

        return $responses;
    }
}
