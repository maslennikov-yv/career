@extends('layouts.app')

@section('content')
    <h1 class="mb-4 text-2xl font-semibold">Личный кабинет</h1>

    <section class="mb-6 rounded bg-white p-5 shadow">
        <h2 class="mb-3 text-lg font-medium">Профиль</h2>

        <form method="POST" action="{{ route('lk.city.update') }}" class="space-y-3">
            @csrf
            <label class="block">
                <span class="mb-1 block text-sm">Регион</span>
                <div class="relative">
                    <input
                        id="city-input"
                        class="w-full rounded border px-3 py-2"
                        type="text"
                        name="city_name"
                        value="{{ old('city_name', $user->city_name) }}"
                        autocomplete="off"
                        placeholder="Начните вводить…"
                    >
                    <ul
                        id="city-suggests"
                        class="absolute left-0 right-0 top-full z-10 mt-1 hidden max-h-64 overflow-auto rounded border bg-white shadow"
                    ></ul>
                </div>
                <input id="hh-region-id" type="hidden" name="hh_region_id" value="{{ old('hh_region_id', $user->hh_region_id) }}">
            </label>

            <button class="rounded bg-black px-4 py-2 text-white" type="submit">Сохранить регион</button>
        </form>
    </section>

    <section class="mb-6 rounded bg-white p-5 shadow">
        <h2 class="mb-3 text-lg font-medium">Смена пароля</h2>
        <form method="POST" action="{{ route('lk.password.update') }}" class="space-y-3">
            @csrf
            <input class="w-full rounded border px-3 py-2" type="password" name="current_password" placeholder="Текущий пароль" required>
            <input class="w-full rounded border px-3 py-2" type="password" name="password" placeholder="Новый пароль" required>
            <input class="w-full rounded border px-3 py-2" type="password" name="password_confirmation" placeholder="Повторите пароль" required>
            <button class="rounded bg-black px-4 py-2 text-white" type="submit">Обновить пароль</button>
        </form>
    </section>

    <section class="rounded bg-white p-5 shadow">
        <h2 class="mb-3 text-lg font-medium">Сброс пароля</h2>
        <form method="POST" action="{{ route('lk.password.reset-link') }}">
            @csrf
            <button class="rounded border px-4 py-2" type="submit">Отправить ссылку для сброса</button>
        </form>
    </section>

    <script>
        const input = document.getElementById('city-input');
        const list = document.getElementById('city-suggests');
        const regionIdInput = document.getElementById('hh-region-id');

        let timer = null;

        const clearList = () => {
            list.innerHTML = '';
            list.classList.add('hidden');
        };

        const showList = () => {
            if (list.children.length > 0) {
                list.classList.remove('hidden');
            }
        };

        input.addEventListener('input', () => {
            regionIdInput.value = '';
            clearTimeout(timer);

            const query = input.value.trim();
            if (query.length < 2) {
                clearList();
                return;
            }

            timer = setTimeout(async () => {
                const response = await fetch(`/api/areas/suggest?text=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    clearList();
                    return;
                }
                const payload = await response.json();
                clearList();

                (payload.items || []).forEach((item) => {
                    const li = document.createElement('li');
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'w-full px-3 py-2 text-left text-sm hover:bg-gray-100';
                    button.textContent = item.text;
                    button.addEventListener('click', () => {
                        input.value = item.text;
                        regionIdInput.value = item.id;
                        clearList();
                    });
                    li.appendChild(button);
                    list.appendChild(li);
                });
                showList();
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!list.contains(e.target) && e.target !== input) {
                list.classList.add('hidden');
            }
        });

        input.addEventListener('focus', () => {
            showList();
        });
    </script>
@endsection
