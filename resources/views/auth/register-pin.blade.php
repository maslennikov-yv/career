@extends('layouts.app')

@section('content')
    <h1 class="mb-4 text-2xl font-semibold">Подтверждение email</h1>
    <p class="mb-4 text-sm text-gray-600">Шаг 2 из 3: введите PIN из 4 цифр. Проверка произойдет автоматически.</p>

    <div class="rounded bg-white p-5 shadow">
        <div
            id="pin-box"
            data-endpoint="{{ route('register.pin.verify') }}"
            data-csrf="{{ csrf_token() }}"
            class="mb-3 flex gap-2"
        >
            <input class="pin-input w-12 rounded border px-2 py-3 text-center text-xl" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code">
            <input class="pin-input w-12 rounded border px-2 py-3 text-center text-xl" type="text" inputmode="numeric" maxlength="1">
            <input class="pin-input w-12 rounded border px-2 py-3 text-center text-xl" type="text" inputmode="numeric" maxlength="1">
            <input class="pin-input w-12 rounded border px-2 py-3 text-center text-xl" type="text" inputmode="numeric" maxlength="1">
        </div>
        <p id="pin-status" class="text-sm text-gray-600"></p>
        <div class="mt-4">
            <a class="text-sm underline" href="{{ route('register') }}">Отправить код заново</a>
        </div>
    </div>

    @vite('resources/js/auth/pin.js')
@endsection
