<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    public function response(mixed $data = [], array $additional = []): JsonResponse
    {
        return response()->json(['data' => $data, ...$additional]);
    }
}
