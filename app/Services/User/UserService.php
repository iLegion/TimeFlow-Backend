<?php

namespace App\Services\User;

use App\DTO\User\UserCreateDTO;
use App\Models\User;

class UserService
{
    public function create(UserCreateDTO $dto): User
    {
        $user = new User();
        $user->name = $dto->name;
        $user->email = $dto->email;
        $user->password = bcrypt($dto->password);

        $user->save();

        return $user;
    }
}
