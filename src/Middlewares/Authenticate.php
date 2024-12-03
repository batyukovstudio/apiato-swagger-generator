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

        $hasAccess = false;

        if (
            $user !== null &&
            method_exists($user, 'hasAdminRole') &&
            $user->hasAdminRole() === true
        ) {
            $hasAccess = true;
        }

        if ($hasAccess === false) {
            return redirect()->to('admin/login');
        }

        return $next($request);
    }
}
