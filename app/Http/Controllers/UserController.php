<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function me(): JsonResponse
    {
        return $this->response(UserResource::make(auth()->user()));
    }
}
