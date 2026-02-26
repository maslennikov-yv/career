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
