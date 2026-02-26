<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('career vacancies endpoint returns six manager vacancies and uses cache', function () {
    $user = User::factory()->create([
        'city_name' => 'Москва',
        'hh_region_id' => '1',
    ]);

    Http::fake([
        'api.hh.ru/vacancies*' => Http::response([
            'items' => [
                [
                    'id' => '1',
                    'name' => 'Менеджер',
                    'area' => ['name' => 'Москва'],
                    'salary' => ['from' => 190000, 'to' => 450000, 'currency' => 'RUR'],
                    'employer' => ['name' => 'Company'],
                    'alternate_url' => 'https://hh.ru/vacancy/1',
                    'published_at' => '2026-02-26T10:17:41+0300',
                ],
            ],
        ]),
    ]);

    $this->actingAs($user)->getJson('/api/career/vacancies')
        ->assertOk()
        ->assertJsonPath('region', 'Москва')
        ->assertJsonCount(1, 'vacancies');

    $this->actingAs($user)->getJson('/api/career/vacancies')
        ->assertOk()
        ->assertJsonPath('region', 'Москва');

    Http::assertSentCount(1);
});

test('career vacancies cache is per region', function () {
    $user = User::factory()->create(['hh_region_id' => null]);

    $responseMoscow = [
        'items' => [
            [
                'id' => '1',
                'name' => 'Менеджер',
                'area' => ['name' => 'Москва'],
                'salary' => null,
                'employer' => ['name' => 'Company'],
                'alternate_url' => 'https://hh.ru/vacancy/1',
                'published_at' => '2026-02-26T10:17:41+0300',
            ],
        ],
    ];
    $responseSpb = [
        'items' => [
            [
                'id' => '2',
                'name' => 'Менеджер',
                'area' => ['name' => 'Санкт-Петербург'],
                'salary' => null,
                'employer' => ['name' => 'Other'],
                'alternate_url' => 'https://hh.ru/vacancy/2',
                'published_at' => '2026-02-26T10:17:41+0300',
            ],
        ],
    ];

    Http::fake([
        'api.hh.ru/vacancies*area=1*' => Http::response($responseMoscow),
        'api.hh.ru/vacancies*area=2*' => Http::response($responseSpb),
    ]);

    $this->actingAs($user)->getJson('/api/career/vacancies?region_id=1')
        ->assertOk()
        ->assertJsonPath('region', 'Москва');
    $this->actingAs($user)->getJson('/api/career/vacancies?region_id=2')
        ->assertOk()
        ->assertJsonPath('region', 'Санкт-Петербург');
    $this->actingAs($user)->getJson('/api/career/vacancies?region_id=1')
        ->assertOk()
        ->assertJsonPath('region', 'Москва');

    Http::assertSentCount(2);
});

test('career vacancies endpoint returns hint when region is missing', function () {
    $user = User::factory()->create([
        'city_name' => null,
        'hh_region_id' => null,
    ]);

    $this->actingAs($user)->getJson('/api/career/vacancies')
        ->assertOk()
        ->assertJson([
            'region' => null,
            'region_hint' => 'Для отображения вакансий выберите регион',
            'vacancies' => [],
        ]);
});
