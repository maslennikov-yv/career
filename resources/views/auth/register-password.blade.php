@extends('layouts.app')

@section('content')
    <h1 class="mb-4 text-2xl font-semibold">Задайте пароль</h1>
    <p class="mb-4 text-sm text-gray-600">Шаг 3 из 3: после сохранения вы будете автоматически авторизованы.</p>

    <form method="POST" action="{{ route('register.password.store') }}" class="space-y-4 rounded bg-white p-5 shadow">
        @csrf
        <label class="block">
            <span class="mb-1 block text-sm">Пароль</span>
            <div class="relative flex">
                <input id="password" class="w-full rounded border border-gray-300 px-3 py-2 pr-10" type="password" name="password">
                <button id="toggle-password" class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700" type="button" title="Показать пароль" aria-label="Показать пароль">
                    <svg id="icon-eye" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg id="icon-eye-off" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4l16 16"/>
                    </svg>
                </button>
            </div>
        </label>

        <button class="rounded bg-black px-4 py-2 text-white" type="submit">Сохранить пароль</button>
    </form>

    <script>
        (function () {
            const btn = document.getElementById('toggle-password');
            const input = document.getElementById('password');
            const iconEye = document.getElementById('icon-eye');
            const iconEyeOff = document.getElementById('icon-eye-off');
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                const visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                iconEye.classList.toggle('hidden', visible);
                iconEyeOff.classList.toggle('hidden', !visible);
                btn.setAttribute('title', visible ? 'Показать пароль' : 'Скрыть пароль');
                btn.setAttribute('aria-label', visible ? 'Показать пароль' : 'Скрыть пароль');
            });
        })();
    </script>
@endsection
