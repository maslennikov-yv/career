@extends('layouts.app')

@section('content')
    <h1 class="mb-4 text-2xl font-semibold">Регистрация</h1>
    <p class="mb-4 text-sm text-gray-600">Шаг 1 из 3: укажите email. На него отправим PIN-код.</p>

    <form method="POST" action="{{ route('register.email') }}" class="space-y-4 rounded bg-white p-5 shadow">
        @csrf
        <label class="block">
            <span class="mb-1 block text-sm">Email</span>
            <input class="w-full rounded border px-3 py-2" type="email" name="email" value="{{ old('email') }}" required>
        </label>

        <button class="rounded bg-black px-4 py-2 text-white" type="submit">Отправить PIN</button>
    </form>
@endsection
