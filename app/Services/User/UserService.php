<?php

namespace App\Services\User;

use App\Data\User\UserUpdateData;
use App\Data\User\UserUpdateEmailData;
use App\Data\User\UserUpdatePasswordData;
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

    public function update(UserUpdateData $data): User
    {
        $user = $data->user;

        if ($data->name && $user->name !== $data->name) $user->name = $data->name;

        if ($user->isDirty()) $user->save();

        return $user;
    }

    public function updateEmail(UserUpdateEmailData $data): User
    {
        $user = $data->user;
        $user->email = $data->email;

        $user->save();

        return $user;
    }

    public function updatePassword(UserUpdatePasswordData $data): User
    {
        $user = $data->user;
        $user->password = bcrypt($data->new_password);

        $user->save();

        return $user;
    }
}
