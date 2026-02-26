<?php

namespace App\Http\Controllers\Lk;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * ЛК: профиль пользователя.
 *
 * 2.2 — регион: из раздела «Личные данные» (город проживания) или ручной выбор;
 * при ручном выборе используется список регионов как у HH.RU (город, область, край).
 */
class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('lk.profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateCity(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'city_name' => ['required', 'string', 'max:255'],
            'hh_region_id' => ['required', 'string', 'max:32'],
        ]);

        $request->user()->update($data);

        return back()->with('status', 'Город обновлен.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $request->user()->update([
            'password' => $data['password'],
        ]);

        return back()->with('status', 'Пароль обновлен.');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        Password::sendResetLink(['email' => $request->user()->email]);

        return back()->with('status', 'Ссылка для сброса отправлена на email.');
    }
}
