@extends('layouts.app')

@section('content')
    <h1 class="mb-4 text-2xl font-semibold">Вход</h1>

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4 rounded bg-white p-5 shadow">
        @csrf

        <label class="block">
            <span class="mb-1 block text-sm">Email</span>
            <input class="w-full rounded border px-3 py-2" type="email" name="email" value="{{ old('email') }}" required>
        </label>

        <label class="block">
            <span class="mb-1 block text-sm">Пароль</span>
            <input class="w-full rounded border px-3 py-2" type="password" name="password" required>
        </label>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1">
            <span>Запомнить меня</span>
        </label>

        <button class="rounded bg-black px-4 py-2 text-white" type="submit">Войти</button>
    </form>
@endsection
