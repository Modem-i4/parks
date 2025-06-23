<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client; // для HTTP запитів
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    public function handleRedirect(Request $request)
    {
        // Отримуємо id_token з параметрів GET (або POST)
        $idToken = $request->input('id_token');

        if (!$idToken) {
            return redirect('/login')->withErrors('Нема ID токена');
        }

        // Перевіряємо токен у Google
        $client = new Client();
        $response = $client->get('https://oauth2.googleapis.com/tokeninfo', [
            'query' => ['id_token' => $idToken],
        ]);

        if ($response->getStatusCode() !== 200) {
            return redirect('/login')->withErrors('Некоректний токен');
        }

        $data = json_decode($response->getBody(), true);

        // Перевіряємо audience (твій Client ID)
        if (($data['aud'] ?? '') !== env('GOOGLE_CLIENT_ID')) {
            return redirect('/login')->withErrors('Невірний клієнт');
        }

        // Тепер у $data є email, name і інше
        $email = $data['email'] ?? null;

        if (!$email) {
            return redirect('/login')->withErrors('Відсутній email');
        }

        // Шукаємо або створюємо користувача
        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $data['name'] ?? '']
        );

        // Логін
        Auth::login($user);

        return redirect('/dashboard');
    }
}
