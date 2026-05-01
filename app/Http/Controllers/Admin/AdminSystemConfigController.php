<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminSystemConfigController extends Controller
{
    public function __invoke(): View
    {
        $basics = [
            'App name' => config('app.name'),
            'Environment' => config('app.env'),
            'Debug' => config('app.debug') ? 'On' : 'Off',
            'URL' => config('app.url'),
            'Timezone' => config('app.timezone'),
            'Mail default' => config('mail.default'),
        ];

        return view('admin.system', compact('basics'));
    }
}
