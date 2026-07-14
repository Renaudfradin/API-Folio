<?php

namespace App\Http\Controllers;

use App\Filament\Pages\LinkedInDashboard;
use App\Services\LinkedIn\LinkedInSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use RuntimeException;

class LinkedInAuthController extends Controller
{
    public function redirect(Request $request, LinkedInSyncService $service)
    {
        $state = Str::random(40);

        $request->session()->put([
            'linkedin_oauth_state' => $state,
        ]);

        return redirect()->away($service->authorizationUrl($state));
    }

    public function callback(Request $request, LinkedInSyncService $service)
    {
        if ($request->string('state')->toString() !== $request->session()->pull('linkedin_oauth_state')) {
            abort(403, 'Invalid LinkedIn state.');
        }

        $code = $request->string('code')->toString();

        if ($code === '') {
            abort(400, 'Missing LinkedIn authorization code.');
        }

        try {
            $tokenData = $service->exchangeCodeForToken($code);
            $profileData = $service->fetchUserInfo($tokenData['access_token']);

            $service->persistConnection(
                $request->user(),
                $request->user()->linkedinConnection()->first(),
                $tokenData,
                $profileData
            );

            Notification::make()
                ->title('LinkedIn connecté')
                ->body('La connexion LinkedIn a été enregistrée avec succès.')
                ->success()
                ->send();
        } catch (RuntimeException $exception) {
            Notification::make()
                ->title('Connexion LinkedIn impossible')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }

        return redirect()->to(LinkedInDashboard::getUrl());
    }
}
