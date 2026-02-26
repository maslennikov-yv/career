<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HhClient;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Данные для страницы «Карьера».
 *
 * 2.1 — вакансии с HH.RU по одной специализации (конфиг), первые 6.
 * 2.2 — регион из профиля пользователя (Личные данные) или ручной выбор (region_id);
 *       если не указан — region: null, region_hint: «Для отображения вакансий выберите регион».
 * 2.4 — кэширование результатов запроса к HH.RU.
 */
class CareerVacancyController extends Controller
{
    public function __construct(private readonly HhClient $hhClient)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $regionId = $request->query('region_id');

        if (! $regionId && $request->user()) {
            $regionId = $request->user()->hh_region_id;
        }

        if (! $regionId) {
            return response()->json([
                'region' => null,
                'region_hint' => 'Для отображения вакансий выберите регион',
                'vacancies' => [],
            ]);
        }

        $ttl = config('career.cache_ttl_minutes', 20);
        $specialization = config('career.vacancy_specialization', 'менеджер');
        $cacheKey = 'hh:vacancies:'.mb_strtolower($specialization).':area:'.$regionId;

        try {
            $payload = $this->cacheStore()->remember($cacheKey, now()->addMinutes($ttl), function () use ($regionId): array {
                return $this->hhClient->vacanciesByArea((string) $regionId);
            });
        } catch (RequestException $e) {
            return response()->json([
                'region' => null,
                'region_hint' => null,
                'vacancies' => [],
                'error' => 'Регион не найден или сервис временно недоступен',
            ], 422);
        }

        return response()->json([
            'region' => $payload['region'],
            'region_hint' => null,
            'vacancies' => $payload['vacancies'],
        ]);
    }

    private function cacheStore(): Repository
    {
        try {
            return Cache::store('redis');
        } catch (\Throwable) {
            return Cache::store();
        }
    }
}
