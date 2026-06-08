<?php

namespace App\Http\Controllers;

use App\Support\StaffNavContext;
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
        $query = [];
        if ($request->boolean('modal')) {
            $query['modal'] = '1';
        }
        if (StaffNavContext::isValid($request->query('context'))) {
            $query['context'] = $request->query('context');
        }
        if ($query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?').http_build_query($query);
        }

        return redirect()->to($url, $status, $headers);
    }
}
