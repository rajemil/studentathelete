<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Redirect to a named route, appending ?modal=1 when the incoming request is in modal (iframe) mode.
     */
    protected function redirectRoutePreservingModal(Request $request, string $routeName, mixed $parameters = [], int $status = 302, array $headers = []): RedirectResponse
    {
        $url = route($routeName, $parameters);
        if ($request->boolean('modal')) {
            $url .= (str_contains($url, '?') ? '&' : '?').'modal=1';
        }

        return redirect()->to($url, $status, $headers);
    }
}
