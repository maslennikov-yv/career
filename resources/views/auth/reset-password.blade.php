@extends('layouts.app')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Сброс пароля</h1>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4 rounded border border-gray-200 bg-white p-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <label class="mb-1 block text-sm font-medium">Email</label>
            <input
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm"
                value="{{ $email }}"
                disabled
            >
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Новый пароль</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="new-password"
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            >
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium">Повторите пароль</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            >
        </div>

        <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">
            Сохранить пароль
        </button>
    </form>
@endsection

