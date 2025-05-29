<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected ?User $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function response(mixed $data = [], array $additional = []): JsonResponse
    {
        return response()->json(['data' => $data, ...$additional]);
    }

    public function responseError(string $message, int $status): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
