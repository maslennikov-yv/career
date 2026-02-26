<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HhClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AreaSuggestController extends Controller
{
    public function __construct(private readonly HhClient $hhClient)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $text = (string) $request->query('text', '');

        if (mb_strlen(trim($text)) < 2) {
            return response()->json(['items' => []]);
        }

        $throttleKey = 'areas-suggest:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 60)) {
            return response()->json(['message' => 'Too many requests.'], 429);
        }
        RateLimiter::hit($throttleKey, 60);

        return response()->json([
            'items' => $this->hhClient->suggestAreas($text),
        ]);
    }
}
