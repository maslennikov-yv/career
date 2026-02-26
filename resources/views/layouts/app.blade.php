<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
<div class="mx-auto max-w-3xl px-4 py-8">
    <header class="mb-8 flex items-center justify-between">
        <a href="/" class="text-lg font-semibold">Карьера</a>
        <nav class="flex gap-3 text-sm">
            @auth
                @php
                    /** @var \App\Models\User $authUser */
                    $authUser = auth()->user();
                    $email = (string) ($authUser->email ?? '');
                    $name = trim((string) ($authUser->name ?? ''));

                    $initials = '';
                    if ($name !== '') {
                        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                        $first = $parts[0] ?? '';
                        $second = $parts[1] ?? '';
                        $initials = mb_substr($first, 0, 1).($second !== '' ? mb_substr($second, 0, 1) : '');
                    } elseif ($email !== '') {
                        $initials = mb_substr($email, 0, 1);
                    }
                    $initials = mb_strtoupper($initials);
                @endphp

                <div class="relative" data-user-menu>
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded px-2 py-1 hover:bg-gray-100"
                        data-user-menu-trigger
                        aria-haspopup="menu"
                        aria-expanded="false"
                    >
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-700">
                            {{ $initials !== '' ? $initials : '?' }}
                        </span>
                        <span class="max-w-[14rem] truncate text-gray-700">
                            {{ $email }}
                        </span>
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div
                        class="absolute right-0 mt-2 hidden w-48 overflow-hidden rounded border border-gray-200 bg-white shadow"
                        data-user-menu-dropdown
                        role="menu"
                        aria-label="User menu"
                    >
                        <a class="block px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('lk.profile') }}" role="menuitem">
                            Профиль
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50" role="menuitem">
                                Выйти
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a class="underline" href="{{ route('login') }}">Вход</a>
                <a class="underline" href="{{ route('register') }}">Регистрация</a>
            @endauth
        </nav>
    </header>

    @if (session('status'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

@if (session('pin_toast'))
    <div
        id="pin-toast"
        role="alert"
        class="fixed bottom-4 right-4 z-50 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900 shadow-lg"
    >
        PIN для теста: <strong>{{ session('pin_toast') }}</strong>
    </div>
    <script>
        (function () {
            const el = document.getElementById('pin-toast');
            if (!el) return;
            setTimeout(function () {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.3s';
                setTimeout(function () { el.remove(); }, 300);
            }, 12000);
        })();
    </script>
@endif

<script>
    (() => {
        const root = document.querySelector('[data-user-menu]');
        if (!root) return;

        const trigger = root.querySelector('[data-user-menu-trigger]');
        const dropdown = root.querySelector('[data-user-menu-dropdown]');
        if (!trigger || !dropdown) return;

        const isOpen = () => !dropdown.classList.contains('hidden');
        const open = () => {
            dropdown.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
        };
        const close = () => {
            dropdown.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        };
        const toggle = () => (isOpen() ? close() : open());

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            toggle();
        });

        document.addEventListener('click', (e) => {
            if (!root.contains(e.target)) close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });
    })();
</script>
</body>
</html>
