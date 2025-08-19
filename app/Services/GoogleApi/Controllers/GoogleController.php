<?php

namespace App\Services\GoogleApi\Controllers;

use App\Services\GoogleApi\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class GoogleController extends BaseController
{
    public function redirect(Request $request, GoogleService $googleService)
    {
        $target = $request->get('target');

        return redirect()->away($googleService->getAuthUrl($target));
    }

    public function callback(Request $request, GoogleService $googleService)
    {
        $code = $request->get('code');
        $target = $request->get('state', route('projects'));

        if ($code) {
            $googleService->setAuthCode($code);

            return redirect()->to($target);
        }

        abort(400, 'Authorization failed.');
    }
}
