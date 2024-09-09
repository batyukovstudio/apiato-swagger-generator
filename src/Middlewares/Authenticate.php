<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Middlewares;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, \Closure $next, ?string ...$guards): Response|RedirectResponse|null
    {
        $response = null;
        $user = Auth::guard('web')->user();

        if (null === $user || false === $user?->hasRole('admin')) {
            return redirect()->to('admin/login');
        }

        return $next($request);
    }
}