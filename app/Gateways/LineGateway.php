<?php

namespace App\Gateways;

use Illuminate\Support\Facades\Http;

class LineGateway
{
    /**
     * @throws \Exception
     */
    public function me($idToken)
    {
        $apiUrl = 'https://api.line.me/oauth2/v2.1/verify';
        $response = Http::asForm()->post(
            $apiUrl,
            [
                'id_token' => $idToken,
                'client_id' => env('LINE_CLIENT_ID'),
            ]
        );
        if ($response->status() == 200) {
            $lineUser = $response->json();

            return [
                'id' => $lineUser['sub'],
                'name' => $lineUser['name'],
                'avatar' => $lineUser['picture'] ?? null,
            ];
        } else {
            $error = $response->json();
            throw new \Exception('取得 Line Profile 失敗，請檢查 id token: ' . json_encode($error));
        }
    }
}
