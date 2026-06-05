<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\Landing\LandingService;
use Illuminate\Http\Request;

class LandingDataController extends Controller
{
    protected LandingService $landingService;

    public function __construct(LandingService $landingService)
    {
        $this->landingService = $landingService;
    }

    public function index(Request $request)
    {
        $originalUser = auth()->user();
        $isGuest = !$originalUser;

        if ($isGuest) {
            $org = Organization::first();
            if ($org) {
                $user = User::where('organization_id', $org->id)->first();
                if ($user) {
                    auth()->setUser($user);
                }
            }
        }

        $landingData = $this->landingService->getLandingData();

        if ($isGuest) {
            auth()->forgetUser();
        }

        return response()->json($landingData);
    }
}
