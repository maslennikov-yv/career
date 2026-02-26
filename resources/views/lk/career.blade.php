@extends('layouts.app')

@section('content')
    <h1 class="mb-4 text-2xl font-semibold">Вакансии</h1>

    <div class="mb-4 rounded bg-white p-5 shadow">
        <p class="mb-3 text-sm text-gray-600">Показываем 6 вакансий по специализации «Менеджер».</p>
        <div class="flex flex-wrap items-end gap-2">
            <label class="block">
                <span class="mb-1 block text-sm">Регион</span>
                <div class="relative">
                    <input
                        id="region-text-input"
                        class="w-full rounded border px-3 py-2"
                        type="text"
                        value=""
                        autocomplete="off"
                        placeholder="Начните вводить…"
                    >
                    <ul
                        id="region-suggests"
                        class="absolute left-0 right-0 top-full z-10 mt-1 hidden max-h-64 overflow-auto rounded border bg-white shadow"
                    ></ul>
                </div>
                <input id="region-id-input" type="hidden" value="{{ $user?->hh_region_id ?? '' }}">
            </label>
            <button id="load-vacancies" class="rounded bg-black px-4 py-2 text-white" type="button">Загрузить</button>
        </div>
        <p id="career-status" class="mt-2 text-sm text-gray-600"></p>
    </div>

    <ul id="vacancy-list" class="space-y-3"></ul>

    <script>
        const regionTextInput = document.getElementById('region-text-input');
        const regionIdInput = document.getElementById('region-id-input');
        const regionSuggests = document.getElementById('region-suggests');
        const loadButton = document.getElementById('load-vacancies');
        const status = document.getElementById('career-status');
        const list = document.getElementById('vacancy-list');

        let suggestTimer = null;

        const clearSuggests = () => {
            regionSuggests.innerHTML = '';
            regionSuggests.classList.add('hidden');
        };

        const showSuggests = () => {
            if (regionSuggests.children.length > 0) {
                regionSuggests.classList.remove('hidden');
            }
        };

        const showToast = (message) => {
            const el = document.createElement('div');
            el.setAttribute('role', 'alert');
            el.className = 'fixed bottom-4 right-4 z-50 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900 shadow-lg';
            el.textContent = message;
            document.body.appendChild(el);
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.3s';
                setTimeout(() => el.remove(), 300);
            }, 4000);
        };

        const escapeHtml = (str) => {
            if (str == null) return '';
            const div = document.createElement('div');
            div.textContent = String(str);
            return div.innerHTML;
        };

        const safeUrl = (url) => {
            if (typeof url !== 'string') return '#';
            const u = url.trim();
            return (u.startsWith('http://') || u.startsWith('https://')) ? u : '#';
        };

        const render = (payload) => {
            list.innerHTML = '';

            if (payload.region_hint) {
                status.textContent = payload.region_hint;
                showToast('Вакансий не найдено');
                return;
            }

            status.textContent = payload.region ? `Регион: ${escapeHtml(payload.region)}` : '';

            const vacancies = payload.vacancies || [];
            vacancies.forEach((vacancy) => {
                const li = document.createElement('li');
                li.className = 'rounded bg-white p-4 shadow';
                const name = escapeHtml(vacancy.name);
                const areaName = escapeHtml(vacancy.area_name);
                const employerName = escapeHtml(vacancy.employer_name ?? '');
                const salaryFrom = vacancy.salary_from != null ? escapeHtml(String(vacancy.salary_from)) : '-';
                const salaryTo = vacancy.salary_to != null ? escapeHtml(String(vacancy.salary_to)) : '-';
                const currency = escapeHtml(vacancy.currency ?? '');
                const url = safeUrl(vacancy.url);
                li.innerHTML = `
                    <h3 class="font-semibold">${name}</h3>
                    <p class="text-sm text-gray-600">${areaName} · ${employerName}</p>
                    <p class="text-sm">ЗП: ${salaryFrom} - ${salaryTo} ${currency}</p>
                    <a class="text-sm underline" href="${escapeHtml(url)}" target="_blank" rel="noreferrer">Открыть вакансию</a>
                `;
                list.appendChild(li);
            });

            if (vacancies.length === 0) {
                showToast('Вакансий не найдено');
            }
        };

        const loadVacancies = async () => {
            const regionId = regionIdInput.value.trim();
            status.textContent = 'Загрузка...';
            const query = regionId ? `?region_id=${encodeURIComponent(regionId)}` : '';
            const response = await fetch(`/api/career/vacancies${query}`);
            const payload = await response.json();

            if (!response.ok) {
                status.textContent = '';
                list.innerHTML = '';
                showToast(payload.error || payload.message || 'Ошибка загрузки вакансий');
                return;
            }

            render(payload);
        };

        loadButton.addEventListener('click', loadVacancies);

        regionTextInput.addEventListener('input', () => {
            regionIdInput.value = '';
            clearTimeout(suggestTimer);

            const query = regionTextInput.value.trim();
            if (query.length < 2) {
                clearSuggests();
                return;
            }

            suggestTimer = setTimeout(async () => {
                const response = await fetch(`/api/areas/suggest?text=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    clearSuggests();
                    return;
                }

                const payload = await response.json();
                clearSuggests();

                (payload.items || []).forEach((item) => {
                    const li = document.createElement('li');
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'w-full px-3 py-2 text-left text-sm hover:bg-gray-100';
                    button.textContent = item.text;
                    button.addEventListener('click', () => {
                        regionTextInput.value = item.text;
                        regionIdInput.value = item.id;
                        clearSuggests();
                        loadVacancies();
                    });
                    li.appendChild(button);
                    regionSuggests.appendChild(li);
                });
                showSuggests();
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!regionSuggests.contains(e.target) && e.target !== regionTextInput) {
                regionSuggests.classList.add('hidden');
            }
        });

        regionTextInput.addEventListener('focus', () => {
            showSuggests();
        });

        if (regionIdInput.value.trim().length > 0) {
            loadVacancies();
        }
    </script>
@endsection
