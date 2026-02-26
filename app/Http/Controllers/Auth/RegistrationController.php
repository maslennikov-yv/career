<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SignupPinMail;
use App\Models\SignupVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    private const PIN_TTL_MINUTES = 10;

    private const MAX_VERIFY_ATTEMPTS = 5;

    public function showEmailStep(): View
    {
        return view('auth.register-email');
    }

    public function sendPin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc,dns'],
        ]);

        if (User::query()->where('email', $data['email'])->exists()) {
            return back()->withErrors([
                'email' => 'Пользователь с таким email уже зарегистрирован.',
            ]);
        }

        $throttleKey = 'signup-pin-send:'.$request->ip().':'.$data['email'];
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Повторная отправка PIN будет доступна через {$seconds} сек.",
            ]);
        }
        RateLimiter::hit($throttleKey, 60);

        $pin = (string) random_int(1000, 9999);

        SignupVerification::query()
            ->where('email', $data['email'])
            ->delete();

        SignupVerification::query()->create([
            'email' => $data['email'],
            'pin_hash' => Hash::make($pin),
            'expires_at' => now()->addMinutes(self::PIN_TTL_MINUTES),
        ]);

        Mail::to($data['email'])->send(new SignupPinMail($pin));

        $request->session()->put('signup_email', $data['email']);
        $request->session()->forget('signup_verified_at');

        return redirect()
            ->route('register.pin')
            ->with('status', 'PIN отправлен на email.')
            ->with('pin_toast', $pin);
    }

    public function showPinStep(Request $request): RedirectResponse|View
    {
        if (! $request->session()->has('signup_email')) {
            return redirect()->route('register');
        }

        return view('auth.register-pin');
    }

    public function verifyPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'digits:4'],
        ]);

        $email = (string) $request->session()->get('signup_email');
        if ($email === '') {
            return response()->json(['message' => 'Сессия регистрации истекла.'], 422);
        }

        $verification = SignupVerification::query()
            ->where('email', $email)
            ->latest('id')
            ->first();

        if (! $verification || $verification->expires_at->isPast()) {
            return response()->json(['message' => 'PIN истек. Запросите новый.'], 422);
        }

        if ($verification->attempts >= self::MAX_VERIFY_ATTEMPTS) {
            return response()->json(['message' => 'Слишком много попыток. Запросите новый PIN.'], 429);
        }

        if (! Hash::check($request->string('pin')->value(), $verification->pin_hash)) {
            $verification->increment('attempts');
            return response()->json(['message' => 'Неверный PIN.'], 422);
        }

        $verification->forceFill([
            'verified_at' => now(),
        ])->save();

        $request->session()->put('signup_verified_at', Carbon::now()->toIso8601String());

        return response()->json([
            'ok' => true,
            'next' => route('register.password'),
        ]);
    }

    public function showPasswordStep(Request $request): RedirectResponse|View
    {
        if (! $request->session()->has('signup_email')) {
            return redirect()->route('register');
        }

        if (! $request->session()->has('signup_verified_at')) {
            return redirect()->route('register.pin');
        }

        return view('auth.register-password');
    }

    public function storePassword(Request $request): RedirectResponse
    {
        $email = (string) $request->session()->get('signup_email');
        if ($email === '') {
            return redirect()->route('register');
        }

        if (! $request->session()->has('signup_verified_at')) {
            return redirect()->route('register.pin');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::query()->create([
            'name' => Str::before($email, '@'),
            'email' => $email,
            'password' => $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget(['signup_email', 'signup_verified_at']);

        return redirect()->route('lk.profile');
    }
}
