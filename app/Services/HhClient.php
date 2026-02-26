<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

/**
 * Клиент к публичному API HH.RU.
 * 2.1 — получение вакансий (специализация и количество из config/career.php).
 * Список регионов — справочник HH.RU через suggests/areas.
 */
class HhClient
{
    private function client(): PendingRequest
    {
        return Http::baseUrl('https://api.hh.ru')
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 200);
    }

    /**
     * Подсказки по регионам HH.RU (город, область, край).
     *
     * @return array<int, array{id:string,text:string,url:string}>
     */
    public function suggestAreas(string $text): array
    {
        $response = $this->client()
            ->get('/suggests/areas', ['text' => $text])
            ->throw()
            ->json();

        return Arr::map(Arr::get($response, 'items', []), static fn (array $item): array => [
            'id' => (string) Arr::get($item, 'id'),
            'text' => (string) Arr::get($item, 'text'),
            'url' => (string) Arr::get($item, 'url'),
        ]);
    }

    /**
     * Вакансии по специализации (из конфига) и региону. Первые N из выдачи (N из конфига).
     *
     * @return array{region:?string,vacancies:array<int, array<string, mixed>>}
     */
    public function vacanciesByArea(string $regionId): array
    {
        $specialization = config('career.vacancy_specialization', 'менеджер');
        $perPage = config('career.vacancy_count', 6);

        $response = $this->client()
            ->get('/vacancies', [
                'text' => $specialization,
                'area' => $regionId,
                'per_page' => $perPage,
            ])
            ->throw()
            ->json();

        $items = Arr::get($response, 'items', []);

        $vacancies = Arr::map($items, static fn (array $vacancy): array => [
            'id' => (string) Arr::get($vacancy, 'id'),
            'name' => (string) Arr::get($vacancy, 'name'),
            'area_name' => (string) Arr::get($vacancy, 'area.name'),
            'salary_from' => Arr::get($vacancy, 'salary.from'),
            'salary_to' => Arr::get($vacancy, 'salary.to'),
            'currency' => Arr::get($vacancy, 'salary.currency'),
            'employer_name' => (string) Arr::get($vacancy, 'employer.name'),
            'url' => (string) Arr::get($vacancy, 'alternate_url'),
            'published_at' => (string) Arr::get($vacancy, 'published_at'),
        ]);

        $region = ! empty($vacancies) ? ($vacancies[0]['area_name'] ?? null) : null;

        return [
            'region' => $region,
            'vacancies' => $vacancies,
        ];
    }
}
