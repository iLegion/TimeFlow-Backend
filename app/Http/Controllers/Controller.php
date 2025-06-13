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
}
